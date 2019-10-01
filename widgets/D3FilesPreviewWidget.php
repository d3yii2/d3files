<?php

namespace d3yii2\d3files\widgets;

use d3system\exceptions\D3Exception;
use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\assetbundles\AjaxAsset;
use eaBlankonThema\widget\ThButton;
use eaBlankonThema\widget\ThModal;
use Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;

/**
 * Class D3FilesPreviewWidget
 * @package d3yii2\d3files\widgets
 * Usage by model \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['model' => $model])
 * Usage by fileList (attachments are joined already) \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['fileList' => $fileList])
 * @var string $viewType
 * @var string $dialogWidgetClass
 * @var array $pdfObjectOptions
 * @var string VIEW_MODAL_BUTTON
 * @var string VIEW_INLINE_BUTTON
 * @var string VIEW_IFRAME
 * @var string VIEW_TYPE_MODAL
 * @var string VIEW_TYPE_INLINE
 * @var string EMBED_CONTENT_CLASS
 */
class D3FilesPreviewWidget extends D3FilesWidget
{
    public $icon = 'glyphicon glyphicon-eye-open';
    public $viewByExtensions = ['pdf'];
    public $viewExtension = 'pdf';
    public $currentFile;
    public $showPrevNext = false;
    public $prevFile;
    public $nextFile;
    public $dialogWidgetClass = 'eaBlankonThema\widget\ThModal';
    public $pdfObjectOptions = [];
    public $viewType = self::VIEW_TYPE_MODAL;
    public $buttonView = self::VIEW_MODAL_BUTTON;

    public static $contentTargetSelector = self::EMBED_CONTENT_CLASS;

    public const VIEW_MODAL_BUTTON = '_modal_button';
    public const VIEW_INLINE_BUTTON = '_inline_button';
    public const VIEW_IFRAME = '_iframe';
    public const VIEW_TYPE_MODAL = 'modal';
    public const VIEW_TYPE_NONE = 'none';
    public const VIEW_TYPE_INLINE = 'inline';
    public const EMBED_CONTENT_CLASS = 'd3files-embed-content';
    public const PREVIEW_BUTTON_CLASS = 'd3files-preview-button';

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

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

        if (self::VIEW_TYPE_NONE !== $this->viewType) {

            if (self::VIEW_TYPE_MODAL !== $this->viewType) {
                $this->buttonView = self::VIEW_INLINE_BUTTON;
                $this->pdfObjectOptions = ['wrapperHtmlOptions' => ['style' => 'height:1200px']];
            }

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

            if ($hasPdf) {
                $this->pdfObjectOptions = array_merge(
                    ['closeButtonOptions' => ['label' => Yii::t('d3files', 'Close')]],
                    $this->pdfObjectOptions
                );
            }
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
            if ($hasPdf && !isset(Yii::$app->view->params['PdfObjectRendered'])) {
                $this->pdfObjectOptions['targetElementClass'] = self::EMBED_CONTENT_CLASS;
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
     * @return array
     * @throws Exception
     */
    public function getViewParams(): array
    {
        $firstViewFile = self::getFirstFileHavingExt($this->fileList, $this->viewExtension);
        $params = array_merge(
            parent::getViewParams(),
            [
                'file' => $firstViewFile,
                'modelId' => $this->model_id,
                'showPrevNext' => $this->showPrevNext,
                'viewType' => $this->viewType,
                'embedContent' => $this->getPdfContent(),
                'previewButton' => $this->buttonView,
            ]
        );

        return $params;
    }

    /**
     * Get element data attributes for modal or inline box scripts
     * @param string $attachmentUrl
     * @param string $contentTargetSelector
     * @return array
     */
    public static function getPreviewButtonDataAttributes(string $attachmentUrl, string $contentTargetSelector): array
    {
        $attrs = [
            'data-src' => $attachmentUrl,
            'data-content-target' => $contentTargetSelector,
        ];
        return $attrs;
    }

    /**
     * @param int $modelId
     * @param array $file
     * @param array $fileList
     * @param string $urlPrefix
     * @return array
     */
    public static function getPreviewButtonAttributes(
        int $modelId,
        array $file,
        array $fileList = [],
        string $urlPrefix = ''
    ): array {
        $ext = parent::getFileExtension($file);
        $fileUrl = $fileUrl = [
            $urlPrefix . 'd3filesopen',
            'id' => $file['file_model_id']
        ];

        $attrs = self::getPreviewButtonDataAttributes(
            Url::to($fileUrl),
            self::$contentTargetSelector
        );

        if ('pdf' !== $ext) {
            $attrs['data-type'] = 'ajaxbox';
        }

        $attrs['title'] = \Yii::t('d3files', 'Preview atachment');
        $attrs['id'] = 'd3files-preview-button-' . $modelId;
        $attrs['class'] = 'pdf' === $ext ? PDFObject::LOAD_BUTTON_CLASS : self::PREVIEW_BUTTON_CLASS;
        $attrs['data-row-id'] = $modelId;
        $attrs['data-files-list'] = Json::encode(
            [
                'modelId' => $file['file_model_id'],
                'files' => $fileList
            ]
        );

        return $attrs;
    }

    /**
     * @param int $modelId
     * @param array $file
     * @param array $fileList
     * @param string $urlPrefix
     * @return array
     */
    public static function getPreviewModalButtonAttributes(int $modelId, array $file, array $fileList = [], string $urlPrefix = ''): array
    {
        self::$contentTargetSelector = '.' . ThModal::MODAL_CONTENT_CLASS;

        $attrs = self::getPreviewButtonAttributes($modelId, $file, $fileList, $urlPrefix);
        $attrs['data-toggle'] = 'modal';
        $attrs['data-target'] = '#' . ThModal::MODAL_ID;

        return $attrs;
    }

    /**
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function getPdfContent(array $options = []): string
    {
        $pdfOptions = array_merge($this->pdfObjectOptions, $options);

        return PDFObject::widget($pdfOptions);
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