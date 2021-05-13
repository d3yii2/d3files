<?php

namespace d3yii2\d3files\components;

use d3yii2\d3files\exceptions\D3FilesUserException;
use d3yii2\d3files\models\D3files;

use Exception;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;


/**
 * Class EditNotesAction
 * @package d3yii2\d3files\components
 *
 * Update existing file notes
 */
class EditNotesAction extends D3FilesAction
{
    /**
     * @param int $id
     * @return array
     */
    public function run(int $id): array
    {
        try {
            if (!$fileModel = D3files::findOne($id)) {
                throw new D3FilesUserException(Yii::t('d3files', 'The requested file does not exist.'));
            }
            
            //@FIXME - jāparedz iespēja pārbaudīt pieejas tiesības

            $notes = Yii::$app->request->post('notes');
            
            $fileModel->notes = $notes;
            $fileModel->save();

            return [
                self::STATUS => self::STATUS_SUCCESS,
                self::MESSAGE => Yii::t('d3files', 'File Notes updated'),
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
