<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\d3files\models\D3filesModelShared;
use yii\web\NotFoundHttpException;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Finds an existing D3files model record and downloads corresponding file
 */
class DownloadShareAction extends Action
{
    public function run($id, $hash)
    {

        // Pause every request
        sleep(1);

        /**
         * Validate both parameters:
         * id - only digits > 0
         * hash - only hex, exactly 32 chars long
         */
        if (!preg_match('#^[1-9][0-9]*$#', $id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!preg_match('#^[0-9A-F]{32}$#', strtoupper($hash))) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$fileModelShared = D3filesModelShared::find()
            ->where(['and', "id=$id", "left_loadings>0", "expire_date>=CURDATE()"])->one()
        ) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$fileModel = D3filesModel::findOne(['id' => $fileModelShared->d3files_model_id, 'deleted' => 0, 'is_file' => 1])) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        if (!$fileModelName = D3filesModelName::findOne($fileModel->model_name_id)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        $hashSalt = Yii::$app->getModule('d3files')->hashSalt;

        if (empty($hashSalt)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        /**
         * Compare hash, use Yii::$app->getModule('d3files')->hashSalt
         */
        $hashText    = sprintf('%s:%s:%s', $id, $file->file_name, $hashSalt);
        $correctHash = md5($hashText);

        if ($hash != $correctHash) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        $fileModelShared->left_loadings--;
        $fileModelShared->save();

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
