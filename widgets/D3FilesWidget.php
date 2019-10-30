<?php

namespace d3yii2\d3files\widgets;

use d3yii2\d3files\D3Files;
use d3yii2\d3files\models\D3files as ModelD3Files;
use Exception;
use Yii;
use yii\base\Widget;
use yii\db\ActiveRecord;

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
class D3FilesWidget extends Widget
{
    public $model;
    public $model_name;
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
    public $viewByFancyBoxExtensions = ['pdf'];
    //File extensions to show
    public $viewByExtensions = ['pdf'];
    public $fileList;
    // Implented only in ea\eablankonthema\d3files_views\d3files\files_readonly.php
    public $actionColumn;
    public $urlPrefix = '/d3files/d3files/';

    public const VIEW_FILES_LIST = 'files-list';

    /**
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function init(): void
    {
        D3Files::registerTranslations();

        if (property_exists($this->model, 'd3filesControllerRoute')) {
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }

        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $this->urlPrefix = $this->controllerRoute;
        }

        if (!$this->model_name) {
            $this->model_name = get_class($this->model);
        }

        if (!$this->model_id && $this->model) {
            $this->model_id = $this->model->primaryKey;
        }

        // Load the file list if has not been set in constructor
        if (!$this->fileList) {
            $this->fileList = ModelD3Files::fileListForWidget($this->model_name, $this->model_id);
        }

        if (!$this->readOnly) {
            $this->registerJsTranslations();
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

        try {
            return $this->render($this->view, $this->getViewParams());
        }catch (Exception $exception){
            Yii::error('D3FilesWidget:run Exception: ' . $exception->getMessage());
        }

        return '';
    }

    /**
     * Get the default params for view file
     * May be owerriden by D3FilesPreviewWidget
     * @return array
     */
    public function getViewParams(): array
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

    /**
     * Get file extension
     * @param array $file
     * @return string
     */
    public static function getFileExtension(array $file): string
    {
        return strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
    }

    /**
     * Return the first file of the fileList having extension
     * @param array $files
     * @param string $extension
     * @return array|null
     */
    public static function getFirstFileHavingExt(array $files, string $extension): array
    {
        foreach ($files as $file) {
            $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
            if ($extension === $fileExtension) {
                return $file;
            }
        }
        return [];
    }

    /**
     * Filter the files by extension
     * @param array $files
     * @param string $ext
     * @return array
     */
    public static function getFilesListByExt(array $files, string $ext): array
    {
        $list = [];
        foreach ($files as $file) {
            $fileExt = self::getFileExtension($file);
            if ($ext === $fileExt) {
                $list[$file['id']] = $file;
            }
        }
        return $list;
    }

    public function registerJsTranslations()
    {
        $i18n = \yii\helpers\Json::encode([
            'aria_label' => Yii::t('d3files', 'Close'),
            'confirm' => Yii::t('d3files', 'Are you sure you want to delete this item?'),
            'no_results' => Yii::t('d3files', 'No results found.'),
        ]);
        Yii::$app->getView()->registerJs('var D3FilesVars = {i18n: ' . $i18n . '};', Yii::$app->getView()::POS_HEAD);
    }
}
