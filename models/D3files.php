<?php

namespace app\vendor\dbrisinajumi\d3files\models;

use yii\db\ActiveRecord;

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
            'id' => 'ID',
            'type_id' => 'Type ID',
            'file_name' => 'File Name',
            'add_datetime' => 'Add Datetime',
            'user_id' => 'User ID',
            'deleted' => 'Deleted',
            'notes' => 'Notes',
            'model_name' => 'Model Name',
            'model_id' => 'Model ID',
        ];
    }
}
