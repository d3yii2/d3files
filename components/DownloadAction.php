<?php
namespace d3yii2\d3files\components;

use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\NotFoundHttpException;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Finds an existing D3files model record and downloads corresponding file
 */
class DownloadAction extends Action
{
    public function run($id)
    {
        $file = $this->findModel($id);
        $fileModel = $file->getD3filesModels()->where(['is_file' => 1])->one();

        if (!$fileModel) {
            return false;
        }

        $fileModelName = D3filesModelName::findOne($fileModel->model_name_id);

        $fileHandler = new FileHandler(
            [
                'model_name' => $fileModelName->name,
                'model_id'   => $file->id,
                'file_name'  => $file->file_name,
            ]
        );

        $fileHandler->download();
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
}
