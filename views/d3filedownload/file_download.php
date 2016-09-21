<?php
use yii\helpers\Html;


echo Html::a(
    Yii::t('d3files', 'Download') . ' ' . $fileName, 
    [
        $downloadUrl, 
        'id' => $fileModelId
    ], 
    [
        'title' => Yii::t('d3files', 'Download')
    ]
);