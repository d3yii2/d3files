<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\NotFoundHttpException;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Finds an existing D3filesModel record and downloads corresponding file
 */
class DownloadAction extends Action
{
    
    public $modelName;

    public $downloadType = 'download';
    
    public function run($id)
    {

        if (!$fileModel = D3filesModel::findOne([
            'id' => $id, 
            'deleted' => 0
            ])) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }
        
        if (!$fileModelName = D3filesModelName::findOne($fileModel->model_name_id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        /**
         * validate modelname
         */
        if (Yii::$app->getModule('d3files')->disableController) {
            if ($fileModelName->name !== $this->modelName) {
                throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
            }            
        }
        
        // Check access rights to the record the file is attached to
        D3files::performReadValidation($fileModelName->name, $fileModel->model_id);

        $modelName = $fileModelName->name;
        
        if(!$fileModel->is_file){
            if (!$realFileModel = D3filesModel::findOne([
                'd3files_id' => $fileModel->d3files_id, 
                //'deleted' => 0, 
                'is_file' => 1
                ])) {
                throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
            }
            if (!$realfileModelName = D3filesModelName::findOne($realFileModel->model_name_id)) {
                throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
            }   
            
            $modelName = $realfileModelName->name;
            

            //$modelName
        }
        
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id'   => $file->id,
                'file_name'  => $file->file_name,
            ]
        );
        if($this->downloadType === 'download') {
            $fileHandler->download();
            return;
        }

        if($this->downloadType === 'open') {
            $fileHandler->open();
            return;
        }
    }
}
