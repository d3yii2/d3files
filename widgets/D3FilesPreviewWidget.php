<?php

namespace d3yii2\d3files\widgets;

use d3yii2\d3files\models\D3files as ModelD3Files;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\widget\ThModal;
use eaBlankonThema\yii2\web\BlankonView;
use Exception;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * Class D3FilesPreviewWidget
 * @package d3yii2\d3files\widgets
 * Usage by model \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['model' => $model])
 * Usage by fileList (attachments are joined already) \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget(['fileList' => $fileList])
 */
class D3FilesPreviewWidget extends Widget
{
    public $listenEvents = ['.d3files-attachment-load' => PDFObject::LISTEN_EVENT_CLICK];
    public $targetElementClass = ThModal::MODAL_CONTENT_CLASS;
    public $viewExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
    public $urlPrefix = '';
    public $model;
    public $fileList = [];
    public $defaultExtension = 'pdf';

    /**
     * @throws Exception
     */
    public function init()
    {
        // Join the attachments list if model specified
        if ($this->model) {
            $this->fileList = ModelD3Files::fileListForWidget($this->model::className(), $this->model->id);
        }

        if ('pdf' === $this->defaultExtension) {
            Yii::$app->view->on(BlankonView::EVENT_END_BODY, function () {

                echo PDFObject::widget(
                    [
                        'listenEvents' => $this->listenEvents,
                        'targetElementClass' => $this->targetElementClass,
                    ]
                );
            });
        }

        // Uses the same EVENT_END_BODY itself
        ThModal::widget();
    }

    /**
     * @return string|void
     */
    public function run()
    {
        parent::run();

        if (empty($this->fileList)) {
            return;
        }

        // Load first attachment by the extension as set in: $this->defaultExtension (PDF by default)
        foreach ($this->fileList as $row) {
            $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
            if ($this->defaultExtension === $ext && in_array($ext, $this->viewExtensions, true)) {
                $fileUrl = $fileUrl = [
                    $this->urlPrefix . 'd3filesopen',
                    'id' => $row['file_model_id']
                ];
                $dataAttributes = D3FilesWidget::getModalLoadAttributes(
                    $fileUrl,
                    $row,
                    '#' . ThModal::MODAL_ID,
                    '.' . ThModal::MODAL_CONTENT_CLASS
                );

                $dataAttributesStr = '';

                foreach ($dataAttributes as $key => $val) {
                    $dataAttributesStr .= ' ' . $key . '="' . $val . '"';
                } ?>
                <a href="javascript:" title="<?= Yii::t('d3files', 'Preview atachment') ?>"<?= $dataAttributesStr ?>
                   class="d3files-attachment-load"
                   data-files-list='<?= Json::encode($this->fileList) ?>'><i class="fa fa-file-text-o"></i></a>
                <?php
                break;
            }
        }
    }
}
