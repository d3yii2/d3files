<?php
namespace d3yii2\d3files\components;

use yii\base\Action;
use Yii;
use yii\web\Response;
use d3yii2\d3files\models\D3filesModel;

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
        $model = D3filesModel::findOne($id);
        $model->deleted = 1;
        $model->save();
        //return $this->controller->renderPartial('delete');
        return $this->controller->renderPartial('@vendor/d3yii2/d3files/views/d3files/delete');
    }
}
