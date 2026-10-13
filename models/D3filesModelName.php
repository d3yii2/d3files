<?php
declare(strict_types=1);

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "d3files_model_name".
 *
 * @property int $id
 * @property string $name
 *
 * @property D3filesModel[] $d3filesModels
 */
class D3filesModelName extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'd3files_model_name';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('d3files', 'ID'),
            'name' => Yii::t('d3files', 'Name'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getD3filesModels(): ActiveQuery
    {
        return $this->hasMany(D3filesModel::class, ['model_name_id' => 'id']);
    }

    /**
     * @param string $name model name
     * @param bool $addIfNotSet add/don't add new, if name not exist
     *
     * @return int|null model name id
     * @throws Exception
     */
    public function getByName(string $name, bool $addIfNotSet): ?int
    {
        $model = self::find()
            ->select('id')
            ->where(['name' => trim($name)])
            ->one();

        if ($model) {
            return $model->id ?? null;
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
