<?php

namespace d3yii2\d3files\components;

use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

use function dirname;

/**
 * Class FileHandler
 * @package d3yii2\d3files\components
 */
class FileHandler
{
    
    const FILE_TYPES = '/(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm|txt|zip|csv)$/i';
    
    protected $options;

    /**
     * FileHandler constructor.
     * @param $options
     * @throws ForbiddenHttpException
     * @throws ReflectionException
     */
    public function __construct($options)
    {
        if (!isset($options['model_name'])) {
            throw new InvalidArgumentException(
                Yii::t('d3files', 'UploadHandler mandatory option module_name is not set')
            );
        }

        if (!isset($options['model_id'])) {
            throw new InvalidArgumentException(Yii::t('d3files', 'UploadHandler mandatory option model_id is not set'));
        }

        if (!isset($options['file_name'])) {
            throw new InvalidArgumentException(
                Yii::t('d3files', 'UploadHandler mandatory option file_name is not set')
            );
        }

        $this->options['upload_dir'] = self::getUploadDirPath($options['model_name']);
        $this->options['file_types'] = self::getAllowedFileTypes($options);
        $this->options['model_name'] = $options['model_name'];
        $this->options['model_id'] = $options['model_id'];
        $this->options['file_name'] = $options['file_name'];
        if (isset($options['file_path'])) {
            $this->options['file_path'] = $options['file_path'];
        }

        $fileTypes = self::getAllowedFileTypes($this->options);

        $fileExtension = pathinfo($this->options['file_name'])['extension'];
        if ('*' !== $fileTypes
            && !preg_match($fileTypes, $fileExtension)) {
            throw new ForbiddenHttpException(Yii::t('d3files', 'Forbidden file type: {0}', [$fileExtension]));
        }
    }

    /**
     * @param $model_name
     * @return string
     */
    protected static function getUploadDirPath($model_name)
    {
        $pos = strrpos($model_name, '\\');
        $modelShortName = false === $pos ? $model_name : substr($model_name, $pos + 1);

        return Yii::$app->getModule('d3files')->uploadDir
            . DIRECTORY_SEPARATOR . $modelShortName;
    }

    /**
     * @param array $options
     * @return string
     * @throws ReflectionException
     */
    protected static function getAllowedFileTypes(array $options = []): string
    {
        // Check for model defined attachment types first
        $model = new ReflectionClass($options['model_name']);
        $modelFileTypes = $model->getConstant('D3FILES_ALLOWED_EXT_REGEXP');
        if ($modelFileTypes) {
            return $modelFileTypes;
        }

        if (isset($options['file_types'])) {
            return $options['file_types'];
        }

        return Yii::$app->getModule('d3files')->fileTypes ?? self::FILE_TYPES;
    }

    /**
     * copy posted file to upload directory
     * @return bool
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function upload()
    {
        if (!isset($_FILES['upload_file'])) {
            throw new InvalidArgumentException(Yii::t('d3files', 'upload_file is not set'));
        }

        $filePath = $this->getFilePath();
        $dir = dirname($filePath);
        FileHelper::createDirectory($dir);
        if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $filePath)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The uploaded file does not exist.'));
        }

        return true;
    }

    /**
     * get file path for saving uploaded file
     * @return string
     */
    public function getFilePath()
    {
        if (isset($this->options['file_path'])) {
            return $this->options['file_path'];
        }

        return $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $this->options['model_id'],
                $this->options['file_name']
            );
    }

    /**
     * @param $d3files_id
     * @param $file_name
     * @return string
     */
    protected static function createSaveFileName($d3files_id, $file_name)
    {
        return $d3files_id . '.' . pathinfo($file_name)['extension'];
    }

    /**
     * copy Yii2 UploadedFile
     * @param UploadedFile $uploadedFile
     * @return bool
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function uploadYii2UloadFile(UploadedFile $uploadedFile)
    {
        $filePath = $this->getFilePath();
        FileHelper::createDirectory(dirname($filePath));
        if (!$uploadedFile->saveAs($filePath)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The uploaded file does not exist.'));
        }

        return true;
    }

    /**
     * save file. Alternative for method  upload()
     * @param $fileContent
     * @return bool
     * @throws Exception
     */
    public function save(&$fileContent)
    {
        $filePath = $this->getFilePath();
        FileHelper::createDirectory(dirname($filePath));
        file_put_contents($filePath, $fileContent);

        return true;
    }

    /**
     * @param $new_id
     * @throws Exception
     */
    public function rename($new_id)
    {
        FileHelper::createDirectory($this->options['upload_dir']);

        $newName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $new_id,
                $this->options['file_name']
            );

        rename($this->getFilePath(), $newName);
    }

    /**
     * @return bool
     */
    public function remove()
    {
        $oldName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $this->options['model_id'],
                $this->options['file_name']
            );

        return unlink($oldName);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function download()
    {
        $file_path = $this->getFilePath();

        if (!is_file($file_path)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $this->options['file_name'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file_path));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        readfile($file_path);
        exit;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function open()
    {
        $file_path = $this->getFilePath();

        if (!is_file($file_path)) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The requested file does not exist.'));
        }
        $mimeType = FileHelper::getMimeTypeByExtension($this->options['file_name']);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $this->options['file_name'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file_path));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        readfile($file_path);
        exit;
    }

    /**
     * @param $id
     */
    public function setModelId($id)
    {
        $this->options['model_id'] = $id;
    }
}
