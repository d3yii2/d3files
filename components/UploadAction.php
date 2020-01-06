<?php
namespace d3yii2\d3files\components;

use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use Yii;
use yii\base\Action;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\d3files\components\D3Files as D3FilesComponent;
use yii\db\Expression;
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
     * @var string|string[] parent model name (with namespace)
     * $_POST['model_name'] is used if controller actions are not disabled
     */
    public $modelName;

    public function run(int $id): string
    {
        try {

            // $id here is id for model to which will be attached attachments

            Yii::$app->response->format = Response::FORMAT_JSON;

            $postModelName = Yii::$app->request->post('model_name');

            /** @var \d3yii2\d3files\D3Files $d3filesModule */
            $d3filesModule = Yii::$app->getModule('d3files');

            if (!$d3filesModule->disableController) {
                if (is_array($this->modelName) && !in_array($postModelName, $this->modelName, true)) {
                    throw new HttpException(422, 'Can not upload file for requested model');
                }

                if (!is_array($this->modelName) && $postModelName !== $this->modelName) {
                    throw new HttpException(422, 'Can not upload file for requested model');
                }
            }

            if (!isset($_FILES['upload_file'])) {
                throw new NotFoundHttpException(Yii::t('d3files', 'File not uploaded.'));
            }

            $this->modelName = $postModelName;


            // Check access rights to the record the file is attached to
            D3files::performReadValidation($this->modelName, $id);

            $tmp_id = uniqid('d3f', false);

            $fileHandler = new FileHandler(
                [
                    'model_name' => $this->modelName,
                    'model_id' => $tmp_id,
                    'file_name' => $_FILES['upload_file']['name'],
                ]
            );

            $fileHandler->upload();

            $model = new D3files();

            $model->file_name = $_FILES['upload_file']['name'];
            $model->add_datetime = new Expression('NOW()');
            $model->user_id = Yii::$app->user->getId();

            if ($model->save()) {

                // Get or create model name id
                $modelMN = new D3filesModelName();
                $model_name_id = $modelMN->getByName($this->modelName, true);

                $modelM = new D3filesModel();
                $modelM->d3files_id = $model->id;
                $modelM->is_file = 1;
                $modelM->model_name_id = $model_name_id;
                $modelM->model_id = $id;
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
                'model_name' => $postModelName,
            ];

            $hasPreview = Yii::$app->request->get('preview');

            if ($hasPreview) {
                $renderParam['icon'] = D3FilesPreviewWidget::DEFAULT_ICON;
                $renderParam['previewButton'] = D3FilesPreviewWidget::VIEW_MODAL_BUTTON;
                $fModel = new D3filesModel();
                $fModel->id = $id;
                $urlParams = [
                    'd3filesopen',
                    'model_name' => $postModelName,
                ];
                $modelFileList = D3FilesComponent::getModelFilesList($postModelName, $modelM->model_id);
                $previewFileList = D3FilesComponent::getPreviewFilesList(
                    $modelFileList,
                    ['pdf', 'png', 'jpg', 'jpeg'],
                    $urlParams,
                    D3FilesPreviewWidget::EMBED_CONTENT_CLASS
                );
                $renderParam['fileList'] = $previewFileList;
                $uploadedFile = D3FilesComponent::getFileFromListById($previewFileList, (string) $model->id);
                $renderParam['file'] = $uploadedFile ?? [];
            }

            return $this->controller->renderFile(
                $d3filesModule->getView('d3files/upload'),
                $renderParam
            );
        } catch (HttpException | NotFoundHttpException $e) {
            Yii::error($e->getMessage());
            return $e->getMessage();
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return '';
        }
    }
}
