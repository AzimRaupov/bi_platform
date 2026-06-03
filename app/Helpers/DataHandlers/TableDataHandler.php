<?php

namespace App\Helpers\DataHandlers;

use App\Helpers\Ai\AIService;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\ExtractedData;
use App\Models\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class TableDataHandler
{
    public $pathData;
    public $message;
    public $uploadData;
    public $chat;
    public $storagePath;
    public $scheme;

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

        $data = match ($this->uploadData->file_type) {
            'xlsx' => $this->xlsx(),
            'xls'  => $this->xlsx(),
            'csv'  => $this->csv(),
            default => [],
        };

        $jsonPath = storage_path(
            'app/' .
            $this->storagePath .
            'extracted_data/' .
            pathinfo($this->uploadData->file_path, PATHINFO_FILENAME) .
            '.json'
        );

        File::ensureDirectoryExists(dirname($jsonPath));

        $this->saveJson($data, $jsonPath);

        $this->saveScheme();
    }

    /**
     * headers
     */
    protected function getHeaders(): array
    {
        $sheets = Excel::toArray([], $this->pathData);

        if (empty($sheets) || empty($sheets[0])) {
            return [];
        }

        return array_values(
            array_filter(
                $sheets[0][0] ?? [],
                fn ($value) => !is_null($value) && $value !== ''
            )
        );
    }

    /**
     * SCHEMA (ПРОМТ НЕ ТРОГАЛ)
     */
    public function saveScheme(?int $upload_id = null)
    {
        $upload_id ??= $this->uploadData->id;

        $rows = $this->to_json(100);

        $this->scheme = [];

        if (!empty($rows)) {

            foreach (array_keys($rows[0]) as $column) {
                $this->scheme[$column] = $this->detectColumnType($column, $rows);
            }
        }

        $pathScheme = storage_path(
            'app/' . $this->storagePath . 'extracted_data/schema.json'
        );

        File::ensureDirectoryExists(dirname($pathScheme));

        $this->saveJson($this->scheme, $pathScheme);

        $pathProperties = storage_path(
            'app/' . $this->storagePath . 'extracted_data/properties.json'
        );

        File::ensureDirectoryExists(dirname($pathProperties));

        $this->saveJson($this->scheme, $pathProperties);

        return $this->scheme;
    }
    protected function buildSchema(array $rows): array
    {
        if (empty($rows)) {
            return [
                'title' => 'Dataset',
                'description' => 'Imported dataset',
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [],
                    'required' => [],
                ],
            ];
        }

        $firstRow = $rows[0];

        $properties = [];

        foreach ($firstRow as $column => $value) {

            $type = $this->detectColumnType($column, $rows);

            $properties[$column] = [
                'type' => $type,
            ];

            if ($type === 'string' && $this->isDateColumn($column, $rows)) {
                $properties[$column]['format'] = 'date';
            }
        }

        return [
            'title' => pathinfo($this->uploadData->original_name ?? 'Dataset', PATHINFO_FILENAME),
            'description' => 'Автоматически определенная структура таблицы',
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => array_keys($properties),
            ],
        ];
    }
    protected function detectColumnType(string $column, array $rows): string
    {
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
                !in_array(strtolower((string)$value), ['true', 'false', '0', '1'], true)
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
    protected function isDateColumn(string $column, array $rows): bool
    {
        foreach ($rows as $row) {

            $value = $row[$column] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            if (
                !preg_match(
                    '/^\d{4}-\d{2}-\d{2}$/',
                    (string) $value
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Excel → array
     */
    protected function to_json(?int $line = null, ?string $outputPath = null): array
    {
        $sheets = Excel::toArray([], $this->pathData);

        if (empty($sheets) || empty($sheets[0])) {
            return [];
        }

        $rows = $sheets[0];

        if ($line !== null) {
            $rows = array_slice($rows, 0, $line);
        }

        $headers = array_values(array_filter($rows[0]));

        $result = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) {
                continue;
            }

            $row = array_values($row);

            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $result[] = array_combine($headers, $row);
        }

        if ($outputPath) {
            File::ensureDirectoryExists(dirname($outputPath));

            file_put_contents(
                $outputPath,
                json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        }

        return $result;
    }
    /**
     * SAVE JSON
     */
    protected function saveJson(array $data, string $path): bool
    {
        File::ensureDirectoryExists(dirname($path));

        return File::put(
                $path,
                json_encode(
                    $data,
                    JSON_UNESCAPED_UNICODE |
                    JSON_PRETTY_PRINT |
                    JSON_INVALID_UTF8_SUBSTITUTE
                )
            ) !== false;
    }
    public function end()
    {

        ExtractedData::query()->create([
            'file_id' => $this->uploadData->id,
            'message_id' => $this->message->id,
            'company_id'=>$this->chat->company_id,
        ]);
        return $this->chat;
    }

    public function xlsx(): array
    {
        return $this->to_json();
    }

    public function xls(): array
    {
        return $this->xlsx();
    }

    public function csv(): array
    {
        return $this->to_json();
    }
}
