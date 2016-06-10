<?php
namespace d3yii2\d3files;

/**
 * d3files module definition class
 */
class D3Files extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'd3yii2\d3files\controllers';
    
    public $upload_dir;
    public $file_types;
}
