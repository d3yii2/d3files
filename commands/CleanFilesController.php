<?php


namespace d3yii2\d3files\commands;

use yii\console\Controller;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\components\FileHandler;

class CleanFilesController extends Controller
{

    /**
     * date must be in format yyyy-mm-dd
     * example:
     * date -d '-3 year' '+%Y-%d-%m'
     *
     * @param $modelName
     * @param $date
     * @throws \ReflectionException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     *
     * @return int
     */
    public function actionRemoveOlderThan($modelName, $date)
    {

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

            if (!D3filesModel::findOne(['d3files_id' => $file->id])) {

                $file->delete();

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        return 0;
    }
    
}