<?php

use d3yii2\pdfobject\widgets\PDFObject;

\d3yii2\d3files\D3FilesAsset::register($this);
/**
 * @var string $urlPrefix
 * @var string $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 */

echo \d3yii2\d3files\widgets\D3FilesPreviewWidget::widget([
    'fileList' => $fileList,
]);