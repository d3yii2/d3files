<?php

use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use d3yii2\d3files\widgets\D3FilesWidget;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\widget\ThModal;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * @var string $icon
 * @var array $fileList
 * @var string $urlPrefix
 * @var array $file
 * @var int $modelId
 */

$file = D3FilesWidget::getFirstFileHavingExt($fileList, 'pdf');

$attrs = D3FilesPreviewWidget::getPreviewModalButtonAttributes($modelId, $file, $fileList, $urlPrefix);

echo Html::a('<span class="' . $icon . '"></span>', 'javascript:void(0)', $attrs);
