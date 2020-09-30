<?php

namespace d3yii2\d3files\widgets;

use d3system\widgets\D3Widget;
use d3yii2\d3files\components\D3Files;
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
    public $showUpload = null;
    public $urlPrefix = '/d3files/d3files/';
    public $controllerRoute = '';
    public $showPreview = true;
    public $showCaption = true;
    public $showRemove = true;
    public $addModelId = true;
    public $autostart = true;
    
    /**
     * @throws Exception
     */
    public function init(): void
    {
        if (!$this->options) {
            $this->options = ['multiple' => true];
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
                'overwriteInitial' => false,
                'initialPreviewAsData' => true,
            ];
            
            if (true === $this->ajaxUpload) {
                $this->pluginOptions['uploadUrl'] = Url::to($url);
                $this->pluginOptions['deleteUrl'] = Url::to([$this->urlPrefix . 'd3filesdelete']);
                $this->pluginOptions['uploadExtraData'] = $this->uploadExtraData;
    
                if (null === $this->showUpload && ! $this->autostart) {
                    $this->showUpload = true;
                    $this->pluginOptions['showUpload'] = true;
                }
                
                if ($this->autostart) {
                    $this->initAssets();
                }
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
    
    public function initAssets()
    {
        $js = '
        var d3UploadField = $("[data-krajee-fileinput]");
        if ("undefined" !== typeof d3UploadField) {
            d3UploadField.fileinput().on("filebatchselected", function(event, files) {
                d3UploadField.fileinput("upload");
            });
        }';
        
        Yii::$app->view->registerJs($js, Yii::$app->view::POS_READY);
    }
}
