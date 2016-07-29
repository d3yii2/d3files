<?php

namespace d3yii2\d3files\models;

use Yii;

/**
 * This is the model class for table "d3files_model_shared".
 *
 * @property string $id
 * @property string $d3files_model_id
 * @property string $hash
 * @property string $expire_date
 * @property integer $left_loadings
 *
 * @property D3filesModel $d3filesModel
 */
class D3filesModelShared extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'd3files_model_shared';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['d3files_model_id', 'hash', 'expire_date'], 'required'],
            [['d3files_model_id', 'left_loadings'], 'integer'],
            [['expire_date'], 'safe'],
            [['hash'], 'string', 'max' => 32],
            [['d3files_model_id'], 'exist', 'skipOnError' => true, 'targetClass' => D3filesModel::className(), 'targetAttribute' => ['d3files_model_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('d3files', 'ID'),
            'd3files_model_id' => Yii::t('d3files', 'D3files Model ID'),
            'hash' => Yii::t('d3files', 'Hash'),
            'expire_date' => Yii::t('d3files', 'Expire Date'),
            'left_loadings' => Yii::t('d3files', 'Left Loadings'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getD3filesModel()
    {
        return $this->hasOne(D3filesModel::className(), ['id' => 'd3files_model_id']);
    }
}
