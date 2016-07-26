<?php

namespace d3yii2\d3files\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

/**
 * D3filesController implements the CRUD actions for D3files model.
 */
class D3filesController extends Controller
{

    public function actions()
    {

        // Disable controller actions
        if (Yii::$app->getModule('d3files')->disableController) {
            return [];
        }

        return [
            'd3filesdownload' => 'd3yii2\d3files\components\DownloadAction',
            'd3filesupload'   => 'd3yii2\d3files\components\UploadAction',
            'd3filesdelete'   => 'd3yii2\d3files\components\DeleteAction',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'd3filedelete' => ['POST'],
                    'd3fileupload' => ['POST'],
                ],
            ],
        ];
    }

    public function performReadValidation($model_name, $model_id)
    {
        $modelMain = $model_name::findOne($model_id);
        if (!$modelMain) {
            throw new ForbiddenHttpException(Yii::t('d3files', "You don't have access to parent record"));
        }
    }
}
