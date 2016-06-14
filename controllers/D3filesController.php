<?php

namespace d3yii2\d3files\controllers;

use Yii;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\components\FileHandler;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;
use yii\filters\VerbFilter;

/**
 * D3filesController implements the CRUD actions for D3files model.
 */
class D3filesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'upload' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Deletes an existing D3files model (sets deleted=1).
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = 'json';
        $model = $this->findModel($id);
        $model->deleted = 1;
        $model->save();
        return $this->renderPartial('delete', ['model' => $model]);
    }

    /**
     * Finds the D3files model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return D3files the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = D3files::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }
    }
    
    public function actionDownload($id)
    {
        
        $model = $this->findModel($id);
        
        $fileHandler = new FileHandler(
            [
                'model_name' => $model->model_name,
                'model_id'   => $model->id,
                'file_name'  => $model->file_name,
            ]
        );
        
        $fileHandler->download();
    }
    
    public function actionUpload($id)
    {
        // $id here is id for model to which will be attached attachments
        
        Yii::$app->response->format = 'json';
        
        if (!isset($_FILES['upload_file'])) {
            throw new NotFoundHttpException(Yii::t('d3files', 'File not uploaded.'));
        }
        
        if (empty($_POST['model_name'])) {
            throw new HttpException(422, Yii::t('d3files', 'mandatory POST parameter model_name is not set'));
        }
        
        $tmp_id = uniqid();
        
        $fileHandler = new FileHandler(
            [
                'model_name' => $_POST['model_name'],
                'model_id'   => $tmp_id,
                'file_name'  => $_FILES['upload_file']['name'],
            ]
        );

        $fileHandler->upload();
        
        $model = new D3files();
        
        $model->file_name    = $_FILES['upload_file']['name'];
        $model->add_datetime = date('Y-m-d H:i:s');
        $model->user_id      = Yii::$app->user->getId();
        $model->model_name   = 'People';
        $model->model_id     = $id;
        
        if ($model->save()) {
            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new HttpException(500, Yii::t('d3files', 'Insert DB record failed'));
        }
        
        return $this->renderPartial('upload', ['model' => $model]);
        
    }
}
