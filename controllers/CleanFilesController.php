<?php


namespace d3yii2\d3files\controllers;

use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\components\FileHandler;
use d3system\commands\D3CommandController;
use d3yii2\d3files\models\D3filesModelName;
use DateInterval;
use DateTime;
use yii\console\ExitCode;

class CleanFilesController extends D3CommandController
{

    /**
     * soft deletes all the file models older than date provided
     * older than number of months
     *
     * @param string $modelName
     * @param int $months
     * @param string|null $sqlLikeFileName
     * @return int
     * @throws \Exception
     */
    public function actionRemoveOlderThan(string $modelName, int $months, string $sqlLikeFileName = null): int
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P' . $months . 'M'));
        $this->out('Clrear oldest ' . $date->format('Y-m-d H:i:s'));
        $modelMN = new D3filesModelName();
        if (!$modelNameId = $modelMN->getByName($modelName, false)) {
            $this->out('Ilegal model name: ' . $modelName);
        }
        $i = 1;
        $count = 1;
        while ($count > 0) {
            $activeQuery = D3filesModel::find()
                ->select('d3files_model.id')
                ->innerJoin('d3files', '`d3files`.`id` = `d3files_model`.`d3files_id`')
                ->where([
                    '`d3files_model`.`model_name_id`' => $modelNameId,
                    'd3files_model.deleted' => 0
                ])
                ->andWhere(['<', '`add_datetime`', $date->format('Y-m-d H:i:s')])
                ->orderBy(['`add_datetime`' => SORT_ASC])
                ->limit(100);

            if ($sqlLikeFileName) {
                $activeQuery->andWhere('d3files.file_name like \'' . $sqlLikeFileName . '\'');
            }
            $oldFilesId = $activeQuery
                ->column();

            $count = count($oldFilesId);
            $this->out('FilesId: ' . implode(',', $oldFilesId));
            $this->out('Deleting ' . $count . ' files.');
            D3filesModel::updateAll(
                ['deleted' => 1],
                ['id' => $oldFilesId]
            );
            if ($i++ > 30) {
                break;
            }

            sleep(1);
        }
        return ExitCode::OK;
    }

    /**
     * deletes all files saved under the model name
     * with value "deleted = 1"
     *
     * @param string $modelName
     * @return int
     * @throws \ReflectionException
     * @throws \yii\db\StaleObjectException
     */
    public function actionRemoveFiles(string $modelName): int
    {
        $modelMN = new D3filesModelName();
        if (!$modelNameId = $modelMN->getByName($modelName, false)) {
            $this->out('Illegal model name: ' . $modelName);
        }
        $deletedFiles = D3filesModel::find()
            ->where([
                'deleted' => 1,
                '`d3files_model`.`model_name_id`' => $modelNameId
            ])
            ->limit(1000)
            ->all();

        $this->out('Deleting ' . count($deletedFiles) . ' file models.');

        foreach ($deletedFiles as $fileModel) {

            /** @var D3files $file */
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
                $this->out('Delete file ' . $fileModel->d3files_id . ' - ' .  $file->file_name);
                $file->delete();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } else {
                $this->out('Can\'t delete file ' . $file->file_name . ', in use with model: '. $usedModel->id);
            }
        }
        return ExitCode::OK;
    }

    /**
     * check all model files in upload directory and check it in db. if not used, delete
     * @param string $modelName
     * @return int
     */
    public function actionUnusedFiles(string $modelName): int
    {

        $modelMN = new D3filesModelName();
        if (!$modelNameId = $modelMN->getByName($modelName, false)) {
            $this->out('Illegal model name: ' . $modelName);
        }

        $dirPath = FileHandler::getUploadDirPath($modelName);
        $handle = opendir($dirPath);
        $i = 0;
        if ($handle) {
            while (($entry = readdir($handle)) !== false) {
                if ($entry ==='.') {
                    continue;
                }
                if ($entry ==='..') {
                    continue;
                }
                $i++;
                $this->out($entry);
                $d3FilesId = pathinfo($entry, PATHINFO_FILENAME);
                if (!preg_match('#^\d+$#', $d3FilesId)) {
                    $this->out(' ilegal $d3FilesId: ' . $d3FilesId);
                    continue;
                }
                if (D3files::find()
                    ->innerJoin('d3files_model', 'd3files_model.d3files_id = d3files.id')
                    ->where([
                        'd3files.id' => $d3FilesId,
                        '`d3files_model`.`model_name_id`' => $modelNameId
                    ])
                    ->exists()
                ) {
                    //$this->out(' Izmanto');
                    continue;
                }
                $this->out(' unused');
                if (!unlink($dirPath . '/' . $entry)) {
                    $this->out(' Error: can not unlink');
                    continue;
                }
                $this->out(' unlinked');
            }
        }
        $this->out('Deleted ' . $i . ' files');
        return ExitCode::OK;
    }
}
