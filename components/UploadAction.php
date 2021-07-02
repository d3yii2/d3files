<?php

namespace d3yii2\d3files\components;

use d3yii2\d3files\components\D3Files as D3FilesComponent;
use d3yii2\d3files\exceptions\D3FilesUserException;
use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use Exception;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use function in_array;

/**
 * Class DownloadAction
 * @package d3yii2\d3files\components
 *
 * Uploads files and adds file records to D3files model
 */
class UploadAction extends D3FilesAction
{
    public $fileInputName = 'upload_file';
    
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
            
            if (!isset($_FILES[$this->fileInputName])) {
                throw new NotFoundHttpException(Yii::t('d3files', 'File not uploaded.'));
            }
            
            $this->modelName = $postModelName;
            
            // Check access rights to the record the file is attached to
            D3files::performReadValidation($this->modelName, $id);
            
            $initialPreview = [];
            $initialPreviewConfig = [];
            
            $modelFiles = D3FilesComponent::getModelFilesList($this->modelName, $id);
            
            $namesArr = [];
            foreach ($modelFiles as $file) {
                $namesArr[$file['file_model_id']] = $file['file_name'];
            }
            
            $fileNames = is_array($_FILES[$this->fileInputName]['name'])
                ? $_FILES[$this->fileInputName]['name']
                : [$_FILES[$this->fileInputName]['name']];
            
            $renderParam = [];
            
            foreach ($fileNames as $fileName) {
                $tmp_id = uniqid('d3f', false);
                $versionCounter = 0;
                $fileData = pathinfo($fileName);

                if(!isset($fileData['extension'])){
                    throw new D3FilesUserException(Yii::t('d3files','Can not upload the file without extension'));
                }

                // If the file with the same name exists, extend the name with the version, e.g. file(1).ext, file(2).ext
                do {
                    $versionName = $versionCounter > 0
                        ? $fileData['filename'] . '(' . ($versionCounter + 1) . ').' . $fileData['extension']
                        : $fileName;
                    $versionCounter++;
                } while (in_array($versionName, $namesArr, true));
                
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
                    throw new \yii\db\Exception(Yii::t('d3files', 'Insert DB record failed'));
                }
                
                $renderParam = [
                    'id' => $model->id,
                    'file_name' => $model->file_name,
                    'file_model_id' => $modelM->id,
                    'model_name' => $postModelName,
                ];
                
                $modelFileList = D3FilesComponent::getModelFilesList($postModelName, $modelM->model_id);
                
                $hasPreview = Yii::$app->request->get('preview');
                
                // Preview buttons for D3FilesPreviewWidget file list
                if ($hasPreview) {
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
                            $urlParams
                        );
                        $uploadedFile = D3FilesComponent::getFileFromListById($previewFileList, (string)$model->id);
                        $file = $uploadedFile ?? [];
                        
                        $renderParam['previewButtonContent'] = $this->controller->renderFile(
                            $d3filesModule->getView('d3files/' . D3FilesPreviewWidget::VIEW_MODAL_BUTTON),
                            [
                                'icon' => D3FilesPreviewWidget::DEFAULT_ICON,
                                'file' => $file,
                                'previewFileList' => $previewFileList
                            ]
                        );
                    } else {
                        $renderParam['previewButtonContent'] = '';
                    }
                }
                
                /**
                 *  Preview data for Kartik File Input
                 *  See the documentation examples: https://plugins.krajee.com/file-input-ajax-demo/6
                 * @TODO - move inside loop to process $_FILE array (support multiple files upload)
                 */
                
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $initialPreview[] = in_array($fileExtension, ['png', 'jpg', 'jpeg', 'gif'])
                    ? Url::to(['d3filesopen', 'id' => $modelM->id, 'model_name_id' => $model_name_id,], true)
                    : false;
                
                $initialPreviewConfig[] = [
                    'key' => $modelM->id,
                    'caption' => $model->file_name,
                    'size' => filesize($fileHandler->getUploadedFilePath()),
                    // URL to download the file
                    'downloadUrl' => Url::to([
                        'd3filesdownload',
                        'id' => $modelM->id,
                        'model_name_id' => $model_name_id,
                    ], true),
                    // URL to delete the file
                    'url' => Url::to([
                        'd3filesdelete',
                        'id' => $modelM->id,
                        'model_name_id' => $model_name_id,
                    ], true),
                    'type' => strtolower(pathinfo($model->file_name, PATHINFO_EXTENSION)),
                    'width' => '20px'
                ];
    
                // Can fix UPLOAD_ERR_PARTIAL problem with non-closing Keep-Alive connection
                header("Connection: close");
            }
            
            return [
                self::STATUS => self::STATUS_SUCCESS,
                self::MESSAGE => Yii::t('d3files', 'File uploaded successfully.'),
                'content' => $this->controller->renderFile($d3filesModule->getView('d3files/upload'), $renderParam),
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'initialPreviewAsData' => true,
               // 'previewFileIcon' => "<i class='glyphicon glyphicon-king'></i>",
            ];
        } catch (D3FilesUserException $e) {
            Yii::$app->response->statusCode = 406;
            return [
                self::STATUS => self::STATUS_ERROR,
                self::MESSAGE => $e->getMessage(),
            ];
        } catch (HttpException | NotFoundHttpException $e) {
            Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            Yii::$app->response->statusCode = 406;
            return [
                self::STATUS => self::STATUS_ERROR,
                self::MESSAGE => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Yii::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            Yii::$app->response->statusCode = 502;
            return [
                self::STATUS => self::STATUS_ERROR,
                self::MESSAGE => Yii::t('d3system', 'Unexpected Server Error'),
            ];
        }
    }
}
