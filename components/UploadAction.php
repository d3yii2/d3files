<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Uploads files and adds file records to D3files model
 */
class UploadAction extends Action
{

    /**
     * @var string parent model name (with namespace)
     * $_POST['model_name'] is used if not set in standalone class configuration:
     *      'd3filesupload' => [
     *          'class'     => 'd3yii2\d3files\components\UploadAction',
     *          'modelName' => 'app\models\Test',
     *       ],
     */
    public $modelName;

    public function run($id)
    {
        // $id here is id for model to which will be attached attachments

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!isset($_FILES['upload_file'])) {
            throw new NotFoundHttpException(Yii::t('d3files', 'File not uploaded.'));
        }

        // if modelName is not set use $_POST['model_name'] value
        $this->modelName or $this->modelName = Yii::$app->request->post('model_name');

        if (empty($this->modelName)) {
            throw new HttpException(422, Yii::t('d3files', 'mandatory POST parameter model_name is not set'));
        }

        // Check access rights to the record the file is attached to
        D3files::performReadValidation($this->modelName, $id);

        $tmp_id = uniqid();

        $fileHandler = new FileHandler(
            [
                'model_name' => $this->modelName,
                'model_id'   => $tmp_id,
                'file_name'  => $_FILES['upload_file']['name'],
            ]
        );

        $fileHandler->upload();

        $model = new D3files();

        $model->file_name    = $_FILES['upload_file']['name'];
        $model->add_datetime = new \yii\db\Expression('NOW()');
        $model->user_id      = Yii::$app->user->getId();

        if ($model->save()) {

            // Get or create model name id
            $modelMN = new D3filesModelName();
            $model_name_id = $modelMN->getByName($this->modelName, true);

            $modelM = new D3filesModel();
            $modelM->d3files_id    = $model->id;
            $modelM->is_file       = 1;
            $modelM->model_name_id = $model_name_id;
            $modelM->model_id      = $id;
            $modelM->save();

            $fileHandler->rename($model->id);
        } else {
            $fileHandler->remove();
            throw new HttpException(500, Yii::t('d3files', 'Insert DB record failed'));
        }

        $renderParam = [
            'id' => $model->id,
            'file_name' => $model->file_name,
            'file_model_id' => $modelM->id,
        ];

        //return $this->controller->renderPartial('upload', $renderParam);
        return $this->controller->renderPartial(
            '@vendor/d3yii2/d3files/views/d3files/upload',
            $renderParam
        );
    }
}
