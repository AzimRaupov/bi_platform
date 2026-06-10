<?php

namespace App\Helpers\DataHandlers;

use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\ExtractedData;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\File;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use OpenSpout\Reader\XLS\Reader as XLSReader;
use OpenSpout\Reader\CSV\Reader as CSVReader;

class TableDataHandler
{
    public string $pathData;

    public AiChatMessage $message;

    public string $jsonPath;

    public UploadedFile $uploadData;

    public AiChat $chat;

    public string $storagePath;

    public array $scheme = [];

    public function __construct($message_id, $upload_id, $chat_id)
    {
        $this->chat = AiChat::query()
            ->with('user')
            ->findOrFail($chat_id);

        $this->message = AiChatMessage::query()
            ->findOrFail($message_id);

        $this->uploadData = UploadedFile::query()
            ->findOrFail($upload_id);

        $this->storagePath =
            'company/' .
            $this->chat->user->email .
            '/chats/' .
            $this->chat->id . '/';

        $this->pathData = storage_path(
            'app/company/' . $this->uploadData->file_path
        );

        $this->jsonPath = storage_path(
            'app/' .
            $this->storagePath .
            'extracted_data/' .
            pathinfo($this->uploadData->file_path, PATHINFO_FILENAME) .
            '.json'
        );

        File::ensureDirectoryExists(dirname($this->jsonPath));

        $sampleRows = $this->streamToJsonAndGetSample(100);

        $this->saveScheme($sampleRows);
    }

    protected function streamToJsonAndGetSample(int $sampleLimit = 100): array
    {
        $extension = strtolower(
            pathinfo($this->pathData, PATHINFO_EXTENSION)
        );

        switch ($extension) {
            case 'xlsx':
                $reader = new XLSXReader();
                break;

            case 'xls':
                $reader = new XLSReader();
                break;

            case 'csv':
                $reader = new CSVReader();
                break;

            default:
                throw new \Exception('Unsupported file type');
        }

        $reader->open($this->pathData);

        $headers = [];
        $sampleRows = [];
        $firstRecord = true;

        $handle = fopen($this->jsonPath, 'w');
        fwrite($handle, '[');

        foreach ($reader->getSheetIterator() as $sheet) {

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {

                $cells = $row->toArray();

                if ($rowIndex === 1) {
                    $headers = array_map(
                        fn($v) => trim((string)$v),
                        $cells
                    );

                    continue;
                }

                if (empty($headers)) {
                    continue;
                }

                $rowData = [];

                foreach ($headers as $index => $header) {

                    if ($header === '') {
                        continue;
                    }

                    $rowData[$header] = $cells[$index] ?? null;
                }

                if (empty(array_filter($rowData, fn($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                if (!$firstRecord) {
                    fwrite($handle, ',');
                }

                fwrite(
                    $handle,
                    json_encode(
                        $rowData,
                        JSON_UNESCAPED_UNICODE |
                        JSON_INVALID_UTF8_SUBSTITUTE
                    )
                );

                $firstRecord = false;

                if (count($sampleRows) < $sampleLimit) {
                    $sampleRows[] = $rowData;
                }
            }

            break;
        }

        fwrite($handle, ']');

        fclose($handle);

        $reader->close();

        return $sampleRows;
    }

    public function saveScheme(array $rows = []): array
    {
        $this->scheme = [];

        if (!empty($rows)) {

            foreach (array_keys($rows[0]) as $column) {
                $this->scheme[$column] = $this->detectColumnType(
                    $column,
                    $rows
                );
            }
        }

        $pathScheme = storage_path(
            'app/' .
            $this->storagePath .
            'extracted_data/schema.json'
        );

        File::ensureDirectoryExists(dirname($pathScheme));

        File::put(
            $pathScheme,
            json_encode(
                $this->scheme,
                JSON_UNESCAPED_UNICODE |
                JSON_PRETTY_PRINT
            )
        );

        $pathProperties = storage_path(
            'app/' .
            $this->storagePath .
            'extracted_data/properties.json'
        );

        File::put(
            $pathProperties,
            json_encode(
                $this->scheme,
                JSON_UNESCAPED_UNICODE |
                JSON_PRETTY_PRINT
            )
        );

        return $this->scheme;
    }

    protected function detectColumnType(
        string $column,
        array $rows
    ): string {
        $isInteger = true;
        $isNumber = true;
        $isDate = true;
        $isBoolean = true;

        foreach ($rows as $row) {

            $value = $row[$column] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value)) {
                $isDate = false;
            }

            if (!filter_var($value, FILTER_VALIDATE_INT) && !is_int($value)) {
                $isInteger = false;
            }

            if (!is_numeric($value)) {
                $isNumber = false;
            }

            if (
                !is_bool($value) &&
                !in_array(
                    strtolower((string)$value),
                    ['true', 'false', '0', '1'],
                    true
                )
            ) {
                $isBoolean = false;
            }
        }

        if ($isDate) {
            return 'date';
        }

        if ($isBoolean) {
            return 'boolean';
        }

        if ($isInteger) {
            return 'integer';
        }

        if ($isNumber) {
            return 'number';
        }

        return 'string';
    }

    public function end()
    {
        ExtractedData::query()->create([
            'file_id' => $this->uploadData->id,
            'chat_id' => $this->chat->id,
            'json_path' => $this->jsonPath,
            'company_id' => $this->chat->company_id,
        ]);

        return $this->chat;
    }
}
