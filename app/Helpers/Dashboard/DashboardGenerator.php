<?php

namespace App\Helpers\Dashboard;

use App\Helpers\Ai\AIService;
use App\Helpers\PythonRunner;
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

    public function __construct($chat_id, $message_id)
    {
        $this->chat = AiChat::query()->with('user','extractedData')->find($chat_id);
        $this->message = AiChatMessage::query()->find($message_id);

        $this->storage = storage_path(
            'app/company/'.
            $this->chat->user->email.
            '/chats/'.
            $this->chat->id
        );
        $this->schema = file_get_contents($this->storage.'/extracted_data/schema.json');

        $this->widgets = Widget::all();
        $this->dashboard = Dashboard::query()->create(
            [
                'chat_id' => $this->chat->id,
                'company_id' => $this->chat->user->company_id,
                'name' => 'sas',
                'status' => 'generating',
            ]
        );

    }
    public function getDashboard()
    {
        return $this->dashboard;
    }

    public function generateWidgets()
    {
        $text=$this->message->message;
        $widgetsList = $this->widgets->select(['name', 'description']);
        $widgets = json_encode($widgetsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $salesReport = json_decode(file_get_contents($this->storage.'/extracted_data/schema.json'), true);
        $schema = json_encode($salesReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $system = <<<'TEXT'
Ты — Senior Data Analyst и эксперт по BI-системам. Твоя цель — проанализировать структуру данных, сопоставить её с запросом пользователя и выбрать наиболее эффективные инструменты визуализации из доступного списка.
TEXT;

        // Пользовательский промт: добавлен Few-Shot пример, правила генерации и защита от "лишнего текста"
        $prompt = <<<TEXT
Роль: Ведущий аналитик данных и архитектор профессиональных дашбордов.

Входные данные: схема таблицы, список доступных типов виджетов и аналитический запрос пользователя.

### 1. СХЕМА ДАННЫХ ДЛЯ ВИЗУАЛИЗАЦИИ:
$schema

### 2. СПИСОК ДОСТУПНЫХ ТИПОВ ВИДЖЕТОВ (Используй ТОЛЬКО 'name' из этого списка):
$widgets

### 3. ЗАПРОС ПОЛЬЗОВАТЕЛЯ (ГЛАВНЫЙ ПРИОРИТЕТ):
"{$text}"

### ЗАДАЧА:
Сформируй полноценную, профессиональную структуру аналитического дашборда, которая максимально глубоко и разносторонне раскрывает запрос пользователя.

**ЗАПРОС ПОЛЬЗОВАТЕЛЯ ИМЕЕТ ВЫСШИЙ ПРИОРИТЕТ.** Все создаваемые виджеты, метрики и срезы должны напрямую отвечать на этот запрос, используя только существующие поля из СХЕМЫ ДАННЫХ. Не придумывай метрики, которых нет в схеме, и не уходи от сути запроса.

Если запрос комплексный (содержит несколько сущностей или срезов, например: "по регионам и по категориям"), ты ДОЛЖЕН разбить его на отдельные логические компоненты. Каждый компонент — это отдельный объект в результирующем JSON-массиве.

### КРИТЕРИЙ КАЧЕСТВА — РАЗНООБРАЗИЕ И СТРУКТУРА:
1. Используй агрегирующие виджеты (например, Карточки / KPI), чтобы показать главные общие цифры из запроса пользователя (например, Общая сумма продаж).
2. Используй разные типы диаграмм для отображения отдельных срезов из запроса (один срез — один график), чтобы дашборд не выглядел монотонно.
3. Ты имеешь право использовать один и тот же тип виджета (`name`) несколько раз, НО только для абсолютно разных данных, метрик или срезов.

### СТРОГИЕ ЗАПРЕТЫ И ПРАВИЛА УНИКАЛЬНОСТИ (КРИТИЧЕСКИ ВАЖНО):
1. **ЗАПРЕЩЕНО дублировать логику:** Каждый объект в JSON-массиве должен отвечать за СВОЙ уникальный срез данных, бренд, регион или метрику. Дублирование объектов с одинаковым смыслом, но разными виджетами — это грубая ошибка.
2. **ЗАПРЕЩЕНО копировать заголовки:** Каждое поле "title" должно быть строго уникальным и точно описывать конкретный график, созданный под запрос пользователя.
3. **ФИЗИЧЕСКИЙ СМЫСЛ ИНСТРУКЦИЙ:** Инструкция ("instruction") должна строго соответствовать конструктивным особенностям выбранного виджета.
   - НЕ пиши про "вывод суммы в центре отверстия" для `pie_chart` (круговой диаграммы без дырки), это свойство есть только у `donut_chart`.
   - НЕ пиши про "оси X и Y" или "тренды времени" для круговых диаграмм или KPI-карточек.
   - Каждая инструкция должна быть написана с нуля под конкретный виджет и конкретную задачу.

### ТРЕБОВАНИЯ К ОТВЕТУ:
1. Выводи ТОЛЬКО валидный JSON-массив объектов. Никаких вводных слов, пояснений вокруг JSON или markdown-разметки (ЗАПРЕЩЕНО использовать ```json ... ```).
2. Ключи в JSON должны быть строго на латинице: "name", "title" и "instruction".
3. В "name" передавай точное значение 'name' из списка доступных виджетов.
4. В "title" пиши короткий, понятный пользователю заголовок конкретного графика (например: "Продажи по регионам").
5. В "instruction" напиши конкретную инструкцию для ИИ-генератора: как физически должен выглядеть этот виджет, какие именно поля из схемы виджета он использует для выполнения запроса пользователя, и что конкретно нужно сделать.

### ЭТАЛОННЫЙ ФОРМАТ ОТВЕТА (JSON):
[
  {
    "name": "название_виджета_из_списка",
    "title": "Уникальный заголовок виджета",
    "instruction": "Уникальная инструкция, описывающая решение запроса пользователя через физические свойства этого виджета и поля из схемы данных"
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
                'widget_id' => $widget->id,
                'title' => $list['title'],
                'instruction' => $list['instruction'],
            ]);
        }
    }

    public function generateContentToWidgets()
    {
        $widgets_dash = DashboardWidget::query()->with('widget')
            ->where('dashboard_id', $this->dashboard->id)->get();

        $results = [];

        foreach ($widgets_dash as $index => $widget) {
            $results[] = $this->generateContentWidget($widget, $index);
        }

        return $results;

    }

    public function generateContentWidget($dashboard_widget, $position)
    {
        $system = <<<'TEXT'
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

НАЗНАЧЕНИЕ КЛЮЧЕЙ:
{$dashboard_widget->widget->scheme_description}

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
- Поля даты и времени могут быть в любом непредсказуемом формате. Обрабатывай их как обычные строки, если формат явно не указан в инструкции.
- никаких комментариев
TEXT;

        $pythonCode = (new AIService(
            responseFormat: 'text',
        ))->ask($prompt, $system);

        $pythonCode = trim((string) $pythonCode);
        $pythonCode = preg_replace('/^```(?:python)?\s*/i', '', $pythonCode);
        $pythonCode = preg_replace('/\s*```$/', '', $pythonCode);
        $pythonCode = preg_replace('/["\']\s*$/', '', $pythonCode);

        $path = $this->storage.'/dashboard/widgets/'.$dashboard_widget->id.'/generated_script.py';

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $pythonCode);

        $path_data = $this->chat->extractedData?->json_path;

        if (! $path_data || ! file_exists($path_data)) {
            throw new \RuntimeException(
                'Не найден путь к JSON данным для Python: '.var_export($path_data, true)
            );
        }

        $dashboard_widget->status = 'active';
        $dashboard_widget->position = $position;
        $dashboard_widget->save();
        return $dashboard_widget;
    }
}
