<?php
declare(strict_types=1);

namespace d3yii2\d3files\widgets;

use d3system\exceptions\D3Exception;
use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\assetbundles\AjaxAsset;
use eaBlankonThema\widget\ThModal;
use Exception;
use Throwable;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;
use d3yii2\d3files\components\D3Files;
use yii\web\View;
use function is_callable;

/**
 * Class D3FilesPreviewWidget
 * @package d3yii2\d3files\widgets
 * Usage by model \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['model' => $model])
 * Usage by fileList (attachments are joined already) D3FilesPreviewWidget::widget(['fileList' => $fileList])
 * @property null|string $modalTitle
 * @property string $filesDropdown
 * @property string $modalToolbarContent
 * @property string $assetsUrl
 * @property string $prevNextFileButtons
 */
class D3FilesPreviewWidget extends D3FilesWidget
{
    /**
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
     * @property bool $useInColumn
     */

    public string $previewExtensions = '/(gif|pdf|jpe?g|png|txt)$/i';

    public array $viewExtension = ['pdf', 'jpg', 'txt'];

    public bool $showPrevNext = false;

    public string $dialogWidgetClass = 'eaBlankonThema\widget\ThModal';

    public array $pdfObjectOptions = [];

    public bool $showPrevNextButtons = false;

    public string $viewType = self::VIEW_TYPE_MODAL;

    public string $buttonView = self::VIEW_MODAL_BUTTON;

    public string $contentTargetSelector = self::EMBED_CONTENT_CLASS;

    public string $nextButtonLabel = '';

    public string $prevButtonLabel = '';

    public bool $useInColumn = false;

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

    private const MODAL_ID = 'D3FilesPreviewModal';

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function init(): void
    {
        parent::init();

        $this->icon = self::DEFAULT_ICON;

        // Backward compatibility
        if ($this->viewByFancyBox) {
            $this->viewType = self::VIEW_TYPE_MODAL;
        }

        $hasPreviewFiles = false;
        $hasPdf = false;
        $hasAjax = false;

        if (self::VIEW_TYPE_NONE !== $this->viewType) {
            if (self::VIEW_TYPE_MODAL !== $this->viewType) {
                $this->buttonView = self::VIEW_INLINE_BUTTON;
                $this->pdfObjectOptions = [
                    'wrapperHtmlOptions' => ['style' => 'height:800px'],
                    'targetElementClass' => self::EMBED_CONTENT_CLASS,
                ];
            }

            // Check for PDF and AJAX loaded attachments to  assets and assign preview attributes
            foreach ($this->fileList as $file) {
                $ext = D3Files::getFileExtension($file);
                if ($ext === 'pdf') {
                    $hasPdf = true;
                } else {
                    $hasAjax = true;
                }

                if (D3Files::hasFileWithExtension([$file], $this->previewExtensions)) {
                    $hasPreviewFiles = true;
                }
            }

            D3FilesPreviewAsset::register(Yii::$app->view);

            // If AJAX load required, register the assets
            if ($hasAjax && !isset(Yii::$app->view->params['AjaxAssetRegistered'])) {
                AjaxAsset::register(Yii::$app->view);

                // Avoid the asets registering multiple times by widget second calls
                Yii::$app->view->params['AjaxAssetRegistered'] = true;
            }
        }

        // Render the PdfObject content in the footer if the files have PDF extension
        // Another preview types (e.g. images are also loaded via PdfObject and some optimization is @TODO)
        if (($hasPdf || $hasPreviewFiles) && !isset(Yii::$app->view->params['PdfObjectRendered'])) {
            if ((self::VIEW_TYPE_INLINE === $this->viewType)) {
                $pdfObjectOptions = array_merge(
                    [
                        'closeButtonOptions' => ['label' => Yii::t('d3files', 'Close')],
                    ],
                    $this->pdfObjectOptions
                );
                echo PDFObject::widget($pdfObjectOptions);
            } else {
                PDFObject::widget($this->pdfObjectOptions);
            }
            Yii::$app->view->params['PdfObjectRendered'] = true;
        }

        // Ensure modal preview is enabled
        if (self::VIEW_TYPE_MODAL === $this->viewType) {

            $this->pdfObjectOptions['targetElementClass'] = ThModal::MODAL_CONTENT_CLASS;

            $modalOptions = [];

            // Force modal width/height
            $modalOptions['dialogHtmlOptions'] = ['style' => 'width:70%;height:80%'];
            $modalOptions['contentHtmlOptions'] = ['style' => 'height:100%'];
            $modalOptions['title'] = $this->getModalTitle();
            $modalOptions['toolbarContent'] = $this->getModalToolbarContent();


            if (class_exists($this->dialogWidgetClass)) {
                $this->dialogWidgetClass::widget($modalOptions);
            }
        }

        Yii::$app->getView()->registerJs(
            'document.D3FP = new $.D3FilesPreview();
                document.D3FP.setOption("prevNextButtons",  ' . ($this->showPrevNextButtons ? 'true' : 'false') . ');
                document.D3FP.reflow();',
            View::POS_READY,
            'd3fp'
        );

        Yii::$app->getView()->registerJsVar(
            'D3FilesPreviewJsVars',
            ['assetUrl' => $this->getAssetsUrl()]
        );
    }

    /**
     * @return void
     * @throws \yii\db\Exception
     */
    public function initFilesList(): void
    {
        parent::initFilesList();

        if (!$this->nameModel) {
            $this->nameModel = D3filesModelName::findOne(['name' => $this->model_name]);
        }

        // Rebuild the list adding some preview params to files
        foreach ($this->fileList as $i => $file) {
            if (D3Files::hasFileWithExtension([$file], $this->previewExtensions)) {
                $file['src'] = Url::to([
                    $this->urlPrefix . 'd3filesopen',
                    'model_name_id' => $this->nameModel->id,
                ]);
                $this->fileList[$i] = $file;
            }
        }
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
        $previewButtons = $this->showPrevNextButtons ? $this->getPrevNextFileButtons() : '';

        return '
            <div class="row">
                <div class="col-sm-4">
                    <div class="d3preview-counter pull-left">
                        <span class="d3preview-counter-i"></span>
                        <span class="d3preview-counter-from">' . Yii::t('d3files', 'from') . '</span>
                        <span class="d3preview-counter-total"></span>
                    </div>
                    <span class="d3preview-model-files pull-left"></span>
                </div>
                <div class="col-sm-5 text-center">' . $previewButtons . '</div>
           </div>
           <div class="row">
                <div class="d3preview-image-content" style="display: none"></div>
           </div>
           ';
    }

    /**
     * @return string
     */
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
        $params = [
            'showPrevNext' => $this->showPrevNext,
            'viewType' => $this->viewType,
            'previewButton' => $this->buttonView,
            'hasPreview' => true,
            'previewExtensions' => $this->previewExtensions,
            'previewFileList' => D3Files::getPreviewFilesList(
                $this->fileList,
                $this->previewExtensions,
                [
                    $this->urlPrefix . 'd3filesopen',
                    'model_name_id' => $this->nameModel->id ?? null,
                ]
            ),
        ];

        if (self::VIEW_MODAL_BUTTON === $this->viewFile || self::VIEW_INLINE_BUTTON === $this->viewFile) {
            foreach ($this->viewExtension as $extension) {
                if ($params['file'] = D3Files::getFirstFileHavingExt($this->fileList, $extension)) {
                    break;
                }
            }
        }

        return array_merge(
            parent::getViewParams(),
            $params
        );
    }

    /**
     * Get element data attributes for modal or inline box scripts
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewButtonDataAttributes(array $file, array $files): array
    {
        return [
            'data-d3files-preview' => Json::encode(['files' => $files, 'modelId' => $file['model_id']]),
            'data-file-id' => $file['id'],
            'data-model-id' => $file['model_id'],
        ];
    }

    /**
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewInlineButtonAttributes(array $file, array $files = []): array
    {
        $attrs = self::getPreviewButtonDataAttributes($file, $files);
        $attrs['title'] = Yii::t('d3files', 'Preview atachment');
        $attrs['class'] = 'd3files-preview-widget-load ';

        return $attrs;
    }

    /**
     * @param array $file
     * @param array $files
     * @return array
     */
    public static function getPreviewModalButtonAttributes(array $file, array $files = []): array
    {
        $attrs = self::getPreviewButtonDataAttributes($file, $files);
        $attrs['title'] = Yii::t('d3files', 'Preview atachment');
        $attrs['class'] = 'd3files-preview-widget-load ';
        $attrs['data-toggle'] = 'modal';
        $attrs['data-target'] = '#' . ThModal::MODAL_ID;

        return $attrs;
    }

    /**
     * @param array $options
     * @return string
     * @throws Throwable
     */
    public function getPdfContent(array $options = []): string
    {
        $pdfObjectOptions = array_merge($this->pdfObjectOptions, $options);

        return PDFObject::widget($pdfObjectOptions);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getPrevNextFileButtons(): string
    {
        $prevButtonLabel = $this->prevButtonLabel ?? Yii::t('d3files', 'Previous Attachment');
        $nextButtonLabel = $this->nextButtonLabel ?? Yii::t('d3files', 'Next Attachment');

        $buttons = '<a id="w80" class="btn btn-success d3files-preview-prev-button">' . $prevButtonLabel . '</a>';

        $buttons .= '<a id="w80" class="btn btn-success d3files-preview-next-button">' . $nextButtonLabel . '</a>';

        return $buttons;
    }
}
