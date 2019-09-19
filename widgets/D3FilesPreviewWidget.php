<?php

namespace d3yii2\d3files\widgets;

use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\widget\ThModal;
use Exception;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class D3FilesPreviewWidget
 * @package d3yii2\d3files\widgets
 * Usage by model \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['model' => $model])
 * Usage by fileList (attachments are joined already) \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['fileList' => $fileList])
 */
class D3FilesPreviewWidget extends D3FilesWidget
{
    public $icon = 'external-link';
    public $viewExtension = 'pdf';
    public $currentFile;
    public $prevFile;
    public $nextFile;
    public $view = parent::VIEW_MODAL_BUTTON;

    public static $contentTargetSelector = parent::EMBED_CONTENT_CLASS;

    const PREVIEW_BUTTON_CLASS = 'd3files-preview-button';

    /**
     * @throws Exception
     */
    public function init(): void
    {
        $this->readOnly = true;

        parent::init();
    }

    /**
     * @return string
     */
    public function run(): string
    {
        $firstViewFile = self::getFirstFileHavingExt($this->fileList, $this->viewExtension);

        if ($firstViewFile) {

            /** @var Widget $this */
            return $this->render(
                $this->view,
                [
                    'icon' => $this->icon,
                    'fileList' => $this->fileList,
                    'urlPrefix' => $this->urlPrefix,
                    'file' => $firstViewFile,
                    'modelId' => $this->model_id,
                ]
            );
        }

        return '';
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
}