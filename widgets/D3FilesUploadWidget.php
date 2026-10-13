<?php
declare(strict_types=1);

namespace d3yii2\d3files\widgets;

use d3system\widgets\D3Widget;
use Exception;
use kartik\widgets\FileInput;
use Throwable;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use Yii;
use yii\widgets\ActiveField;
use yii\widgets\ActiveForm;
use function get_class;

/**
 * Class D3FilesUploadWidget
 * @package d3yii2\d3files\widgets
 * Documentation: https://demos.krajee.com/widget-details/fileinput
 */
class D3FilesUploadWidget extends D3Widget
{
    public ?ActiveRecord $model = null;

    public ?ActiveForm $form = null;

    public string $name = 'upload_file';

    public ?string $modelName = null;

    public ?int $modelId = null;

    public ?bool $ajaxUpload = null;

    public array $uploadExtraData = [];

    public int $maxFileCount = 3;

    public array $options = [];

    public array $pluginOptions = [];

    public ?bool $showUpload = null;

    public string $urlPrefix = '/d3files/d3files/';

    public string $controllerRoute = '';

    public bool $showPreview = true;

    public bool $showCaption = true;

    public bool $showRemove = true;

    public bool $addModelId = true;

    public bool $autostart = true;
    
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
        $disableController = Yii::$app->getModule('d3files')->disableController ?? false;
        if ($disableController) {
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
        if ($this->modelId && $this->ajaxUpload === null) {
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
                //'preferIconicPreview' => true,
                //'maxImageHeight' => '20',
            ];
            
            if ($this->ajaxUpload === true) {
                $this->pluginOptions['uploadUrl'] = Url::to($url);
                $this->pluginOptions['uploadAsync'] = true;
                $this->pluginOptions['uploadExtraData'] = $this->uploadExtraData;
    
                if ($this->showUpload === null && ! $this->autostart) {
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
     * @return FileInput|ActiveField
     * @throws Throwable
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

    /**
     * @return void
     */
    /**
     * @return void
     */
    public function initAssets(): void
    {
        $js = '
        var d3UploadField = $("[data-krajee-fileinput]");
        if ("undefined" !== typeof d3UploadField) {
            d3UploadField.fileinput().on("filebatchselected", function(event, files) {
                d3UploadField.fileinput("upload");
            });
        }';
        
        Yii::$app->view->registerJs($js);
    }
}
