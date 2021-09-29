<?php


namespace d3yii2\d3files\commands;

use yii\console\Controller;
use d3yii2\d3files\models\D3files;
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
    public function actionRemove($modelName, $date)
    {

        $oldFiles = D3Files::find()
            ->innerJoin('d3files_model', '`d3files_model`.`d3files_id` = `d3files`.`id`')
            ->innerJoin(['d3files_model_name', '`d3file_model_name`.id = `d3files_model`.`model_name_id'])
            ->where(['`d3files_model_name`.`name`' => $modelName ])
            ->andWhere(['<', '`add_datetime`', $date ])
            ->all()
        ;

        $this->out('Deleting ' . count($oldFiles) . ' files.');

        foreach ($oldFiles as $file) {

            $fileHandler = new FileHandler(
                [
                    'model_name' => $modelName,
                    'model_id' => $file->id,
                    'file_name'  => $file->file_name,
                ]
            );
            $filePath = $fileHandler->getFilePath();
            $fileModel = D3filesModel::findOne(['d3files_id' => $file->id]);

            $fileModel->delete();
            $file->delete();

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return 0;
    }
}