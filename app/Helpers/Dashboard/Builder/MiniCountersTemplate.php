<?php

namespace App\Helpers\Dashboard\Builder;

class MiniCountersTemplate
{
    public $template;
    public $data;
    public $view;
    public $widget;
    public $counters;
    public function __construct($data,$widget){
        $this->template = 'components.widgets.mini-counters';
        $this->data = $data;
        $this->widget = $widget;
        $this->counters = $data['counters'];
        $this->view=view($this->template,['counters'=>$this->counters,'widget'=>$widget])->render();
        return 'ok';
    }
}
