<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use d3yii2\d3files\controllers\D3filesController;
use yii\web\Response;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;

/**
 * Class DeleteAction
 * @package d3yii2\d3files\components
 *
 * Deletes an existing D3files model record (sets deleted=1).
 */
class DeleteAction extends Action
{
    public function run($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileModel     = D3filesModel::findOne($id);
        $fileModelName = D3filesModelName::findOne($fileModel->model_name_id);

        // Check access rights to the record the file is attached to
        D3filesController::performReadValidation($fileModelName->name, $fileModel->model_id);

        $fileModel->deleted = 1;
        $fileModel->save();

        //return $this->controller->renderPartial('delete');
        return $this->controller->renderPartial('@vendor/d3yii2/d3files/views/d3files/delete');

    }
}
