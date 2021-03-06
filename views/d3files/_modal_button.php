<?php

use yii\helpers\Html;
use d3yii2\d3files\widgets\D3FilesPreviewWidget;

/**
 * @var string $icon
 * @var array $file
 * @var array $previewFileList
 */

if (empty($file) || empty($previewFileList)) {
    echo '';
} else {
    $previewButtonAttrs = D3FilesPreviewWidget::getPreviewModalButtonAttributes($file, $previewFileList);

    echo Html::a('<span class="' . $icon . '"></span>', 'javascript:void(0)', $previewButtonAttrs);
}
