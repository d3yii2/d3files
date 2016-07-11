<?php

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveRecord;
use d3yii2\d3files\components\FileHandler;
/**
 * This is the model class for table "d3files".
 *
 * @property string $id
 * @property integer $type_id
 * @property string $file_name
 * @property string $add_datetime
 * @property integer $user_id
 * @property integer $deleted
 * @property string $notes
 * @property string $model_name
 * @property string $model_id
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
            [['type_id', 'user_id', 'deleted', 'model_id'], 'integer'],
            [['file_name', 'add_datetime', 'user_id', 'model_name', 'model_id'], 'required'],
            [['notes'], 'string'],
            [['add_datetime'], 'safe'],
            [['file_name'], 'string', 'max' => 255],
            [['model_name'], 'string', 'max' => 50],
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
            'deleted'      => Yii::t('d3files', 'Deleted'),
            'notes'        => Yii::t('d3files', 'Notes'),
            'model_name'   => Yii::t('d3files', 'Model Name'),
            'model_id'     => Yii::t('d3files', 'Model ID'),
        ];
    }
    
    /**
     * alternative for uploading file
     * 
     * @param string $fileName
     * @param string $modelName
     * @param int $modelId
     * @param string $fileContent
     * @param int $userId
     * @throws HttpException
     */
    public static function saveFile($fileName,$modelName,$modelId, $fileContent, $fileTypes, $userId = 0 )
    {
        $fileHandler = new FileHandler(
            [
                'model_name' => $modelName,
                'model_id'   => uniqid(),
                'file_name'  => $fileName,
                'file_types'  => $fileTypes,
            ]
        );

        $fileHandler->save($fileContent);
        
        $model = new D3files();

        $model->file_name    = $fileName;
        $model->add_datetime = new \yii\db\Expression('NOW()');
        $model->user_id      = $userId;
        $model->model_name   = $modelName;
        $model->model_id     = $modelId;
        
        if ($model->save()) {
            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new Exception(500, Yii::t('d3files', 'Insert DB record failed'));
        }        
    }
}
