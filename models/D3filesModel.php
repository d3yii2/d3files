<?php
declare(strict_types=1);

namespace d3yii2\d3files\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;
use function sprintf;

/**
 * This is the model class for table "d3files_model".
 *
 * @property int $id
 * @property string $d3files_id
 * @property int $is_file
 * @property int $model_name_id
 * @property string $model_id
 * @property int $deleted
 *
 * @property D3filesModelName $modelName
 * @property D3files $d3files
 */
class D3filesModel extends ActiveRecord
{

    public const int SHARED_EXPIRE_DAYS = 5;
    public const int SHARED_LEFT_LOADINGS = 5;

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'd3files_model';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['d3files_id', 'model_name_id'], 'required'],
            [['d3files_id', 'is_file', 'model_name_id', 'model_id', 'deleted'], 'integer'],
            [
                ['model_name_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => D3filesModelName::class,
                'targetAttribute' => ['model_name_id' => 'id']
            ],
            [
                ['d3files_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => D3files::class,
                'targetAttribute' => ['d3files_id' => 'id']
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
            'd3files_id' => Yii::t('d3files', 'D3files ID'),
            'is_file' => Yii::t('d3files', 'Is File'),
            'model_name_id' => Yii::t('d3files', 'Model Name ID'),
            'model_id' => Yii::t('d3files', 'Model ID'),
            'deleted' => Yii::t('d3files', 'Deleted'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getModelName(): ActiveQuery
    {
        return $this->hasOne(D3filesModelName::class, ['id' => 'model_name_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getD3files(): ActiveQuery
    {
        return $this->hasOne(D3files::class, ['id' => 'd3files_id']);
    }

    /**
     * @param $id
     * @param $model_name
     * @param $model_id
     * @return void
     * @throws Exception
     */
    public static function createCopy($id, $model_name, $model_id): void
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

    /**
     * @param $d3files_id
     * @return array
     */
    public static function findFileLinks($d3files_id): array
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
     * @param int $id D3filesModel ID
     * @param int|null $expireDays the period of validity days
     * @param int|null $leftLoadings allowed download count
     *
     * @return array|null [integer D3filesModelShared ID, string hex hash]
     * @throws Exception
     */
    public function createSharedModel(int $id, ?int $expireDays = null, ?int $leftLoadings = null): ?array
    {
        $d3filesModule = Yii::$app->getModule('d3files');

        $hashSalt = $d3filesModule->hashSalt ?? null;

        if (!$hashSalt) {
            return null;
        }

        $sharedExpireDays = $d3filesModule->sharedExpireDays ?? null;


        if (!$expireDays && !$expireDays === $sharedExpireDays) {
            $expireDays = self::SHARED_EXPIRE_DAYS;
        }

        $sharedLeftLoadings = $d3filesModule->sharedLeftLoadings ?? null;
        if (!$leftLoadings && !$leftLoadings === $sharedLeftLoadings) {
            $leftLoadings = self::SHARED_LEFT_LOADINGS;
        }

        if (!$fileModel = self::findOne(['id' => $id, 'deleted' => 0, 'is_file' => 1])) {
            return null;
        }

        if (!$file = D3files::findOne($fileModel->d3files_id)) {
            return null;
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
