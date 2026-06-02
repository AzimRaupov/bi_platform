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

        $data = $this->to_json(3);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
You are a data schema analyst. Analyze the provided data sample and generate a JSON Schema that accurately describes its structure.

## Input Data (first rows of the dataset):
{$data}

## Task:
Generate a JSON Schema for this dataset following these strict rules:

1. The schema must be a JSON object with these exact top-level keys:
   - "title" — short name for the schema (in the same language as the column headers)
   - "description" — one sentence describing what the dataset represents
   - "type" — always "array"
   - "items" — object describing a single row

2. Inside "items.properties", create one entry per column with:
   - "type" — infer correctly: "integer", "number", "string", "boolean"
   - "format" — add "date" for date strings (YYYY-MM-DD), omit otherwise
   - "description" — one sentence explaining what this field represents (same language as headers)

3. "items.required" must list ALL column names.

4. Column names must be taken EXACTLY as they appear in the header row (row 0).

5. Return ONLY valid JSON. No markdown, no backticks, no explanation — just the raw JSON object.
PROMPT;

        $this->scheme = (new AIService(responseFormat: 'json'))->ask($prompt);

        if (is_string($this->scheme)) {
            $this->scheme = json_decode($this->scheme, true);
        }

        if (is_object($this->scheme)) {
            $this->scheme = json_decode(json_encode($this->scheme), true);
        }

        $pathScheme = storage_path(
            'app/' . $this->storagePath . 'extracted_data/schema.json'
        );

        File::ensureDirectoryExists(dirname($pathScheme));

        $this->saveJson($this->scheme, $pathScheme);

        $properties = $this->scheme['items']['properties'] ?? [];

        $pathProperties = storage_path(
            'app/' . $this->storagePath . 'extracted_data/properties.json'
        );

        File::ensureDirectoryExists(dirname($pathProperties));

        $this->saveJson($properties, $pathProperties);

        return $this->scheme;
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
        $this->chat->title=$this->scheme['title'];
        $this->chat->save();
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
