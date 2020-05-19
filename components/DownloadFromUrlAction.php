<?php

declare(strict_types=1);

namespace d3yii2\d3files\components;

use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use ReflectionException;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;

use function basename;
use function file_get_contents;
use function file_put_contents;

class DownloadFromUrlAction extends Action
{
    /**
     * @var string
     */
    public $modelClass;


    public const THE_REQUESTED_FILE_DOES_NOT_EXIST = 'The requested file does not exist.';
    public const THE_REQUEST_INVALID = 'The request is invalid.';

    /**
     * @param int $id
     * @throws HttpException
     * @throws NotFoundHttpException
     * @throws ReflectionException
     * @throws ForbiddenHttpException
     */
    public function run(int $id): void
    {
        $modelClass = $this->modelClass;
        $request    = Yii::$app->request;

//        if (!Yii::$app->getModule('d3files')->disableController) {
//            if (is_array($this->modelName) && !in_array($modelName, $this->modelName, true)) {
//                throw new HttpException(422, 'Can not upload file for requested model');
//            }
//
//            if (!is_array($this->modelName) && $modelName !== $this->modelName) {
//                throw new HttpException(422, 'Can not upload file for requested model');
//            }
//        }

        if ($request->isPost) {
            $getResponseMessage = $this->collectFile($request);
            echo $getResponseMessage;
        } else {
            throw new MethodNotAllowedHttpException(Yii::t('d3files', self::THE_REQUEST_INVALID));
        }
        exit();


        if (!$fileModel = D3filesModel::findOne(
            [
                'id'      => $id,
                'deleted' => 0
            ]
        )) {
            Yii::error('Can not find D3filesModel. id=' . $id);
            throw new NotFoundHttpException(Yii::t('d3files', self::THE_REQUESTED_FILE_DOES_NOT_EXIST));
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            Yii::error('Can not find D3files. id=' . $fileModel->d3files_id);
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
            if (!$realFileModel = D3filesModel::findOne(
                [
                    'd3files_id' => $fileModel->d3files_id,
                    //'deleted' => 0,
                    'is_file'    => 1
                ]
            )) {
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
        if ($this->downloadType === 'download') {
            $fileHandler->download();
            return;
        }

        if ($this->downloadType === 'open') {
            $fileHandler->open();
            return;
        }
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getRequestFile(Request $request): string
    {
        return $request->post('url');
    }

    /**
     * @param $getUrl
     * @return string
     */
    protected function getFileName($getUrl): string
    {
        return basename($getUrl);
    }

    /**
     * @param Request $request
     * @return string
     */
    final public function collectFile(Request $request): string
    {
        $getUrl = $this->getRequestFile($request);

        $fileName = $this->getFileName($getUrl);

        if (file_put_contents($fileName, file_get_contents($getUrl))) {
            $message = "File downloaded successfully";
        } else {
            $message = "File downloading failed.";
        }

        return $message;
    }
}
