<?php

namespace App\Helpers\DataHandlers;

use App\Helpers\Ai\AIService;
use App\Models\AiChatMessage;
use App\Models\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class TableDataHandler
{
    public $pathData;
    public $message;
    public $uploadData;

    public function __construct($message_id, $upload_id)
    {
        $this->message = AiChatMessage::query()->findOrFail($message_id);
        $this->uploadData = UploadedFile::query()->findOrFail($upload_id);

        $this->pathData = storage_path(
            'app/private/' . $this->uploadData->file_path
        );

        $data = match ($this->uploadData->file_type) {
            'xlsx' => $this->xlsx(),
            'xls'  => $this->xls(),
            'csv'  => $this->csv(),
            default => [],
        };

        $jsonPath = storage_path(
            'app/private/json/' .
            pathinfo($this->uploadData->file_path, PATHINFO_FILENAME) .
            '.json'
        );

        $this->saveJson($data, $jsonPath);
        $this->saveScheme();
    }
    /**
     * Получить заголовки из первой строки Excel/CSV
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
    protected function saveScheme(?int $upload_id = null)
    {
        $upload_id ??= $this->uploadData->id;

        $d=$this->to_json(3);
dd($d);
         $prompt='
       Ты возвращаешь ТОЛЬКО валидный JSON.

Правила:
- Не добавляй текст, объяснения или комментарии
- Не используй ```json или любые markdown блоки
- Ответ должен начинаться с { и заканчиваться }
- Все ключи только в двойных кавычках
- JSON должен быть валидным и парситься json_decode без ошибок

Формат ответа:
[
{
  "result": "...",
  "status": "success"
}
]
';
        $res = (new AIService(responseFormat: 'text'))->ask($prompt);
        dd($res);

    }

    protected function to_json(?int $line = null, ?string $outputPath = null): array
    {
        $sheets = Excel::toArray([], $this->pathData);

        if (empty($sheets) || empty($sheets[0])) {
            return [];
        }

        $rows = $sheets[0];

        // лимит строк (если задан)
        if ($line !== null) {
            $rows = array_slice($rows, 0, $line);
        }

        $result = [];

        foreach ($rows as $row) {
            $row = array_values($row);

            // пропускаем полностью пустые строки
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $result[] = $row;
        }

        // ===== СОХРАНЕНИЕ В JSON =====
        if ($outputPath) {
            file_put_contents(
                $outputPath,
                json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        }

        return $result;
    }

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
    public function xlsx(): array
    {
        return $this->to_json();
    }

    public function xls(): array
    {
        return $this->getHeaders();
    }

    public function csv(): array
    {
        return $this->to_json();
    }
}
