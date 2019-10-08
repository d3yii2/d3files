<?php

use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use yii\helpers\Html;

/**
 * @var string $icon
 * @var array $fileList
 * @var string $urlPrefix
 * @var array $file
 * @var int $modelId
 */

if($attrs = D3FilesPreviewWidget::getPreviewButtonAttributes($modelId, $file, $fileList, $urlPrefix)) {
    echo Html::a('<span class="' . $icon . '"></span>', 'javascript:void(0)', $attrs);
}
