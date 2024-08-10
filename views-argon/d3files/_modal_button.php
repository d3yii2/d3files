<?php


use d3yii2\d3icon\Icon;
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

    echo Html::a(Icon::fa('eye'), 'javascript:void(0)', $previewButtonAttrs);
}
