<?php

namespace app\vendor\dbrisinajumi\d3files\widgets;

use yii\base\Widget;
use app\vendor\dbrisinajumi\d3files\models\D3filesSearch;

class D3FilesWidget extends Widget
{
    public $model;
    public $model_name;
    public $model_id;
    public $title;
    public $icon;
    public $hideTitle;
    public $readOnly;
    
    protected $dataProvider;

    public function init()
    {
        parent::init();
        
        $reflection       = new \ReflectionClass($this->model);
        $this->model_name = $reflection->getShortName();
        $searchModel      = new D3filesSearch();
        
        $this->dataProvider = $searchModel->search(
            [
                'D3filesSearch' =>
                    [
                        'model_name' => $this->model_name,
                        'model_id'   => $this->model_id,
                        'deleted'    => 0,
                    ]
            ]
        );
    }

    public function run()
    {
        
        if ($this->title === null) {
            $this->title = 'Attachments';
        }
        
        if ($this->icon === null) {
            $this->icon = "glyphicon glyphicon-paperclip";
        }
        
        return $this->render(
            'files',
            [
                'model_name'   => $this->model_name,
                'model_id'     => $this->model_id,
                'title'        => $this->title,
                'icon'         => $this->icon,
                'hideTitle'    => $this->hideTitle,
                'readOnly'     => $this->readOnly,
                'dataProvider' => $this->dataProvider,
            ]
        );
        
    }
}
