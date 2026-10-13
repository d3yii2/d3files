<?php
declare(strict_types=1);

namespace d3yii2\d3files;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use d3yii2\pdfobject\PDFObjectAsset;

/**
 *
 */
class D3FilesPreviewAsset extends AssetBundle
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
        ];

        $this->js = [
        'js/d3files-preview.js',
        ];

        $this->depends = [
            JqueryAsset::class,
            D3FilesAsset::class,
            PDFObjectAsset::class,
        ];
    }
}
