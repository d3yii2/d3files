<?php

namespace d3yii2\d3files\widgets;

use d3system\widgets\D3Widget;
use d3yii2\d3files\components\D3Files;
use d3yii2\d3files\D3Files as D3FilesModule;
use d3yii2\d3files\models\D3filesModelName;
use Exception;
use Yii;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Class`D3FilesWidget`
 * @package d3yii2\d3files\widgets
 * @var ActiveRecord $model
 * @var string $model_name
 * @var int $model_id
 * @var string $title
 * @var string $icon
 * @var bool $hideTitle
 * @var bool $readOnly
 * @var string $controllerRoute
 * @var bool $viewByFancyBox
 * @var string $view
 * @var array $viewByExtensions
 * @var array $fileList
 * @var callable $actionColumn
 * @var string $urlPrefix
 * @var string VIEW_DROPDOWN_LIST
 * @var string VIEW_FILES_LIST
 *
 * @property array $viewParams
 */
class D3FilesWidget extends D3Widget
{
    public $model;
    public $model_name;
    public $nameModel;
    public $model_id;
    public $title;
    public $icon = 'glyphicon glyphicon-paperclip';
    public $hideTitle = false;
    public $readOnly = false;
    // File handling controller route. If empty, then use actual controller
    public $controllerRoute = '';

    /**
     * @deprecated $viewByFancyBox
     * @since 0.9.18
     * Fancybox window has been replaced with modal dialog
     * Use D3FilesPreviewWidget with $viewType instead
     */
    public $viewByFancyBox = false;
    public $view = self::VIEW_FILES_LIST;

    /**
     * @var array
     * @deprecated $viewByFancyBoxExtensions
     * Has been renamed since 0.9.18
     * Use $viewByExtensions instead
     */
    public $viewByFancyBoxExtensions;
    //File extensions to show
    public $viewByExtensions;
    public $fileList = [];
    // Implented only in ea\eablankonthema\d3files_views\d3files\files_readonly.php
    public $actionColumn;
    public $urlPrefix = '/d3files/d3files/';

    public $uploadButtonPlacement = self::BUTTON_PLACEMENT_RIGHT;

    public const VIEW_FILES_LIST = 'files-list';

    public const BUTTON_PLACEMENT_LEFT = 'left';
    public const BUTTON_PLACEMENT_RIGHT = 'right';

    /**
     * @throws Exception
     */
    public function init(): void
    {
        if (!$this->viewByExtensions) {
            $this->viewByExtensions = D3Files::getAllowedFileTypes();
        }

        D3FilesModule::registerTranslations();

        if ($this->model_name && $this->model_id) {
            // Find the record by model name and id
            $this->model = $this->model_name::findOne($this->model_id);
        }

        // Just exit if there is no model data (new record?)
        if (!$this->model || empty($this->model->primaryKey)) {
            return;
        }

        if (!$this->model_name) {
            $this->model_name = get_class($this->model);
        }

        if (property_exists($this->model, 'd3filesControllerRoute')) {
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }

        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $this->urlPrefix = $this->controllerRoute;
        }

        if (!$this->model_id) {
            $this->model_id = $this->model->primaryKey;
        }

        if (!$this->nameModel) {
            $this->nameModel = D3filesModelName::findOne(['name' => $this->model_name]);
        }

        $this->initFilesList();

        if (!$this->readOnly) {
            $this->registerJsTranslations();
        }
    }

    public function initFilesList()
    {
        // Load the file list if has not been set in constructor
        if (!$this->fileList) {
            $this->fileList = D3Files::getModelFilesList($this->model_name, $this->model_id);
        }
    }

    /**
     * @return string|void
     * @throws Exception
     */
    public function run()
    {
        if ($this->title === null) {
            $this->title = Yii::t('d3files', 'Attachments');
        }

        if (!$this->view) {
            return '';
        }

        try {
            $viewParams = $this->getViewParams();
            return $this->render($this->view, $viewParams);
        } catch (Exception $exception) {
            Yii::error('D3FilesWidget:run Exception message: ' . PHP_EOL . $exception->getMessage());
            Yii::error('D3FilesWidget:run Exception trace: ' . PHP_EOL . $exception->getTraceAsString());
            return Yii::t('d3files', 'Attachment error');
        }
    }

    /**
     * Get the default params for view file
     * May be owerriden by D3FilesPreviewWidget
     * @return array
     */
    public function getViewParams(): ?array
    {
        return [
            'model_name' => $this->model_name,
            'model_id' => $this->model_id,
            'title' => $this->title,
            'icon' => $this->icon,
            'hideTitle' => $this->hideTitle,
            'fileList' => $this->fileList,
            'urlPrefix' => $this->urlPrefix,
            'viewByExtensions' => $this->viewByExtensions,
            'actionColumn' => $this->actionColumn,
            'readOnly' => $this->readOnly,
            'uploadButtonPlacement' => $this->uploadButtonPlacement,
        ];
    }

    /**
     * @return string
     */
    public function getViewPath(): string
    {
        if (!$viewPath = Yii::$app->getModule('d3files')->viewPath) {
            $viewPath = dirname(__DIR__) . '/views';
        }
        return $viewPath . '/d3files/';
    }

    /**
     * Get the list of readed model files
     * @return array
     */
    public function getFileList(): array
    {
        return $this->fileList;
    }


    public function registerJsTranslations(): void
    {
        $i18n = Json::encode([
            'aria_label' => Yii::t('d3files', 'Close'),
            'confirm' => Yii::t('d3files', 'Are you sure you want to delete this item?'),
            'no_results' => Yii::t('d3files', 'No results found.'),
            'file_uploaded' => Yii::t('d3files', 'File uploaded successfully.'),
        ]);
        Yii::$app->getView()->registerJs('var D3FilesVars = {i18n: ' . $i18n . '};', Yii::$app->getView()::POS_HEAD);
    }
}
