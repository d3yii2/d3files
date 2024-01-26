<?php

namespace d3yii2\d3files\components;

use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use Exception;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use function in_array;

/**
 * Class DeleteAction
 * @package d3yii2\d3files\components
 *
 * Deletes an existing D3files model record (sets deleted=1).
 */
class DeleteAction extends D3FilesAction
{
    /**
     * @param int $id
     * @param string $model_name
     * @return array
     */
    public function run(int $id, string $model_name = ''): array
    {
        try {
            if (!$model_name) {
                $model_name = $this->modelName;
            }

            if (!Yii::$app->getModule('d3files')->disableController) {
                if (is_array($this->modelName) && !in_array($model_name, $this->modelName, true)) {
                    throw new HttpException(422, 'Can not delete file for requested model');
                }

                if (!is_array($this->modelName) && $model_name !== $this->modelName) {
                    throw new HttpException(422, 'Can not delete file for requested model');
                }
            }

            $this->modelName = $model_name;

            D3Files::deleteFileById($id);

            return [
                self::STATUS => self::STATUS_SUCCESS,
                self::MESSAGE => Yii::t('d3files', 'File deleted'),
            ];
        } catch (HttpException | NotFoundHttpException $e) {
            Yii::error($e->getMessage());
            Yii::$app->response->statusCode = 406;
            return [self::STATUS => self::STATUS_ERROR, self::MESSAGE => $e->getMessage()];
        } catch (Exception $e) {
            Yii::error($e->getMessage());
            Yii::$app->response->statusCode = 502;
            return [self::STATUS => self::STATUS_ERROR, self::MESSAGE => Yii::t('d3system', 'Unexpected Server Error')];
        }
    }
}
