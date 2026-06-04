<?php

namespace App\Helpers\Ai;

use Orhanerday\OpenAi\OpenAi;

class AIService
{
    protected string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected string $responseFormat;

    public $language;

    public function __construct(
        string $prompt = '',
        int $tokens = 4000,
        string $responseFormat = 'text'
    ) {
        $this->language = 'Русский';
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model = env('GPT_MODEL', 'gpt-5-nano');
        $this->maxTokens = $tokens;
        $this->responseFormat = $responseFormat;
    }

    /**
     * Отправка запроса в OpenAI
     */
    public function ask(string $prompt, ?string $systemPrompt = null): string|array
    {
        $open_ai = new OpenAi($this->apiKey);

        $systemPrompt ??= '
        Ты — AI-ассистент для анализа табличных данных и генерации структурированных JSON-схем.

Твоя задача:
- анализировать входные данные (таблицы, JSON, массивы)
- понимать структуру данных
- генерировать точные JSON Schema или структурированные JSON-объекты
- не добавлять лишний текст, объяснения или комментарии

Правила:
- всегда возвращай только валидный JSON
- не используй markdown, не добавляй ``` и пояснения
- строго следуй структуре, которую требует задача
- не придумывай поля, используй только данные из входа
- если данных недостаточно — всё равно возвращай максимально корректную структуру на основе доступных данных

Фокус:
- точность структуры важнее объяснений
- работаешь как backend-валидатор и генератор схем';

        $response = $open_ai->chat([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => 1.0,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,

        ]);

        $decoded = json_decode($response, true);
        $text = $decoded['choices'][0]['message']['content'] ?? '';

        if ($this->responseFormat === 'text') {
            return $text;
        }

        $clean = str_replace(['```json', '```'], '', $text);
        $parsed = json_decode($clean, true);

        return $parsed ?? [];
    }
}
