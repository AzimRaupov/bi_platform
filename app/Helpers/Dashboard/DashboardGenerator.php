<?php

namespace App\Helpers\Dashboard;

use App\Helpers\Ai\AIService;
use App\Models\AiChat;
use App\Models\AiChatMessage;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\Widget;
use Illuminate\Support\Facades\File;

class DashboardGenerator
{
    public $chat;
    public $message;
    public $widgets;
    public $storage;
    public $dashboard;
    public $schema;
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
        $this->schema=file_get_contents($this->storage . '/extracted_data/schema.json');

        $this->widgets=Widget::all();
        $this->dashboard=Dashboard::query()->create(
            [
                'chat_id'=>$this->chat->id,
                'company_id' =>$this->chat->user->company_id,
                'name'=>'sas',
                'status'=>'generating',
            ]
        );

       $this->generateWidgets($this->message->message);
        $this->generateContentToWidgets();
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
Роль: Ведущий аналитик данных и архитектор дашбордов.

Входные данные: схема таблицы, список доступных типов виджетов и аналитический запрос пользователя.

### 1. СХЕМА ДАННЫХ ДЛЯ ВИЗУАЛИЗАЦИИ:
$schema

### 2. СПИСОК ДОСТУПНЫХ ТИПОВ ВИДЖЕТОВ (Используй ТОЛЬКО 'name' из этого списка):
$widgets

### 3. ЗАПРОС ПОЛЬЗОВАТЕЛЯ:
"{$text}"

### ЗАДАЧА:
Сформируй полноценную, профессиональную структуру аналитического дашборда, которая максимально глубоко и разносторонне раскрывает запрос пользователя.

Если запрос комплексный (содержит несколько сущностей или срезов, например: "по регионам и по категориям"), ты ДОЛЖЕН разбить его на отдельные логические компоненты.

**КРИТЕРИЙ КАЧЕСТВА — РАЗНООБРАЗИЕ И СТРУКТУРА:**
Не ограничивайся одинаковыми графиками. Прояви гибкость и сочетай РАЗНЫЕ типы виджетов из списка для лучшего UX:
1. Ипользуй агрегирующие виджеты (например, Карточки / KPI), чтобы показать главные общие цифры (например, Общая сумма продаж).
2. Используй разные типы диаграмм для отображения отдельных срезов (один срез — один график), чтобы дашборд не выглядел монотонно.

Для каждого компонента создай отдельный объект виджета. Ты имеешь право и обязан ИСПОЛЬЗОВАТЬ ОДИН И ТОТ ЖЕ ТИП ВИДЖЕТА (`name`) ЛЮБОЕ КОЛИЧЕСТВО РАЗ, если это необходимо для раздельного отображения разных метрик или аналитических срезов. Каждая подзадача — это отдельный элемент в результирующем JSON-массиве.

### ТРЕБОВАНИЯ К ОТВЕТУ:
1. Выводи ТОЛЬКО валидный JSON-массив объектов. Никаких вводных слов, пояснений вокруг JSON или markdown-разметки (ЗАПРЕЩЕНО использовать ```json ... ```).
2. Ключи в JSON должны быть строго на латинице: "name", "title" и "instruction".
3. В "name" передавай точное значение 'name' из списка доступных виджетов.
4. В "title" пиши короткий, понятный пользователю заголовок конкретного графика (например: "Продажи по регионам").
4. В "instruction" напиши конкретную инструкцию для ии: как будет выглядет выиджет и что нужно сделать
### ЭТАЛОННЫЙ ФОРМАТ ОТВЕТА (JSON):
[
  {
    "name": "название_виджета_из_списка",
    "title": "Заголовок виджета",
    "instruction": ""
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
    }

    public function generateContentToWidgets()
    {
        $text=$this->message->message;

        $widgets_dash = DashboardWidget::query()->with('widget')
            ->where('dashboard_id', $this->dashboard->id)->get();

        foreach ($widgets_dash as $index=>$widget) {
            $this->generateContentWidget($widget,$index);
        }
        dd($text);

    }
    public function generateContentWidget($dashboard_widget,$position)
    {



        $system = <<<TEXT
Ты опытный Python Data Analyst. Твоя задача — писать чистый, готовый к выполнению Python-код без какого-либо сопроводительного текста, комментариев или Markdown-разметки (не используй ```python). Код должен быть полностью автономным.
TEXT;

        $prompt = <<<TEXT
Ты пишешь ТОЛЬКО валидный Python-код без комментариев, объяснений и Markdown.

ЗАПРЕЩЕНО:
- любые пояснения
- любые markdown блоки
- текст вне Python-кода

ЗАДАЧА:
Есть JSON файл с данными.

ПУТЬ К ФАЙЛУ ПЕРЕДАЁТСЯ ЧЕРЕЗ АРГУМЕНТ КОМАНДНОЙ СТРОКИ:
--path=data.json

ФОРМАТ ВХОДНЫХ ДАННЫХ:
{$this->schema}

ИНСТРУКЦИЯ ОБРАБОТКИ:
{$dashboard_widget->instruction}

ЦЕЛЕВОЙ ФОРМАТ ВЫХОДА:
{$dashboard_widget->widget->scheme}

ТРЕБОВАНИЯ:
1. Используй только стандартную библиотеку Python (json, collections, datetime при необходимости) и pandas тоже исползую.
2. Считай файл json из --path, не исползуй другие агрументы.
3. Преобразуй данные строго по инструкции
4. НЕ изменяй названия ключей, только значения
5. Результат должен строго соответствовать целевой JSON схеме
6. Сохрани резултат в extract.json
7. В конце виведи 'ok'

ВАЖНО:
- код должен запускаться без ошибок
- никаких заглушек
- никаких комментариев
TEXT;
        $pythonCode = (new AIService(
            responseFormat: 'text',
        ))->ask($prompt, $system);

        $path = $this->storage.'/dashboard/widgets/'.$dashboard_widget->id.'/generated_script.py';

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $pythonCode);

        $dashboard_widget->status="active";
        $dashboard_widget->position=$position;
        $dashboard_widget->save();
    }

}
