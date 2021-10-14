<?php


namespace d3yii2\d3files\controllers;

use yii\console\Controller;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\components\FileHandler;
use d3system\commands\D3CommandController;

class CleanFilesController extends D3CommandController
{

    /**
     * soft deletes all the file models older than date provided
     * older than number of months
     *
     * @param $modelName
     * @param $months
     * @throws \ReflectionException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @return int
     */
    public function actionRemoveOlderThan($modelName, $months)
    {
        $date = date('Y-m-d', strtotime('-'.$months.' month'));

        $oldFiles = D3filesModel::find()
            ->innerJoin('d3files', '`d3files`.`id` = `d3files_model`.`d3files_id`')
            ->innerJoin(['d3files_model_name', '`d3file_model_name`.id = `d3files_model`.`model_name_id'])
            ->where(['`d3files_model_name`.`name`' => $modelName ])
            ->andWhere(['deleted' => 0])
            ->andWhere(['<', '`add_datetime`', $date ])
            ->all()
        ;

        $this->stdout('Deleting ' . count($oldFiles) . ' files.');

        foreach ($oldFiles as $file) {

            $file->deleted = 1;
            $file->save();
        }

        return 0;
    }

    /**
     * deletes all files saved under the model name
     * with value "deleted = 1"
     *
     * @param $modelName
     * @throws \ReflectionException
     *
     * @return int
     */
    public function actionRemoveFiles($modelName)
    {
        $deletedFiles = D3filesModel::find()
            ->where(['deleted' => 1])
            ->all();

        $this->stdout('Deleting ' . count($deletedFiles) . ' file models.');

        foreach ($deletedFiles as $fileModel) {

            $file = $fileModel->getD3files()->one();

            $fileHandler = new FileHandler(
                [
                    'model_name' => $modelName,
                    'model_id' => $fileModel->d3files_id,
                    'file_name' => $file->file_name,
                ]
            );

            $filePath = $fileHandler->getFilePath();
            $fileModel->delete();

            if (!$usedModel = D3filesModel::findOne(['d3files_id' => $file->id])) {

                $file->delete();

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } else {
                $this->stdout('Can\'t delete file ' . $file->file_name . ', in use with model: '. $usedModel->id);
            }

        }

        return 0;
    }
    
}