<?php
declare(strict_types=1);

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "d3files_model_shared".
 *
 * @property string $id
 * @property string $d3files_model_id
 * @property string $hash
 * @property string $expire_date
 * @property int $left_loadings
 *
 * @property D3filesModel $d3filesModel
 */
class D3filesModelShared extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'd3files_model_shared';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['d3files_model_id', 'hash', 'expire_date'], 'required'],
            [['d3files_model_id', 'left_loadings'], 'integer'],
            [['expire_date'], 'safe'],
            [['hash'], 'string', 'max' => 32],
            [
                ['d3files_model_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => D3filesModel::class,
                'targetAttribute' => ['d3files_model_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
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
     * @return ActiveQuery
     */
    public function getD3filesModel(): ActiveQuery
    {
        return $this->hasOne(D3filesModel::class, ['id' => 'd3files_model_id']);
    }
}
