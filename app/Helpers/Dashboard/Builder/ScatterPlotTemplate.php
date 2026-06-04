<?php

namespace App\Helpers\Dashboard\Builder;

class ScatterPlotTemplate
{
    public $template;
    public $data;
    public $view;
    public $widget;

    public $series;
    public $categories;
    public function __construct($data,$widget){
        $this->template ='components.widgets.scatter-plot';
        $this->data = $data;
        $this->widget = $widget;

        $this->series = $data["series"];
        $this->categories = $data["categories"];
        $this->view=view($this->template,['widget'=>$this->widget,'categories'=>$this->categories,'series'=>$this->series])->render();
        return 'ok';
    }

}
