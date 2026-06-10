<?php

namespace App\Helpers\Dashboard\Builder;

use App\Helpers\PythonRunner;
use App\Models\AiChat;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;

class DashboardBuilder
{
    public $dashboard;
    public $chat;
    public $widgets;
    public $storage;
    public $layout;

    public function __construct($dashboard_id, $chat_id)
    {
        $this->layout = 'layouts.dashboard';
        $this->dashboard = Dashboard::query()->find($dashboard_id);
        $this->chat = AiChat::query()->with(['user', 'extractedData'])->find($chat_id);
        $this->widgets = DashboardWidget::query()->with('widget')->where('dashboard_id', $this->dashboard->id)->get();

        $this->storage = storage_path(
            'app/company/' .
            $this->chat->user->email .
            '/chats/' .
            $this->chat->id
        );
    }

    public function buildWidgets()
    {
        $content = "";

        foreach ($this->widgets as $widget) {
            if ($widget->status != 'draft') {
                continue;
            }

            try {
                $h1="
                <p class='h2 m-2'>$widget->title</p>
                ";
                $content.=$h1;
                $data_path = $this->storage . '/dashboard/widgets/' . $widget->id . '/extract.json';

                $data = json_decode(
                    json_encode(
                        iterator_to_array(Items::fromFile($data_path), true)
                    ),
                    true
                );
                if ($widget->widget->name == "pie-chart") {
                    $template = new PieChartTemplate($widget->id, $data);
                    $content .= $template->view;
                } elseif ($widget->widget->name == "multi-series-trend") {
                    $template = new MultiSeriesTrendTemplate($widget, $data);
                    $content .= $template->view;
                } elseif ($widget->widget->name == "donut-chart") {
                    $template = new DonutChartTemplate($widget->id, $data);
                    $content .= $template->view;
                } elseif ($widget->widget->name == "table") {
                    $template = new TableTemplate($widget->id, $data, $widget);
                    $content .= $template->view;
                } elseif ($widget->widget->name == "mini-counters") {
                    $template = new MiniCountersTemplate($data, $widget);
                    $content .= $template->view;
                } elseif ($widget->widget->name == "scatter-plot") {
                    $template = new ScatterPlotTemplate($data, $widget);
                    $content .= $template->view;
                }
            } catch (\Throwable $e) {
                Log::error("Widget {$widget->id} failed", [
                    'widget_id' => $widget->id,
                    'type' => $widget->widget->name,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $widget->status = 'failed';
                $widget->save();

                continue;
            }
        }

//        $html = view($this->layout, [
//            'content' => $content
//        ])->render();

        file_put_contents(
            $this->storage . '/dashboard/content.blade.php',
            $content
        );
    }

    public function runScripts()
    {
        $results = [];
        foreach ($this->widgets as $widget) {
            $status = 'draft';
            $path_python = $this->storage . '/dashboard/widgets/' . $widget->id . '/generated_script.py';

            $run = new PythonRunner($path_python, $this->chat->extractedData->json_path);
            $result_run = $run->run();
            $results[] = $result_run;

            if (!isset($result_run["output"][0]) || $result_run["output"][0] != "ok") {
                $status = 'failed';
                Log::error("Ошибка выполнения Python скрипта для виджета {$widget->id}: ", $result_run["output"] ?? []);
            }

            $widget->status = $status;
            $widget->save();
        }
        return $results;
    }


}
