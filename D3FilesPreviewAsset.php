<?php
namespace d3yii2\d3files;

use yii\web\AssetBundle;

class D3FilesPreviewAsset extends AssetBundle
{
    public $sourcePath = '@vendor/d3yii2/d3files/assets';
    public $basePath = '@webroot';
    public $css = [
    ];
    public $js = [
        'js/d3files-preview.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'd3yii2\d3files\D3FilesAsset',
        'd3yii2\pdfobject\PDFObjectAsset',
    ];
}