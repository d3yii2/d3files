<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

class FileHandler
{
    
    const FILE_TYPES = '/(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm|txt)$/i';
    
    protected $options;

    public function __construct($options) {
        
        if (!isset($options['model_name'])) {
            throw new InvalidParamException(Yii::t('d3files', 'UploadHandler mandatory option module_name is not set'));
        }
        
        if (!isset($options['model_id'])) {
            throw new InvalidParamException(Yii::t('d3files', 'UploadHandler mandatory option model_id is not set'));
        }
        
        if (!isset($options['file_name'])) {
            throw new InvalidParamException(Yii::t('d3files', 'UploadHandler mandatory option file_name is not set'));
        }
        
        $this->options['upload_dir'] = self::getUploadDirPath($options['model_name']); 
        $this->options['file_types'] = self::getAllowedFileTypes($options);
        $this->options['model_name'] = $options['model_name'];
        $this->options['model_id']   = $options['model_id'];
        $this->options['file_name']  = $options['file_name'];
        if(isset($options['file_path'])){
            $this->options['file_path']  = $options['file_path'];
        }
        
        $fileExtension = pathinfo($this->options['file_name'])['extension'];
        if ($this->options['file_types']  != '*' 
                && !preg_match($this->options['file_types'],$fileExtension)) {
            throw new ForbiddenHttpException(Yii::t('d3files', 'Forbidden file type: ' . $fileExtension));
        }
        
    }
    
    protected static function getAllowedFileTypes($options = [])
    {
        if (isset($options['file_types'])) {
            return $options['file_types'];
        }
        
        if (!$file_types = Yii::$app->getModule('d3files')->file_types) {
            $file_types = self::FILE_TYPES;
        }
        
        return $file_types;
    }
    
    protected static function getUploadDirPath($model_name)
    {
        $pos = strrpos($model_name, '\\');
        $modelShortName = $pos === false ? $model_name : substr($model_name, $pos + 1);
        
        return Yii::$app->getModule('d3files')->upload_dir
            . DIRECTORY_SEPARATOR . $modelShortName;
    }
    
    /**
     * copy posted file to upload directory
     * @return boolean
     * @throws InvalidParamException
     * @throws NotFoundHttpException
     */
    public function upload()
    {
        
        if (!isset($_FILES['upload_file'])) {
            throw new InvalidParamException(Yii::t('d3files', 'upload_file is not set'));
        }
        
        if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $this->getFilePath())) {
            throw new NotFoundHttpException(Yii::t('d3files', 'The uploaded file does not exist.'));
        }
        
        return true;
        
    }

    /**
     * copy Yii2 UploadedFile
     * @param UploadedFile $upoadFile 
     * @return boolean
     * @throws NotFoundHttpException
     */
    public function uploadYii2UloadFile(UploadedFile $upoadFile)
    {
        
        if (!$upoadFile->saveAs($this->getFilePath())) {
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
        if(isset($this->options['file_path'])){
            return $this->options['file_path'];
        }
        
        return $this->options['upload_dir'] . DIRECTORY_SEPARATOR
                . self::createSaveFileName(
                    $this->options['model_id'],
                    $this->options['file_name']
                );
    }

    /**
     * save file. Alternative for method  upload()
     * @param string $fileContent
     * @return boolean true
     */
    public function save(&$fileContent)
    {
        file_put_contents($this->getFilePath(), $fileContent);
        
        return true;
    }

    public function rename($new_id) {
        
        $newName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $new_id,
                $this->options['file_name']
            );
        
        rename($this->getFilePath(), $newName);
    }
    
    public function remove() {
        
        $oldName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $this->options['model_id'],
                $this->options['file_name']
            );
        
        return unlink($oldName);
    }
    
    public function download()
    {
        $file_path = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
                . self::createSaveFileName(
                    $this->options['model_id'],
                    $this->options['file_name']
                );
        
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
    
    protected static function createSaveFileName($d3files_id, $file_name)
    {
        return $d3files_id . '.' . pathinfo($file_name)['extension'];
    }
}
