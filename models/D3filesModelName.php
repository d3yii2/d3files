<?php

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "d3files_model_name".
 *
 * @property integer $id
 * @property string $name
 *
 * @property D3filesModel[] $d3filesModels
 */
class D3filesModelName extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'd3files_model_name';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('d3files', 'ID'),
            'name' => Yii::t('d3files', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getD3filesModels()
    {
        return $this->hasMany(D3filesModel::className(), ['model_name_id' => 'id']);
    }

    /**
     * @param string $name model name
     * @param bool $addIfNotSet add/don't add new, if name not exist
     *
     * @return integer model name id
     */
    public function getByName($name, $addIfNotSet)
    {
        $model = self::find()
            ->select('id')
            ->where(['name' => $name])
            ->one();

        if ($model) {
            return $model->id;
        }

        if (!$addIfNotSet) {
            return null;
        }

        $newModel = new self();
        $newModel->name = $name;
        $newModel->save();

        return $newModel->id;

    }
}
