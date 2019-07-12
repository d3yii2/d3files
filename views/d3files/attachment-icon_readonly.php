<?php

use d3yii2\d3files\widgets\D3FilesWidget;
use eaBlankonThema\widget\ThModal;
use yii\helpers\Html;

\d3yii2\d3files\D3FilesAsset::register($this);
/**
 * @var string $url_prefix
 * @var string $viewByFancyBox
 * @var array $fileList
 */

if (empty($fileList)) {
    return;
}

$pdfFile = false;

foreach ($fileList as $i => $file) {
    if (strstr($file['file_name'], '.pdf')) {
        $file['file_url'] = [$url_prefix . 'd3filesopen', 'id' => $file['file_model_id']];
        $pdfFile = $file;
        break;
    }
}

if (!$pdfFile) {
    return;
}

$dataAttributes = D3FilesWidget::getModalLoadAttributes(
    $pdfFile['file_url'],
    $pdfFile,
    '#' . ThModal::MODAL_ID,
    '.' . ThModal::MODAL_CONTENT_CLASS
);

$dataAttributesStr = '';

foreach ($dataAttributes as $key => $val) {
    $dataAttributesStr .= ' ' . $key . '="' . $val . '"';
}
?>

<a href="javascript:;" title="Atvērt pielikumus"<?= $dataAttributesStr ?> class="d3files-attachment-load" data-files-list='<?= \yii\helpers\Json::encode($fileList) ?>'><i class="fa fa-file-text-o"></i></a>
