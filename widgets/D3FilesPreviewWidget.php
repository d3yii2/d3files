<?php

namespace d3yii2\d3files\widgets;

use d3system\exceptions\D3Exception;
use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\d3files\models\D3files as ModelD3Files;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\assetbundles\AjaxAsset;
use eaBlankonThema\widget\ThModal;
use Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;
use d3yii2\d3files\components\D3Files;

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
 *
 * @property null|string $modalTitle
 * @property string $filesDropdown
 * @property string $modalToolbarContent
 * @property string $assetsUrl
 */
class D3FilesPreviewWidget extends D3FilesWidget
{
    public $icon = self::DEFAULT_ICON;
    public $viewByExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
    public $viewByFancyBoxExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
    public $viewExtension = 'pdf';
    public $currentFile;
    public $showPrevNext = false;
    public $prevFile;
    public $nextFile;
    public $dialogWidgetClass = 'eaBlankonThema\widget\ThModal';
    public $pdfObjectOptions = [];
    public $showPrevNextButtons = false;
    public $viewType = self::VIEW_TYPE_MODAL;
    public $buttonView = self::VIEW_MODAL_BUTTON;
    public $contentTargetSelector = self::EMBED_CONTENT_CLASS;

    public const DEFAULT_ICON = 'glyphicon glyphicon-eye-open';

    public const VIEW_DROPDOWN_LIST = 'dropdown-list';
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
                $this->pdfObjectOptions = [
                    'wrapperHtmlOptions' => ['style' => 'height:1200px'],
                    //'targetElementClass' => self::EMBED_CONTENT_CLASS,
                ];
            }

            // Check for PDF and AJAX loaded attachments to  assets and assign preview attributes
            foreach ($this->fileList as $i => $file) {
                $ext = D3Files::getFileExtension($file);
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
        }
        $pageFooterHtml = null;

        // Render the PdfObject content in the footer if the files have PDF extension
        if ($hasPdf && !isset(Yii::$app->view->params['PdfObjectRendered'])) {
            PDFObject::widget($this->pdfObjectOptions);
            Yii::$app->view->params['PdfObjectRendered'] = true;
        }


        // Ensure modal preview is enabled and the layout rendered once
        if (self::VIEW_TYPE_MODAL === $this->viewType && !isset(Yii::$app->view->params['ThModalRendered'])) {

            if (is_callable($this->dialogWidgetClass)) {
                throw new D3Exception('Invalid Modal Dialog class: ' . $this->dialogWidgetClass);
            }

            $modalOptions = [];

            // Make modal 80% height of the page
            $modalOptions['dialogHtmlOptions'] = ['style' => 'height:80%'];
            $modalOptions['contentClass'] = PDFObject::CONTENT_CLASS;

            $modalOptions['title'] = $this->getModalTitle();
            $modalOptions['toolbarContent'] = $this->getModalToolbarContent();

            // Avoid rendering the HTML multiple times by widget second calls
            Yii::$app->view->params['ThModalRendered'] = true;

            $pageFooterHtml .= $this->dialogWidgetClass::widget($modalOptions);
        } else // Render the PdfObject content iframe in the footer if the files have PDF extension
            if((self::VIEW_TYPE_INLINE === $this->viewType) && $hasPdf) {

                $this->pdfObjectOptions = array_merge(
                    [
                        'closeButtonOptions' => ['label' => Yii::t('d3files', 'Close')],
                        'targetElementClass' => self::EMBED_CONTENT_CLASS,
                    ],
                    $this->pdfObjectOptions
                );
                echo $this->getPdfContent();
            }

        if ($pageFooterHtml) {
            Yii::$app->view->setPageFooter($pageFooterHtml);
        }

        Yii::$app->getView()->registerJsVar(
            'D3FilesPreviewJsVars',
            ['assetUrl' => $this->getAssetsUrl()]
        );
    }

    public function initFilesList()
    {
        // Load the file list if has not been set in constructor
        $fileList = is_array($this->fileList)
            ? $this->fileList
            : ModelD3Files::fileListForWidget($this->model_name, $this->model_id);

        $fl = [];
        foreach ($fileList as $i => $file) {
            if (!D3Files::hasViewExtension([$file], $this->viewByExtensions)) {
                continue;
            }
            $file['content-target'] = $this->contentTargetSelector;
            $file['src'] = Url::to(
                [
                    $this->urlPrefix . 'd3filesopen',
                    'id' => $file['file_model_id'],
                    'model_name' => $this->model_name,
                ],
                true
            );
            $fl[$i] = $file;
        }
        $this->fileList = $fl;
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function getAssetsUrl(): string
    {
        return Yii::$app->getAssetManager()->getBundle(D3FilesPreviewAsset::class)->baseUrl;
    }

    /**
     * @return string|null
     */
    public function getModalTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getModalToolbarContent(): string
    {
        $content = '';

        if ($this->showPrevNextButtons) {
            $content .= $this->getPrevNextFileButtons();
        }

        $content .= '
            <div class="pull-left">
                <span class="d3preview-model-files"></span>
           </div>
           <div class="d3preview-image-content" style="display: none"></div>
           ';

        return $content;
    }

    public function getFilesDropdown(): string
    {
        return '<span class="pull-right">
                <label for="getFilesDropdown">Files:</label>'
            . Html::dropDownList('getFilesDropdown', false, [], ['class' => 'd3files-preview-dropdown'])
            . '</span>';
    }

    /**
     * @return array|null
     */
    public function getViewParams(): ?array
    {
        $previewAttrs = [];
        if($file = D3Files::getFirstFileHavingExt($this->fileList, $this->viewExtension)) {
            $previewAttrs = self::VIEW_TYPE_MODAL === $this->viewType
                ? self::getPreviewModalButtonAttributes($this->model, $file, $this->fileList)
                : self::getPreviewInlineButtonAttributes($this->model, $file, $this->fileList);
        }
        $params = [
            'file' => $file,
            'modelId' => $this->model_id,
            'showPrevNext' => $this->showPrevNext,
            'viewType' => $this->viewType,
            'previewButton' => $this->buttonView,
            'previewButtonAttrs' => $previewAttrs,
            'hasPreview' => true,
        ];

        $params = array_merge(
            parent::getViewParams(),
            $params
        );
        return $params;
    }

    /**
     * Get element data attributes for modal or inline box scripts
     * @param ActiveRecord $model
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewButtonDataAttributes(ActiveRecord $model, array $file, array $files): array
    {
        return [
            'data-d3files-preview' => Json::encode(
                [
                    'active' => $file['id'],
                    'files' => $files,
                    'modelId' => $model->id,
                ]
            ),
        ];

    }

    /**
     * @param ActiveRecord $model
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewInlineButtonAttributes(ActiveRecord $model, array $file, array $files = []): array
    {
        return [
            'class' => PDFObject::LOAD_BUTTON_CLASS,
            'data-src' => $file['src'],
            'content-target' => self::EMBED_CONTENT_CLASS,
        ];

    }

    /**
     * @param ActiveRecord $model
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewModalButtonAttributes(ActiveRecord $model, array $file, array $files = []): array
    {
        $attrs = self::getPreviewButtonDataAttributes($model, $file, $files);
        $attrs['title'] = Yii::t('d3files', 'Preview atachment');
        $attrs['class'] = 'd3files-preview-widget-load ';
        $attrs['data-toggle'] = 'modal';
        //@FIXME - modal ID jāpadod widžetam parametros, lai nav atkarīgs no ThModal
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
        $pdfObjectOptions = array_merge($this->pdfObjectOptions, $options);

        return PDFObject::widget($pdfObjectOptions);
    }

    /**
     * @param array $data
     * @param array|null $currentFile
     * @return string
     * @throws Exception
     */
    public function getPrevNextFileButtons(?array $data = [], ?array $currentFile = null): string
    {
        $buttons = '<a id="w80" class="btn btn-success d3files-preview-prev-button">' . Yii::t('d3files',
                'Previous Attachment') . '</a>';

        $buttons .= '<a id="w80" class="btn btn-success d3files-preview-next-button">' . Yii::t('d3files',
                'Next Attachment') . '</a>';

        return $buttons;
    }
}