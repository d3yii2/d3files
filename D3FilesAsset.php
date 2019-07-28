<?php
namespace d3yii2\d3files;

use yii\web\AssetBundle;

class D3FilesAsset extends AssetBundle
{
    public $sourcePath = '@vendor/d3yii2/d3files/assets';
    public $basePath = '@webroot';
    public $css = [
        'css/d3files.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}