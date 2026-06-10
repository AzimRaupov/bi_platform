<?php

namespace App\Jobs;

use App\Helpers\Dashboard\Builder\DashboardBuilder;
use App\Helpers\Dashboard\DashboardGenerator;
use App\Helpers\DataHandlers\TableDataHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratorDashboardJob implements ShouldQueue
{
    use Queueable;
    public $chat_id;
    public $message_id;
    public $upload_id;
    public $timeout = 600;
    /**
     * Create a new job instance.
     */
    public function __construct($message_id, $chat_id, $upload_id)
    {
        $this->message_id = $message_id;
        $this->chat_id = $chat_id;
        $this->upload_id = $upload_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $builder=new DashboardBuilder(1,1);
        $builder->runScripts();
        $builder->buildWidgets();
        dd($builder);

        $save_handler = new TableDataHandler($this->message_id, $this->upload_id, $this->chat_id);
        $result = $save_handler->end();

        $dashboard_generate=new DashboardGenerator($this->chat_id, $this->message_id);
        $dashboard_generate->generateWidgets();
        $dashboard_generate->generateContentToWidgets();
        $dashboard=$dashboard_generate->getDashboard();









    }
}
