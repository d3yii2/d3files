<?php
declare(strict_types=1);

namespace d3yii2\d3files;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 *
 */
class D3FilesAsset extends AssetBundle
{
    /**
     * Initializes the bundle.
     * If you override this method, make sure you call the parent implementation in the last.
     */
    public function init(): void
    {
        $this->sourcePath = '@vendor/d3yii2/d3files/assets';

        $this->basePath = '@webroot';

        $this->css = [
        'css/d3files.css',
        'css/d3photo-view.min.css'
        ];

        $this->js = [
        'js/d3files.js',
        'js/d3photo-view.js'
        ];

        $this->depends = [
            JqueryAsset::class,
        ];
    }
}
