<?php

namespace d3yii2\d3files\widgets;

use d3system\exceptions\D3Exception;
use d3yii2\d3files\D3Files;
use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\d3files\models\D3files as ModelD3Files;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\assetbundles\AjaxAsset;
use eaBlankonThema\widget\ThButton;
use Exception;
use ReflectionClass;
use ReflectionException;
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
 * @var string $viewType
 * @var string $view
 * @var array $viewByExtensions
 * @var array $fileList
 * @var callable $actionColumn
 * @var string $urlPrefix
 * @var string $dialogWidgetClass
 * @var array $pdfObjectOptions
 * @var string VIEW_DROPDOWN_LIST
 * @var string VIEW_FILES_LIST
 * @var string VIEW_MODAL_BUTTON
 * @var string VIEW_INLINE_BUTTON
 * @var string VIEW_IFRAME
 * @var string VIEW_TYPE_MODAL
 * @var string VIEW_TYPE_INLINE
 * @var string EMBED_CONTENT_CLASS
 */
class D3FilesWidget extends Widget
{
    public $model;
    public $model_name;
    public $model_id;
    public $title;
    public /** @noinspection SpellCheckingInspection */
        $icon = 'glyphicon glyphicon-paperclip';
    public $hideTitle = false;
    public $readOnly;
    // File handling controller route. If empty, then use actual controller
    public $controllerRoute = '';

    /**
     * @deprecated $viewByFancyBox
     * @since 0.9.18
     * Fancybox window has been replaced with modal dialog
     * Use $viewType instead
     */
    public $viewByFancyBox = false;

    /** @example D3FilesWidget::VIEW_TYPE_MODAL */
    public $viewType = self::VIEW_TYPE_MODAL;
    public $view = self::VIEW_FILES_LIST;

    /**
     * @var array
     * @deprecated $viewByFancyBoxExtensions
     * Has been renamed since 0.9.18
     * Use $viewByExtensions instead
     */
    public $viewByFancyBoxExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'txt', 'html'];
    //File extensions allowed to view
    public $viewByExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'txt', 'html'];
    public $fileList;
    // Implented only in ea\eablankonthema\d3files_views\d3files\files_readonly.php
    public $actionColumn;
    public $urlPrefix = '/d3files/d3files/';
    public $dialogWidgetClass = 'eaBlankonThema\widget\ThModal';
    public $pdfObjectOptions = [];

    public const VIEW_DROPDOWN_LIST = 'dropdown-list';
    public const VIEW_FILES_LIST = 'files-list';
    public const VIEW_MODAL_BUTTON = '_modal_button';
    public const VIEW_INLINE_BUTTON = '_inline_button';
    public const VIEW_IFRAME = '_iframe';
    public const VIEW_TYPE_MODAL = 'modal';
    public const VIEW_TYPE_INLINE = 'inline';
    public const EMBED_CONTENT_CLASS = 'd3files-embed-content';

    private $modelAllowedExtensions;

    /**
     * @throws ReflectionException
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

        $this->modelAllowedExtensions = Yii::$app->getModule('d3files')->getModelAllowedExtensions($this->model);

        // Backward compatibility
        if ($this->viewByFancyBox) {
            $this->viewType = self::VIEW_TYPE_MODAL;
        }

        // Backward compatibility
        if ($this->viewByFancyBoxExtensions) {
            $this->viewByExtensions = $this->viewByFancyBoxExtensions;
        }

        $hasPdf = false;
        $hasAjax = false;

        // Check for PDF and AJAX loaded attachments to  assets
        foreach ($this->fileList as $i => $file) {
            $ext = self::getFileExtension($file);

            if (!in_array($ext, $this->viewByExtensions, true)) {
                continue;
            }

            if ('pdf' === $ext) {
                $hasPdf = true;
            } else {
                $hasAjax = true;
            }
        }

        if (self::VIEW_TYPE_MODAL === $this->viewType) {
            D3FilesPreviewAsset::register(Yii::$app->view);
        }

        // If AJAX load required, register the assets
        if ($hasAjax && !isset(Yii::$app->view->params['AjaxAssetRegistered'])) {
            AjaxAsset::register(Yii::$app->view);

            // Avoid the asets registering multiple times by widget second calls
            Yii::$app->view->params['AjaxAssetRegistered'] = true;
        }

        $pageFooterHtml = null;

        // Ensure modal preview is enabled and the layout rendered once
        if (self::VIEW_TYPE_MODAL === $this->viewType && !isset(Yii::$app->view->params['ThModalRendered'])) {

            if (is_callable($this->dialogWidgetClass)) {
                throw new D3Exception('Invalid Modal Dialog class: ' . $this->dialogWidgetClass);
            }

            $modalOptions = [];

            // Make modal 80% height of the page
            $modalOptions['dialogHtmlOptions'] = ['style' => 'height:80%'];

            $modalOptions['toolbarContent'] = $this->getPrevNextFileButtons();

            // Avoid rendering the HTML multiple times by widget second calls
            Yii::$app->view->params['ThModalRendered'] = true;

            // Render the PdfObject content iframe in the footer if the files have PDF extension
            if ($hasPdf
                // && !isset(Yii::$app->view->params['PdfObjectRendered'])
            ) {

                $this->pdfObjectOptions['showCloseButton'] = false;

                $modalOptions['content'] = $this->getPdfContent($this->pdfObjectOptions);

                // Avoid rendering the HTML multiple times by widget second calls
                Yii::$app->view->params['PdfObjectRendered'] = true;
            }
            $pageFooterHtml .= $this->dialogWidgetClass::widget($modalOptions);

        }

        if ($pageFooterHtml) {
            Yii::$app->view->setPageFooter($pageFooterHtml);
        }
    }

    /**
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function getPdfContent(array $options = []): string
    {
        $defaultOptions = [
            'closeButtonOptions' => [
                'label' => Yii::t('d3emails', 'Close')
            ]
        ];

        $pdfOptions = array_merge($defaultOptions, $options);

        return PDFObject::widget($pdfOptions);
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

        return $this->render(
            $this->view,
            [
                'model_name' => $this->model_name,
                'model_id' => $this->model_id,
                'title' => $this->title,
                'icon' => $this->icon,
                'hideTitle' => $this->hideTitle,
                'fileList' => $this->fileList,
                'urlPrefix' => $this->urlPrefix,
                'viewType' => $this->viewType,
                'viewByExtensions' => $this->viewByExtensions,
                'actionColumn' => $this->actionColumn,
                'readOnly' => $this->readOnly,
                'embedContent' => $this->getPdfContent(),
            ]
        );
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
    public static function getFirstFileHavingExt(array $files, string $extension): ?array
    {
        foreach ($files as $file) {
            $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
            if ($extension === $fileExtension) {
                return $file;
            }
        }
        return null;
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

    /**
     * @param array $fileList
     * @param array|null $currentFile
     * @return string
     * @throws Exception
     */
    public function getPrevNextFileButtons(?array $fileList = [], ?array $currentFile = null): string
    {

        $attrs = [
            //'data-target' => '#' . ThModal::MODAL_ID,
            //'data-content-target' => ThModal::MODAL_CONTENT_CLASS,
            'type' => ThButton::TYPE_SUCCESS,
            'label' => Yii::t('d3files', 'Previous Attachment'),
            'htmlOptions' => ['class' => 'd3files-preview-prev-button']
        ];

        //@FIXME - ThButton nevar padot css klasi (htmlOptions tiek pārrakstīts)
        //$buttons = ThButton::widget($attrs);
        $buttons = '<a id="w80" class="btn btn-success d3files-preview-prev-button">' . Yii::t('d3files',
                'Previous Attachment') . '</a>';

        //$attrs['label'] = Yii::t('d3files', 'Next Attachment');
        //$attrs['htmlOptions']['class'] = 'd3files-preview-next-button';

        $buttons .= '<a id="w80" class="btn btn-success d3files-preview-next-button">' . Yii::t('d3files',
                'Next Attachment') . '</a>';
        //$buttons .= ' ' . ThButton::widget($attrs);

        return $buttons;
    }
}
