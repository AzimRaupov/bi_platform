<?php

namespace App\Helpers\Dashboard\Builder;

class DonutChartTemplate
{
    public $id;
    public $series;
    public $labels;
    public $template;
    public $data;
    public $view;
    public function __construct($id,$data){
        $this->template = 'components.widgets.donut-chart';
        $this->id = $id;
        $this->data = $data;


        $this->labels = $data["labels"];
        $this->series = $data["series"];
        $this->view=view($this->template,['id'=>$id,'series'=>$this->series,'labels'=>$this->labels])->render();
        return 'ok';
    }
}
