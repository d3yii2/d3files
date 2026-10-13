<?php
declare(strict_types=1);

namespace d3yii2\d3files\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use d3yii2\d3files\components\DownloadAction;
use d3yii2\d3files\components\UploadAction;
use d3yii2\d3files\components\DeleteAction;
use d3yii2\d3files\components\DownloadShareAction;

/**
 * D3filesController implements the CRUD actions for D3files model.
 */
class D3filesController extends Controller
{

    /**
     * Declares external actions for the controller.
     *
     * This method is meant to be overwritten to declare external actions for the controller.
     * It should return an array, with array keys being action IDs, and array values the corresponding
     * action class names or action configuration arrays. For example,
     *
     * ```
     * return [
     *     'action1' => 'app\components\Action1',
     *     'action2' => [
     *         'class' => 'app\components\Action2',
     *         'property1' => 'value1',
     *         'property2' => 'value2',
     *     ],
     * ];
     * ```
     *
     * [[\Yii::createObject()]] will be used later to create the requested action
     * using the configuration provided here.
     * @return array<array-key, class-string|array{class: class-string, ...}>
     */
    public function actions(): array
    {

        // Disable controller actions
        $disableController = Yii::$app->getModule('d3files')->disableController ?? false;
        if ($disableController) {
            return [
                'downloadshare' => DownloadShareAction::class,
            ];
        }

        return [
            'd3filesdownload' => DownloadAction::class,
            'd3filesupload'   => UploadAction::class,
            'd3filesdelete'   => DeleteAction::class,
            'downloadshare'   => DownloadShareAction::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'd3filedelete' => ['POST'],
                    'd3fileupload' => ['POST'],
                ],
            ],
        ];
    }
}
