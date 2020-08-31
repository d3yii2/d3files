<?php

namespace d3yii2\d3files\widgets;

use d3system\widgets\D3Widget;
use Exception;
use kartik\widgets\FileInput;
use yii\helpers\Url;
use Yii;

/**
 * Class D3FilesUploadWidget
 * @package d3yii2\d3files\widgets
 * Documentation: https://demos.krajee.com/widget-details/fileinput
 */
class D3FilesUploadWidget extends D3Widget
{
    public $model;
    public $form;
    public $name = 'upload_file';
    public $modelName;
    public $modelId;
    public $ajaxUpload = null;
    public $uploadExtraData = [];
    public $maxFileCount = 3;
    public $options;
    public $pluginOptions;
    public $showUpload = true;
    public $urlPrefix = '/d3files/d3files/';
    public $controllerRoute = '';
    public $showPreview = true;
    public $showCaption = true;
    public $showRemove = true;
    public $addModelId = true;
    
    /**
     * @throws Exception
     */
    public function init(): void
    {
        if (!$this->options) {
            $this->options = ['multiple' => true, 'accept' => 'image/*'];
        }
    
        if ($this->model && property_exists($this->model, 'd3filesControllerRoute')) {
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }
    
        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $this->urlPrefix = $this->controllerRoute;
        }
    
        $url = [$this->urlPrefix . 'd3filesupload'];
    
        // If enabled, get the id from model primary key
        if ($this->addModelId) {
            if (!$this->modelId && isset($this->model->primaryKey)) {
                $this->modelId = $this->model->primaryKey;
            }
            $url['id'] = $this->modelId;
        }
    
        // Automatically enable ajax upload for existing record if not set
        if ($this->modelId && null === $this->ajaxUpload) {
            $this->ajaxUpload = true;
        }
    
        if (empty($this->uploadExtraData)) {
            if (!$this->modelName) {
                $this->modelName = $this->model ? get_class($this->model) : null;
            }
        
            $this->uploadExtraData['model_name'] = $this->modelName;
        }
        
        if (!$this->pluginOptions) {
            $this->pluginOptions = [
                'encodeUrl' => false,
                'showUpload' => $this->showUpload,
                'maxFileCount' => $this->maxFileCount,
                'showPreview' => $this->showPreview,
                'showCaption' => $this->showCaption,
                'showRemove' => $this->showRemove,
            ];
            
            if (true === $this->ajaxUpload) {
                $this->pluginOptions['uploadUrl'] = Url::to($url);
                $this->pluginOptions['uploadExtraData'] = $this->uploadExtraData;
                
            }
            
            
            if (!$this->showUpload) {
                $this->pluginOptions['fileActionSettings'] = ['showUpload' => false];
            }
        }
    }
    
    /**
     * @return string|void
     * @throws Exception
     */
    public function run()
    {
        return $this->form
            ? $this->form->field($this->model, $this->name)->widget(
                FileInput::class,
                [
                    'options' => $this->options,
                    'pluginOptions' => $this->pluginOptions,
                ]
            )
            : FileInput::widget(
                [
                    'name' => $this->name,
                    'options' => $this->options,
                    'pluginOptions' => $this->pluginOptions
                ]
            );
    }
}
