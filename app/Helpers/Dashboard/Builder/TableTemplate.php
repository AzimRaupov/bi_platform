<?php

namespace App\Helpers\Dashboard\Builder;

class TableTemplate
{
    public $id;
    public $template;
    public $data;
    public $view;
    public $widget;
    public function __construct($id,$data,$widget){
        $this->template = 'components.widgets.table';
        $this->id = $id;
        $this->data = $data;
       $this->widget = $widget;
        $this->view=view($this->template,['id'=>$id,'data'=>$this->data,'widget'=>$widget])->render();
        return 'ok';
    }
}
