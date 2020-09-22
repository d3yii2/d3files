<?php
namespace d3yii2\d3files\components;

use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use Exception;
use Yii;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\d3files\components\D3Files as D3FilesComponent;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\HttpException;

use function in_array;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Uploads files and adds file records to D3files model
 */
class UploadAction extends D3FilesAction
{
    /**
     * @param int $id
     * @return array
     */
    public function run(int $id): array
    {
        try {
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

            $fileName = $_FILES['upload_file']['name'];
            
            $modelFiles = D3FilesComponent::getModelFilesList($this->modelName, $id);
            
            $namesArr = [];
            foreach ($modelFiles as $file) {
                $namesArr[$file['file_model_id']] = $file['file_name'];
            }
            
            $versionOk = false;
            $versionCounter = 0;
            $fileData = pathinfo($fileName);
    
            // If the file with the same name exists, extend the name with the version, e.g. file(1).ext, file(2).ext
            do {
                $versionName = $versionCounter > 0
                    ? $fileData['filename'] . '(' . ( $versionCounter + 1 ) . ') . ' . $fileData['extension']
                    : $fileName;
                $versionCounter ++;
            } while (in_array($versionName, $namesArr));
    
            $fileHandler = new FileHandler(
                [
                    'model_name' => $this->modelName,
                    'model_id' => $tmp_id,
                    'file_name' => $versionName,
                ]
            );
        
            $fileHandler->upload();

            $model = new D3files();

            $model->file_name = $versionName;
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
                $modelFileList = D3FilesComponent::getModelFilesList($postModelName, $modelM->model_id);

                $previewExtensions = '/(gif|pdf|jpe?g|png)$/i';

                if (D3FilesComponent::fileHasExtension($renderParam, $previewExtensions)) {
                    $fModel = new D3filesModel();
                    $fModel->id = $id;
                    $urlParams = [
                        'd3filesopen',
                        'model_name_id' => $model_name_id,
                    ];
                    $previewFileList = D3FilesComponent::getPreviewFilesList(
                        $modelFileList,
                        $previewExtensions,
                        $urlParams,
                        D3FilesPreviewWidget::EMBED_CONTENT_CLASS
                    );
                    $uploadedFile = D3FilesComponent::getFileFromListById($previewFileList, (string) $model->id);
                    $file = $uploadedFile ?? [];

                    $renderParam['previewButtonContent'] = $this->controller->renderFile(
                        $d3filesModule->getView('d3files/' . D3FilesPreviewWidget::VIEW_MODAL_BUTTON),
                        ['icon' => D3FilesPreviewWidget::DEFAULT_ICON, 'file' => $file, 'previewFileList' => $previewFileList]
                    );
                } else {
                    $renderParam['previewButtonContent'] = '';
                }
            }

            return [
                self::STATUS => self::STATUS_SUCCESS,
                self::MESSAGE => Yii::t('d3files', 'File uploaded successfully.'),
                'content' => $this->controller->renderFile($d3filesModule->getView('d3files/upload'), $renderParam)
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
