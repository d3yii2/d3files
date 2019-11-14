<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\HttpException;
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

    public const THE_REQUESTED_FILE_DOES_NOT_EXIST = 'The requested file does not exist.';

    public function run(int $id, string $model_name): void
    {

        if(!Yii::$app->getModule('d3files')->disableController){
            if (is_array($this->modelName) && !in_array($model_name, $this->modelName, true)) {
                throw new HttpException(422, 'Can not upload file for requested model');
            }

            if (!is_array($this->modelName) && $model_name !== $this->modelName) {
                throw new HttpException(422, 'Can not upload file for requested model');
            }
        }

        $this->modelName = $model_name;

        if (!$fileModel = D3filesModel::findOne([
            'id' => $id, 
            'deleted' => 0
            ])) {
            Yii::error( 'Can not find D3filesModel. id='.$id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            Yii::error( 'Can not find D3files. id='.$fileModel->d3files_id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }
        
        if (!$fileModelName = D3filesModelName::findOne($fileModel->model_name_id)) {
            Yii::error( 'Can not find D3filesModelName. id=' . $fileModel->model_name_id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
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
                Yii::error( 'No found $realFileModel d3files_id=' . $fileModel->d3files_id);
                throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
            }
            if (!$realfileModelName = D3filesModelName::findOne($realFileModel->model_name_id)) {
                Yii::error( 'No found $realfileModelName id=' . $realFileModel->model_name_id);
                throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
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
