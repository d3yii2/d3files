<?php

namespace d3yii2\d3files\widgets;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\widget\ThModal;
use eaBlankonThema\yii2\web\BlankonView;
use d3yii2\d3files\D3FilesPreviewAsset;
use eaBlankonThema\widget\ThButton;
use Exception;
use Yii;
use yii\helpers\Html;
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
    public $currentFile = null;
    public $prevFile = null;
    public $nextFile = null;

    public const TEMPLATE_MODAL = '_modal';

    /**
     * @throws Exception
     */
    public function init(): void
    {
        if (empty($this->model_name)) {
            $this->model_name = $this->model::className();
        }

        $this->modalPreview = true;
        $this->readOnly = true;

        parent::init();
    }

    /**
     * @return string
     */
    public function run(): string
    {
        parent::run();

        $firstViewFile = parent::getFirstFileHavingExt($this->fileList, $this->viewExtension);

        if ($firstViewFile) {

            return $this->render(
                self::TEMPLATE_MODAL,
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
}
