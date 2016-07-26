<?php

namespace d3yii2\d3files\widgets;

use Yii;
use yii\base\Widget;
use d3yii2\d3files\D3Files;
use d3yii2\d3files\models\D3files as ModelD3Files;

class D3FilesWidget extends Widget
{
    public $model;
    public $model_name;
    public $model_id;
    public $title;
    public $icon;
    public $hideTitle;
    public $readOnly;

    protected $fileList;

    public function init()
    {
        parent::init();
        D3Files::registerTranslations();
        
        $this->model_name = $this->model->className();

        $this->fileList = ModelD3Files::fileListForWidget($this->model_name, $this->model_id);        
        
    }
    
    public function run()
    {
        
        if ($this->title === null) {
            $this->title = Yii::t('d3files', 'Attachments');
        }
        
        if ($this->icon === null) {
            $this->icon = "glyphicon glyphicon-paperclip";
        }

        //url prefix (module/controller)
        $url_prefix = '/d3files/d3files/';

        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $url_prefix = '';
        }

        if ($this->readOnly) {
            return $this->render(
                'files_readonly',
                [
                    'model_name' => $this->model_name,
                    'model_id'   => $this->model_id,
                    'title'      => $this->title,
                    'icon'       => $this->icon,
                    'hideTitle'  => $this->hideTitle,
                    'fileList'   => $this->fileList,
                    'url_prefix' => $url_prefix,
                ]
            );
        }

        return $this->render(
            'files',
            [
                'model_name' => $this->model_name,
                'model_id'   => $this->model_id,
                'title'      => $this->title,
                'icon'       => $this->icon,
                'hideTitle'  => $this->hideTitle,
                'fileList'   => $this->fileList,
                'url_prefix' => $url_prefix,
            ]
        );
        
    }
    
    public function getViewPath()
    {
        return dirname(__DIR__) . '/views/d3files/';
    }
}
