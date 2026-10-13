<?php
declare(strict_types=1);

namespace d3yii2\d3files\components;

use d3yii2\d3files\exceptions\D3FilesUserException;
use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use ReflectionException;
use function in_array;
use function is_array;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Finds an existing D3filesModel record and downloads corresponding file
 */
class DownloadAction extends Action
{
    
    public mixed $modelName;

    public string $downloadType = 'download';

    public const DOWNLOAD_TYPE_OPEN = 'open';
    public const DOWNLOAD_TYPE_DOWNLOAD = 'download';

    public const THE_REQUESTED_FILE_DOES_NOT_EXIST = 'The requested file does not exist.';

    /**
     * @throws D3FilesUserException
     * @throws HttpException
     * @throws ForbiddenHttpException
     * @throws ReflectionException
     * @throws NotFoundHttpException
     */
    public function run(int $id, string $model_name_id = ''): void
    {

        if ($model_name_id) {
            $nameModel = D3filesModelName::findOne($model_name_id);
            if (!$nameModel) {
                throw new HttpException(404, 'D3filesModelName not found by id: ' . $model_name_id);
            }
            $model_name = $nameModel->name;
            $this->modelName = $model_name;
        } else {
            if (empty($this->modelName)) {
                throw new HttpException(404, 'Either one of model name id or model id should be set');
            }
            $model_name = $this->modelName;
        }

        $disableController = Yii::$app->getModule('d3files')->disableController ?? null;
        if (!$disableController) {
            if (is_array($this->modelName) && !in_array($model_name, $this->modelName, true)) {
                throw new HttpException(422, 'Can not upload file for requested model');
            }

            if ($model_name !== $this->modelName) {
                throw new HttpException(422, 'Can not upload file for requested model');
            }
        }

        $this->modelName = $model_name;

        if (!$fileModel = D3filesModel::findOne([
            'id' => $id,
            'deleted' => 0
            ])) {
            Yii::error('Can not find D3filesModel. id='.$id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            Yii::error('Can not find D3files. id='.$fileModel->d3files_id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }
        
        if (!$fileModelName = D3filesModelName::findOne($fileModel->model_name_id)) {
            Yii::error('Can not find D3filesModelName. id=' . $fileModel->model_name_id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }

        // Check access rights to the record the file is attached to
        D3files::performReadValidation($fileModelName->name, (int)$fileModel->model_id);

        $modelName = $fileModelName->name;
        
        if (!$fileModel->is_file) {
            if (!$realFileModel = D3filesModel::findOne([
                'd3files_id' => $fileModel->d3files_id,
                //'deleted' => 0,
                'is_file' => 1
                ])) {
                Yii::error('No found $realFileModel d3files_id=' . $fileModel->d3files_id);
                throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
            }
            if (!$realfileModelName = D3filesModelName::findOne($realFileModel->model_name_id)) {
                Yii::error('No found $realfileModelName id=' . $realFileModel->model_name_id);
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
        if ($this->downloadType === self::DOWNLOAD_TYPE_DOWNLOAD) {
            $fileHandler->download();
        }

        if ($this->downloadType === self::DOWNLOAD_TYPE_OPEN) {
            $fileHandler->open();
        }
    }
}
