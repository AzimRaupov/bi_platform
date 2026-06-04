<?php

namespace App\Helpers\Dashboard\Builder;

class MultiSeriesTrendTemplate
{
    public $widget;
    public $series;
    public $labels;
    public $template;
    public $data;
    public $view;
    public function __construct($widget,$data){
        $this->template = 'components.widgets.multi-series-trend';
        $this->widget = $widget;
        $this->data = $data;
        $this->labels = $data["labels"];
        $this->series = $data["series"];
        dd($this->data);
        $this->view=view($this->template,['widget'=>$widget,'series'=>$this->series,'labels'=>$this->labels])->render();
        return 'ok';
    }
}
