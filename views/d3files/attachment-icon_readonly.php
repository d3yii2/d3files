<?php

use d3yii2\pdfobject\widgets\PDFObject;

\d3yii2\d3files\D3FilesAsset::register($this);
/**
 * @var string $url_prefix
 * @var string $viewByFancyBox
 * @var array $viewByFancyBoxExtensions
 * @var array $fileList
 */

echo \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget([
    'fileList' => $fileList,
]);