<?php

namespace d3yii2\d3files\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * D3filesController implements the CRUD actions for D3files model.
 */
class D3filesController extends Controller
{

    public function actions()
    {

        // Disable controller actions
        if (Yii::$app->getModule('d3files')->disableController) {
            return [
                'downloadshare' => 'd3yii2\d3files\components\DownloadShareAction',
            ];
        }

        return [
            'd3filesdownload' => 'd3yii2\d3files\components\DownloadAction',
            'd3filesupload'   => 'd3yii2\d3files\components\UploadAction',
            'd3filesdelete'   => 'd3yii2\d3files\components\DeleteAction',
            'downloadshare'   => 'd3yii2\d3files\components\DownloadShareAction',
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
}
