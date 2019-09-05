<?php

namespace d3yii2\d3files\widgets;

use Exception;
use yii\base\Widget;

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
     * @param string $dataTargetSelector
     * @param string $contentTargetSelector
     * @return array
     */
    public static function getPreviewButtonDataAttributes(
        string $attachmentUrl,
        string $dataTargetSelector,
        string $contentTargetSelector
    ): array {
        $attrs = [
            'data-src' => $attachmentUrl,
            'data-target' => $dataTargetSelector,
            'data-content-target' => $contentTargetSelector,
        ];

       $attrs['data-toggle'] = 'modal';

        return $attrs;
    }
}
