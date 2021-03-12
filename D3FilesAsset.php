<?php
namespace d3yii2\d3files;

use yii\web\AssetBundle;

class D3FilesAsset extends AssetBundle
{
    public $sourcePath = '@vendor/d3yii2/d3files/assets';
    public $basePath = '@webroot';
    public $css = [
        'css/d3files.css',
        'css/d3photo-view.min.css'
    ];
    public $js = [
        'js/d3files.js',
        'js/d3photo-view.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}