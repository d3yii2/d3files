<?php

namespace d3yii2\d3files\widgets;

use Yii;
use yii\base\Widget;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\D3Files;

class D3FilesFileDownloadWidget extends Widget
{
    public $downloadUrl;
    public $fileModelId;
    private $fileName;


    public function init()
    {
        parent::init();
        D3Files::registerTranslations();
        
        $this->fileName = D3filesModel::findOne($this->fileModelId)
                    ->getD3files()
                    ->one()->file_name;
        
    }
    
    public function run()
    {
            return $this->render(
                'file_download',
                [
                    'fileModelId' => $this->fileModelId,
                    'downloadUrl'   => $this->downloadUrl,
                    'fileName'      => $this->fileName,
                ]
            );
        
    }
    
    public function getViewPath()
    {
        if (!$viewPath = Yii::$app->getModule('d3files')->viewPath) {
            $viewPath = dirname(__DIR__) . '/views';
        }
        return $viewPath . '/d3filedownload/';
    }
}
