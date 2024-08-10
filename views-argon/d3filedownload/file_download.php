<?php
use yii\helpers\Html;

$downloadHtmlOptions = [
    'data-title' => Yii::t('d3files', 'Download'),
    'data-placement' => "top",
    'data-toggle' => "tooltip",
    'class' => 'text-primary',
        ];

echo Html::a(
    '<i class="fa fa-cloud-download  text-primary"></i> ' .  $fileName, 
    [
        $downloadUrl, 
        'id' => $fileModelId
    ], 
    $downloadHtmlOptions
);