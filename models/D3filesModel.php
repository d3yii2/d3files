<?php

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

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
class D3filesModel extends ActiveRecord
{

    const SHARED_EXPIRE_DAYS   = 5;
    const SHARED_LEFT_LOADINGS = 5;

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

        $model = self::findOne($id);
        $newModel = new self();
        $newModel->d3files_id = $model->d3files_id;
        $newModel->is_file = 0;
        $newModel->model_name_id = $model_name_id;
        $newModel->model_id = $model_id;
        $newModel->save();
                
    }
    
    public static function findFileLinks($d3files_id)
    {
        return self::find()
                ->select('mn.name model_name,model_id')
                ->innerJoin('d3files_model_name mn', 'd3files_model.model_name_id = mn.id')
                ->where([
                    'd3files_model.d3files_id' => $d3files_id,
                    'd3files_model.deleted' => 0,
                        ])
                ->asArray()
                ->all();
    }

    /**
     * @param integer $id D3filesModel ID
     * @param integer $expireDays the period of validity days
     * @param integer $leftLoadings allowed download count
     *
     * @return array|bool [integer D3filesModelShared ID, string hex hash]
     */
    public function createSharedModel($id, $expireDays = null, $leftLoadings = null)
    {

        if (!$hashSalt = Yii::$app->getModule('d3files')->hashSalt) {
            return false;
        }

        if (!$expireDays && !$expireDays = Yii::$app->getModule('d3files')->sharedExpireDays) {
            $expireDays = self::SHARED_EXPIRE_DAYS;
        }

        if (!$leftLoadings && !$leftLoadings = Yii::$app->getModule('d3files')->sharedLeftLoadings) {
            $leftLoadings = self::SHARED_LEFT_LOADINGS;
        }

        if (!$fileModel = self::findOne(['id' => $id, 'deleted' => 0, 'is_file' => 1])) {
            return false;
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            return false;
        }

        $fileModelShared = new D3filesModelShared();
        $fileModelShared->d3files_model_id = $id;
        $fileModelShared->expire_date      = new Expression(
            'DATE_ADD(CURDATE(), INTERVAL ' . $expireDays . ' DAY)'
        );
        $fileModelShared->left_loadings    = $leftLoadings;

        $fileModelShared->save();

        $hashText = sprintf('%s:%s:%s', $fileModelShared->id, $file->file_name, $hashSalt);

        $fileModelShared->hash = strtoupper(md5($hashText));
        $fileModelShared->save();

        return ['id' => $fileModelShared->id, 'hash' => $fileModelShared->hash];

    }
}
