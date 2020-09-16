<?php

namespace d3yii2\d3files\models;

use d3system\exceptions\D3ActiveRecordException;
use d3system\exceptions\D3Exception;
use d3yii2\d3files\components\FileHandler;
use ReflectionException;
use RuntimeException;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "d3files".
 *
 * @property string $id
 * @property integer $type_id
 * @property string $file_name
 * @property string $add_datetime
 * @property integer $user_id
 * @property string $notes
 *
 * @property D3filesModel[] $d3filesModels
 */
class D3files extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'd3files';
    }

    /**
     * alternative for uploading file
     *
     * @param string $fileName
     * @param string $modelName
     * @param int $modelId
     * @param string $filePath
     * @param string $fileTypes
     * @param int $userId
     * @throws \Exception
     */
    public static function saveFile($fileName, $modelName, $modelId, $filePath, $fileTypes, $userId = 0): void
    {
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id' => uniqid('d3files', false),
                'file_name' => $fileName,
                'file_types' => $fileTypes,
                'file_path' => $filePath,
            ]
        );

        $model = new self();

        $model->file_name = $fileName;
        $model->add_datetime = new Expression('NOW()');
        $model->user_id = $userId;

        if (!$model->save()) {
            $fileHandler->remove();
            throw new D3Exception(Yii::t('d3files', 'Insert DB record failed'));
        }
    
        self::saveModelName($modelName, $modelId, $model->id);
        $fileHandler->rename($model->id);
    }

    /**
     * alternative for uploading file
     *
     * @param string $fileName
     * @param string $modelName
     * @param int $modelId
     * @param string $fileContent
     * @param string $fileTypes
     * @param int $userId
     * @throws ForbiddenHttpException
     * @throws Exception
     * @throws ReflectionException
     */
    public static function saveContent($fileName, $modelName, $modelId, $fileContent, $fileTypes, $userId = 0): void
    {
        $fileHandler = new FileHandler(
            [
                'file_name' => $fileName,
                'model_name' => $modelName,
                'model_id' => $modelId,
                'file_types' => $fileTypes,
            ]
        );

        $model = new self();

        $model->file_name = $fileName;
        $model->add_datetime = new Expression('NOW()');
        $model->user_id = $userId;

        if ($model->save()) {
            self::saveModelName($modelName, $modelId, $model->id);
            $fileHandler->setModelId($model->id);
            
            if (!$fileHandler->save($fileContent)) {
                throw new D3Exception('D3Files: Cannot save the file: ' . $fileName);
            }
        } else {
            $fileHandler->remove();
            throw new RuntimeException(500, Yii::t('d3files', 'Insert DB record failed'));
        }
    }

    /**
     * Upload yii\web\UploadedFile
     * @param UploadedFile $uploadFile
     * @param string $modelName model name with name space
     * @param int $modelId
     * @throws \Exception
     */
    public static function saveYii2UploadFile(UploadedFile $uploadFile, $modelName, $modelId): void
    {
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id' => uniqid('d3f', false),
                'file_name' => $uploadFile->name,
                'file_types' => '*', //yii2 model control file types
            ]
        );

        $fileHandler->uploadYii2UloadFile($uploadFile);

        $model = new self();

        $model->file_name = $uploadFile->name;
        $model->add_datetime = new Expression('NOW()');
        $model->user_id = Yii::$app->person->user_id;

        if ($model->save()) {
            self::saveModelName($modelName, $modelId, $model->id);
            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new D3ActiveRecordException($model, Yii::t('d3files', 'Insert DB record failed'));
        }
    }

    /**
     * @param string $modelName
     * @param int $modelId
     * @param int $filesModelId
     */
    private static function saveModelName(string $modelName, int $modelId, int $filesModelId): void
    {
        // Get or create model name id
        $nameModel = new D3filesModelName();
        $model_name_id = $nameModel->getByName($modelName, true);

        $filesModel = new D3filesModel();
        $filesModel->d3files_id = $filesModelId;
        $filesModel->is_file = 1;
        $filesModel->model_name_id = $model_name_id;
        $filesModel->model_id = $modelId;
        if (!$filesModel->save()) {
            throw new D3ActiveRecordException($filesModel, null, 'Cannot save D3filesModel');
        }
    }

    /**
     * get file list with file_path
     *
     * @param $modelName
     * @param $modelId
     * @return array
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws ReflectionException
     */
    public static function getRecordFilesList($modelName, $modelId): array
    {
        $filesList = self::fileListForWidget($modelName, $modelId);
        foreach ($filesList as $k => $fileRow) {
            $fileHandler = new FileHandler(
                [
                    'model_name' => $modelName,
                    'model_id' => $fileRow['id'],
                    'file_name' => $fileRow['file_name'],
                ]
            );
            $filesList[$k]['file_path'] = $fileHandler->getFilePath();
        }

        return $filesList;
    }

    /**
     * get file list for widget
     *
     * @param $modelName
     * @param $modelId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function fileListForWidget($modelName, $modelId): array
    {
        $sSql = /** @lang text */
            '
            SELECT 
              f.id,
              f.file_name,
              fm.id  file_model_id
            FROM
              d3files f
              INNER JOIN d3files_model fm
                ON f.id = fm.d3files_id
              INNER JOIN d3files_model_name fmn
                ON fm.model_name_id = fmn.id
            WHERE fmn.name    = :model_name
              AND fm.model_id = :model_id
              AND fm.deleted  = 0
        ';

        $parameters = [
            ':model_name' => $modelName,
            ':model_id' => $modelId,
        ];

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sSql, $parameters);
        $raw = $command->getRawSql();
        return $command->queryAll();
    }

    /**
     * get file list for widget by model name id and model id
     *
     * @param $modelName
     * @param $modelId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function fileListForWidgetByNameId(int $modelNameId, int $modelId): array
    {
        $sSql = /** @lang text */
            '
            SELECT 
              f.id,
              f.file_name,
              fm.id  file_model_id
            FROM
              d3files f
              INNER JOIN d3files_model fm
                ON f.id = fm.d3files_id
              INNER JOIN d3files_model_name fmn
                ON fm.model_name_id = fmn.id
            WHERE fmn.name_id    = :model_name_id
              AND fm.model_id = :model_id
              AND fm.deleted  = 0
        ';

        $parameters = [
            ':model_name_id' => $modelNameId,
            ':model_id' => $modelId,
        ];

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sSql, $parameters);
        $raw = $command->getRawSql();
        return $command->queryAll();
    }

    /**
     * @param string $model_name
     * @param int $model_id
     * @throws ForbiddenHttpException
     */
    public static function performReadValidation(string $model_name, int $model_id): void
    {
        /** @var ActiveRecord $model_name */
        $modelMain = $model_name::findOne($model_id);
        if (!$modelMain) {
            throw new ForbiddenHttpException(Yii::t('d3files', "You don't have access to parent record"));
        }
    }

    /**
     * @param string $modelClass
     * @param array $modelIds
     * @return array
     * @throws \yii\db\Exception
     */

    public static function forListBox(string $modelClass, array $modelIds): array
    {
        $records = self::getAllByModelRecordIds($modelClass, $modelIds);

        $items = [];

        foreach ($records as $record) {
            $fileExt = (string) pathinfo($record['file_name'], PATHINFO_EXTENSION);

            if (isset($items[$fileExt])) {
                continue;
            }
            $items[$fileExt] = $fileExt;
        }

        return $items;
    }

    /**
     * @param string $modelClass
     * @param array $ids
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getAllByModelRecordIds(string $modelClass, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // Sanitization
        $array_map = [];
        foreach ($ids as $key => $id) {
            $array_map[$key] = (int)$id;
        }
        $ids = $array_map;

        $sSql = /** @lang text */
            '
            SELECT 
              f.id,
              f.file_name,
              fm.id  file_model_id,
              fm.model_id
            FROM
              d3files f
              INNER JOIN d3files_model fm
                ON f.id = fm.d3files_id
              INNER JOIN d3files_model_name fmn
                ON fm.model_name_id = fmn.id
            WHERE fm.model_id IN (' . implode(',', $ids) . ')
              AND fmn.name    = :model_name
              AND fm.deleted  = 0
            ORDER BY file_model_id
        ';

        $parameters = [
            ':model_name' => $modelClass,
        ];

        $connection = Yii::$app->getDb();
        $command = $connection->createCommand($sSql, $parameters);
        return $command->queryAll();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['type_id', 'user_id'], 'integer'],
            [['file_name', 'add_datetime', 'user_id'], 'required'],
            [['add_datetime'], 'safe'],
            [['notes'], 'string'],
            [['file_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('d3files', 'ID'),
            'type_id' => Yii::t('d3files', 'Type ID'),
            'file_name' => Yii::t('d3files', 'File Name'),
            'add_datetime' => Yii::t('d3files', 'Add Datetime'),
            'user_id' => Yii::t('d3files', 'User ID'),
            'notes' => Yii::t('d3files', 'Notes'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getD3filesModels(): ActiveQuery
    {
        return $this->hasMany(D3filesModel::class, ['d3files_id' => 'id']);
    }
}
