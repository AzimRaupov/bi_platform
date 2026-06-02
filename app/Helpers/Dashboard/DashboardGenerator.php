<?php

namespace App\Helpers\Dashboard;

use App\Helpers\Ai\AIService;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\Widget;

class DashboardGenerator
{
    public $chat;
    public $message;
    public $widgets;
    public $storage;
    public $dashboard;
    public function __construct($chat_id,$message_id)
    {
        $this->chat=AiChat::query()->with('user')->find($chat_id);
        $this->message=AiChatMessage::query()->find($message_id);
        $this->storage = storage_path(
            'app/company/' .
            $this->chat->user->email .
            '/chats/' .
            $this->chat->id
        );
        $this->widgets=Widget::all();
        $this->dashboard=Dashboard::query()->create(
            [
                'chat_id'=>$this->chat->id,
                'company_id' =>$this->chat->user->company_id,
                'name'=>'sas',
                'status'=>'generating',
            ]
        );


        $this->generateWidgets();
    }
    public function generateWidgets($text = "Обший доход по категориям. И обшый доход по месатцам.")
    {
        $widgetsList = $this->widgets->select(['name', 'description']);
        $widgets = json_encode($widgetsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $salesReport = json_decode(file_get_contents($this->storage . '/extracted_data/schema.json'), true);
        $schema = json_encode($salesReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $system = <<<TEXT
Ты — Senior Data Analyst и эксперт по BI-системам. Твоя цель — проанализировать структуру данных, сопоставить её с запросом пользователя и выбрать наиболее эффективные инструменты визуализации из доступного списка.
TEXT;

        // Пользовательский промт: добавлен Few-Shot пример, правила генерации и защита от "лишнего текста"
        $prompt = <<<TEXT
Роль: Аналитик данных. Входные данные: схема таблицы и список доступных графиков.

### 1. СХЕМА ДАННЫХ ДЛЯ ВИЗУАЛИЗАЦИИ:
$schema

### 2. СПИСОК ДОСТУПНЫХ ВИДЖЕТОВ (Используй ТОЛЬКО 'name' из этого списка):
$widgets

### 3. ЗАПРОС ПОЛЬЗОВАТЕЛЯ:
"{$text}"

### ЗАДАЧА:
Выбери из списка те виджеты, которые лучше всего подходят для реализации запроса пользователя на основе предоставленной схемы данных. Если подходят несколько, верни их списком.
Можно исползовать один виджет для разной цели.

### ТРЕБОВАНИЯ К ОТВЕТУ:
1. Выводи ТОЛЬКО валидный JSON-массив объектов. Никаких вводных слов, пояснений вокруг JSON или markdown-разметки (без ```json).
2. Ключи в JSON должны быть строго на латинице: "name" и "instruction" и "title".
3. В "name" передавай точное значение 'name' из списка доступных виджетов.
4. В "instruction" напиши конкретную инструкцию для ии: какие поля (колонки) из схемы данных нужно передать в этот виджет и. Четкая инструкция для ии
5. В "title" короткий заголовок.
### ЭТАЛОННЫЙ ФОРМАТ ОТВЕТА (JSON):
[
  {
    "name": "название_виджета_из_списка",
    "title": "Заголовок виджета",
    "instruction": "Для оси X используем поле 'date' (группировка по месяцам), для оси Y — сумму поля 'total_price'. Это позволит наглядно увидеть динамику..."
  }
]
TEXT;

        $generateWidgets = (new AIService(
            responseFormat: 'json',
        ))->ask($prompt, $system);

        foreach ($generateWidgets as $list) {
            $widget = $this->widgets->where('name', $list['name'])->first();
            DashboardWidget::query()->create([
                'dashboard_id' => $this->dashboard->id,
                'widget_id'=>$widget->id,
                'title'=>$list['title'],
                'instruction'=>$list['instruction'],
            ]);
        }
        dd($generateWidgets );
    }
}
