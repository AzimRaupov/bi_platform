<?php

namespace App\Helpers\Ai;

use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\Widget;

class DashboardGenerator
{

    public $chat;
    public $message;

    public function __construct($chat_id,$message_id)
    {
        $this->chat=AiChat::query()->with('user')->find($chat_id);
        $this->message=AiChatMessage::query()->find($message_id);
    }
    public function generateContentWidget(string $userRequest='Количество по категориям'): void
    {
        $path = storage_path(
            'app/company/' .
            $this->chat->user->email .
            '/chats/' .
            $this->chat->id .
            '/extracted_data/sales_report.json'
        );

        if (! file_exists($path)) {
            throw new \RuntimeException("Файл sales_report.json не найден");
        }

        $salesReport = json_decode(file_get_contents($path), true);

        if (! is_array($salesReport) || empty($salesReport)) {
            throw new \RuntimeException("Некорректный формат sales_report.json");
        }

        // Берём небольшую выборку (например, 3 строки), чтобы AI видел структуру и примеры данных
        $sampleData = array_slice($salesReport, 0, 3);

        $widget = Widget::query()
            ->find(2);

        $system = <<<TEXT
Ты опытный Python Data Analyst. Твоя задача — писать чистый, готовый к выполнению Python-код без какого-либо сопроводительного текста, комментариев или Markdown-разметки (не используй ```python). Код должен быть полностью автономным.
TEXT;

        $prompt = <<<TEXT
У нас есть JSON-файл с данными о продажах, расположенный по пути: '{$path}'.
Внутри него находится массив объектов.Ключи не долны менятса а значение обязыны менятся зависет от запроса ползователя Вот пример первых нескольких записей из этого файла:
{$this->json($sampleData)}

Нам нужно трансформировать эти данные и привезти их к строгому формату виджета.
Целевой формат JSON, который должен получиться на выходе:
{$widget->scheme}

Напиши Python-код, который:
1. Читает исходный файл по пути '{$path}'.
2. Фильтрует, группирует или агрегирует данные, выполняя задачу пользователя: "{$userRequest}".
3. Форматирует результат в соответствии с целевым форматом виджета.
4. Выводит (print) ИТОГОВЫЙ JSON в терминал (этот вывод мы перехватим в PHP).

Пиши ТОЛЬКО код. Никаких объяснений.
TEXT;

        // Запрашиваем код у AI
        $pythonCode = (new AIService(
            responseFormat: 'text', // Проследи, чтобы в промте стояло требование "без разметки"
        ))->ask($prompt, $system);

        // Очищаем от случайных маркдаун-тегов, если AI всё-таки их добавил

        // Теперь этот код можно выполнить через exec() или Docker-песочницу
        dd($pythonCode);
    }
    private function json(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }
}
