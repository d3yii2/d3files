<?php

use d3yii2\d3files\D3FilesAsset;
use d3yii2\d3files\widgets\D3FilesPreviewWidget;

D3FilesAsset::register($this);
/**
 * @var string $urlPrefix
 * @var string $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 */

echo D3FilesPreviewWidget::widget([
    'fileList' => $fileList,
]);
