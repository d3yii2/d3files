<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use yii\web\Response;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\NotFoundHttpException;

/**
 * Class DeleteAction
 * @package d3yii2\d3files\components
 *
 * Deletes an existing D3files model record (sets deleted=1).
 */
class DeleteAction extends Action
{
    
    public $modelName;
    
    public function run($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$fileModel = D3filesModel::findOne(['id' => $id, 'deleted' => 0])) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$fileModelName = D3filesModelName::findOne($fileModel->model_name_id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        /**
         * validate modelname
         */
        if (Yii::$app->getModule('d3files')->disableController) {
            if ($fileModelName->name != $this->modelName) {
                throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
            }            
        }        
        
        // Check access rights to the record the file is attached to
        D3files::performReadValidation($fileModelName->name, $fileModel->model_id);

        $fileModel->deleted = 1;
        $fileModel->save();

        return $this->controller->renderFile(
            Yii::$app->getModule('d3files')->getView('delete')
        );

    }
}
