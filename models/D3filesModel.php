<?php

namespace d3yii2\d3files\models;

use Yii;

/**
 * This is the model class for table "d3files_model".
 *
 * @property string $id
 * @property string $d3files_id
 * @property integer $is_file
 * @property string $model_name
 * @property string $model_id
 * @property integer $deleted
 *
 * @property D3files $d3files
 */
class D3filesModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'd3files_model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['d3files_id'], 'required'],
            [['d3files_id', 'is_file', 'model_id', 'deleted'], 'integer'],
            [['model_name'], 'string', 'max' => 50],
            [['d3files_id'], 'exist', 'skipOnError' => true, 'targetClass' => D3files::className(), 'targetAttribute' => ['d3files_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('d3files', 'ID'),
            'd3files_id' => Yii::t('d3files', 'D3files ID'),
            'is_file' => Yii::t('d3files', 'Is File'),
            'model_name' => Yii::t('d3files', 'Model Name'),
            'model_id' => Yii::t('d3files', 'Model ID'),
            'deleted' => Yii::t('d3files', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getD3files()
    {
        return $this->hasOne(D3files::className(), ['id' => 'd3files_id']);
    }
}
