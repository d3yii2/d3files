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

$attrs = D3FilesPreviewWidget::getPreviewButtonAttributes($modelId, $file, $fileList, $urlPrefix);

echo Html::a('<i class="fa fa-' . $icon . '"></i>', 'javascript:void(0)', $attrs);
