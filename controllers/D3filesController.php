<?php

namespace app\vendor\d3yii2\d3files\controllers;

use Yii;
use app\vendor\d3yii2\d3files\models\D3files;
use app\vendor\d3yii2\d3files\components\FileHandler;
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
        $model = $this->findModel($id);
        $model->deleted = 1;
        $model->save();
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
            throw new NotFoundHttpException('The requested page does not exist.');
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
        // $id te ir id modelim, kuram tiek pievienots pielikums
        
        if (!isset($_FILES['upload_file'])) {
            throw new NotFoundHttpException('File not uploaded.');
        }
        
        if (empty($_POST['model_name'])) {
            throw new HttpException(422, 'mandatory POST parameter model_name is not set');
        }
        
        $model = new D3files();
        
        $model->file_name    = $_FILES['upload_file']['name'];
        $model->add_datetime = date('Y-m-d H:i:s');
        $model->user_id      = Yii::$app->user->getId();
        $model->model_name   = 'People';
        $model->model_id     = $id;
        
        if ($model->save()) {
            $fileHandler = new FileHandler(
                [
                    'model_name' => $_POST['model_name'],
                    'model_id'   => $model->id,
                    'file_name'  => $model->file_name,
                ]
            );
            
            $fileHandler->upload();
            
            return $this->renderPartial('upload', ['model' => $model]);
        }
        
    }
}
