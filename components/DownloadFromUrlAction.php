<?php

declare(strict_types=1);

namespace d3yii2\d3files\components;

use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use eaBlankonThema\components\FlashHelper;
use Exception;
use Yii;
use yii\db\Expression;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

use function basename;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function is_array;
use function uniqid;

class DownloadFromUrlAction extends D3FilesAction
{
    public const THE_REQUESTED_FILE_DOES_NOT_EXIST = 'The requested file does not exist.';
    public const THE_REQUEST_INVALID = 'The request is invalid.';

    /**
     * @param int $id
     * @return Response
     */
    public function run(int $id): Response
    {
        try {
            $request       = Yii::$app->request;
            $postModelName = Yii::$app->request->post('model_name');

            /** @var \d3yii2\d3files\D3Files $d3filesModule */
            $d3filesModule = Yii::$app->getModule('d3files');

            //Broken ?
            if (!$d3filesModule->disableController) {
                if (is_array($this->modelName) && !in_array($postModelName, $this->modelName, true)) {
                    throw new HttpException(422, 'Can not upload file for requested model');
                }

                if (!is_array($this->modelName) && $postModelName !== $this->modelName) {
                    throw new HttpException(422, 'Can not upload file for requested model');
                }
            }

            if (!$this->getRequestFile($request)) {
                throw new NotFoundHttpException(Yii::t('d3files', 'Download File url is not set.'));
            }

            $getUrl      = $this->getRequestFile($request);
            $getFileName = $this->getFileName($getUrl);

            // Check access rights to the record the file is attached to
            D3files::performReadValidation($this->modelName, $id);

            $tmp_id = uniqid('d3f', false);

            $fileHandler = new FileHandler(
                [
                    'model_name' => $this->modelName,
                    'model_id'   => $tmp_id,
                    'file_name'  => $getFileName,
                ]
            );

            if ($request->isPost) {
                $this->store($getUrl, $fileHandler->getFilePath());
            } else {
                throw new MethodNotAllowedHttpException(Yii::t('d3files', self::THE_REQUEST_INVALID));
            }

            $model = new D3files();

            $model->file_name    = $getFileName;
            $model->add_datetime = new Expression('NOW()');
            $model->user_id      = Yii::$app->user->getId();

            if ($model->save()) {
                // Get or create model name id
                $modelMN       = new D3filesModelName();
                $model_name_id = $modelMN->getByName($this->modelName, true);

                $modelM                = new D3filesModel();
                $modelM->d3files_id    = $model->id;
                $modelM->is_file       = 1;
                $modelM->model_name_id = $model_name_id;
                $modelM->model_id      = $id;
                $modelM->save();

                $fileHandler->rename($model->id);
            } else {
                $fileHandler->remove();
                FlashHelper::addDanger(Yii::t('d3files', 'Insert DB record failed'));
            }

            return $this->controller->goBack();
        } catch (HttpException | NotFoundHttpException $e) {
            FlashHelper::addDanger($e->getMessage());
            return $this->controller->goBack();
        } catch (Exception $e) {
            FlashHelper::addDanger($e->getMessage());
            return $this->controller->goBack();
        }
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function getRequestFile(Request $request): ?string
    {
        return $request->post('url');
    }

    /**
     * @param string $getUrl
     * @return string
     */
    protected function getFileName(string $getUrl): string
    {
        return basename($getUrl);
    }

    /**
     * @param $getUrl
     * @param $getFileName
     */
    final public function store($getUrl, $getFileName): void
    {
        if (file_put_contents($getFileName, file_get_contents($getUrl))) {
            FlashHelper::addSuccess(Yii::t('d3files', "File downloaded successfully"));
        } else {
            FlashHelper::addWarning(Yii::t('d3files', "File downloading failed."));
        }
    }
}
