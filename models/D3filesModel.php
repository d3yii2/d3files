<?php

namespace d3yii2\d3files\models;

use Yii;

/**
 * This is the model class for table "d3files_model".
 *
 * @property string $id
 * @property string $d3files_id
 * @property integer $is_file
 * @property integer $model_name_id
 * @property string $model_id
 * @property integer $deleted
 *
 * @property D3filesModelName $modelName
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
            [['d3files_id', 'model_name_id'], 'required'],
            [['d3files_id', 'is_file', 'model_name_id', 'model_id', 'deleted'], 'integer'],
            [['model_name_id'], 'exist', 'skipOnError' => true, 'targetClass' => D3filesModelName::className(), 'targetAttribute' => ['model_name_id' => 'id']],
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
            'model_name_id' => Yii::t('d3files', 'Model Name ID'),
            'model_id' => Yii::t('d3files', 'Model ID'),
            'deleted' => Yii::t('d3files', 'Deleted'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModelName()
    {
        return $this->hasOne(D3filesModelName::className(), ['id' => 'model_name_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getD3files()
    {
        return $this->hasOne(D3files::className(), ['id' => 'd3files_id']);
    }
    
    public static function createCopy($id, $model_name, $model_id)
    {

        // Get or create model name id
        $modelMN = new D3filesModelName();
        $model_name_id = $modelMN->getByName($model_name, true);

        $model = D3filesModel::findOne($id);
        $newModel = new D3filesModel();
        $newModel->d3files_id = $model->d3files_id;
        $newModel->is_file = 0;
        $newModel->model_name_id = $model_name_id;
        $newModel->model_id = $model_id;
        $newModel->save();
                
    }
    
    public static function findFileLinks($d3files_id)
    {
        return D3filesModel::find()
                ->select('model_name,model_id')
                ->where([
                    'd3files_id' => $d3files_id,
                    'deleted' => 0,
                        ])
                ->asArray()
                ->all();
    }
}
