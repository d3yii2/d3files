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
        return [
            'download' => 'd3yii2\d3files\components\DownloadAction',
            'upload'   => 'd3yii2\d3files\components\UploadAction',
            'delete'   => 'd3yii2\d3files\components\DeleteAction',
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
                    'delete' => ['POST'],
                    'upload' => ['POST'],
                ],
            ],
        ];
    }
}
