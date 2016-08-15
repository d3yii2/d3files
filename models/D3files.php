<?php

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use d3yii2\d3files\components\FileHandler;
use yii\web\ForbiddenHttpException;

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
    public static function tableName()
    {
        return 'd3files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => Yii::t('d3files', 'ID'),
            'type_id'      => Yii::t('d3files', 'Type ID'),
            'file_name'    => Yii::t('d3files', 'File Name'),
            'add_datetime' => Yii::t('d3files', 'Add Datetime'),
            'user_id'      => Yii::t('d3files', 'User ID'),
            'notes'        => Yii::t('d3files', 'Notes'),
        ];
    }
    
    /**
     * alternative for uploading file
     * 
     * @param string $fileName
     * @param string $modelName
     * @param int $modelId
     * @param string $filePath
     * @param int $userId
     * @throws HttpException
     */
    public static function saveFile($fileName, $modelName, $modelId, $filePath, $fileTypes, $userId = 0 )
    {
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id'   => uniqid(),
                'file_name'  => $fileName,
                'file_types' => $fileTypes,
                'file_path'  => $filePath,
            ]
        );
        
        $model = new D3files();

        $model->file_name    = $fileName;
        $model->add_datetime = new \yii\db\Expression('NOW()');
        $model->user_id      = $userId;
        
        if ($model->save()) {

            // Get or create model name id
            $modelMN = new D3filesModelName();
            $model_name_id = $modelMN->getByName($modelName, true);
            
            $modelM = new D3filesModel();
            $modelM->d3files_id = $model->id;
            $modelM->is_file = 1;
            $modelM->model_name_id = $model_name_id;
            $modelM->model_id = $modelId;
            $modelM->save();            
            
            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new Exception(500, Yii::t('d3files', 'Insert DB record failed'));
        }        
    }

    /**
     * Upload yii\web\UploadedFile
     * @param UploadedFile $uploadFile
     * @param string $modelName model name with name space
     * @param int $modelId
     * @throws Exception
     */
    public static function saveYii2UploadFile(UploadedFile $uploadFile, $modelName, $modelId)
    {
        
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id'   => uniqid(),
                'file_name'  => $uploadFile->name,
                'file_types' => '*', //yii2 model control file types
            ]
        );

        $fileHandler->uploadYii2UloadFile($uploadFile);
        
        $model = new D3files();

        $model->file_name    = $uploadFile->name;
        $model->add_datetime = new \yii\db\Expression('NOW()');
        $model->user_id      = \Yii::$app->person->user_id;
        
        if ($model->save()) {

            // Get or create model name id
            $modelMN = new D3filesModelName();
            $model_name_id = $modelMN->getByName($modelName, true);
            
            $modelM = new D3filesModel();
            $modelM->d3files_id = $model->id;
            $modelM->is_file = 1;
            $modelM->model_name_id = $model_name_id;
            $modelM->model_id = $modelId;
            $modelM->save();            
            
            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new Exception(500, Yii::t('d3files', 'Insert DB record failed'));
        }        
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getD3filesModels()
    {
        return $this->hasMany(D3filesModel::className(), ['d3files_id' => 'id']);
    }     
    
    /**
     * get file list for widget
     * 
     * @param string $modelName model name
     * @param int $modelId model record PK value
     * @return array
     */
    public static function fileListForWidget($modelName, $modelId) {

        $sSql = "
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
        ";

        $parameters = [
            ':model_name' => $modelName,
            ':model_id'   => $modelId,
        ];
        
        $connection = \Yii::$app->getDb();
        $command = $connection->createCommand($sSql, $parameters);
        return $command->queryAll();        
    }

    public static function performReadValidation($model_name, $model_id)
    {
        $modelMain = $model_name::findOne($model_id);
        if (!$modelMain) {
            throw new ForbiddenHttpException(Yii::t('d3files', "You don't have access to parent record"));
        }
    }
}
