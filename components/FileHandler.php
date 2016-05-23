<?php
namespace app\vendor\d3yii2\d3files\components;

use yii\web\NotFoundHttpException;
use yii\base\InvalidParamException;
use yii\web\ForbiddenHttpException;

class FileHandler
{
    
    const FILE_TYPES = '/(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm)$/i';
    
    protected $options;

    public function __construct($options) {
        
        if (!isset($options['model_name'])) {
            throw new InvalidParamException('UploadHandler mandatory option module_name is not set');
        }
        
        if (!isset($options['model_id'])) {
            throw new InvalidParamException('UploadHandler mandatory option model_id is not set');
        }
        
        if (!isset($options['file_name'])) {
            throw new InvalidParamException('UploadHandler mandatory option file_name is not set');
        }
        
        $this->options['upload_dir'] = self::getUploadDirPath($options['model_name']); 
        $this->options['file_types'] = self::getAllowedFileTypes($options);
        $this->options['model_name'] = $options['model_name'];
        $this->options['model_id']   = $options['model_id'];
        $this->options['file_name']  = $options['file_name'];
        
        if (!preg_match(
            $this->options['file_types'],
            pathinfo($this->options['file_name'])['extension']
        )
        ) {
            throw new ForbiddenHttpException('Forbidden file type');
        }
        
    }
    
    protected static function getAllowedFileTypes($options = [])
    {
        if (isset($options['file_types'])) {
            return $options['file_types'];
        }
        
        if (!$file_types = \Yii::$app->getModule('d3files')->file_types) {
            $file_types = self::FILE_TYPES;
        }
        
        return $file_types;
    }
    
    protected static function getUploadDirPath($model_name)
    {
        return \Yii::$app->getModule('d3files')->upload_dir
            . DIRECTORY_SEPARATOR . $model_name;
    }
    
    public function upload()
    {
        
        if (!isset($_FILES['upload_file'])) {
            throw new InvalidParamException('upload_file is not set');
        }
        
        if (!move_uploaded_file(
            $_FILES['upload_file']['tmp_name'],
            $this->options['upload_dir'] . DIRECTORY_SEPARATOR
                . self::createSaveFileName(
                    $this->options['model_id'],
                    $this->options['file_name']
                )
        )
        ) {
            throw new NotFoundHttpException('The uploaded file does not exist.');
        }
        
        return true;
        
    }
    
    public function rename($new_id) {
        
        $oldName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $this->options['model_id'],
                $this->options['file_name']
            );
        
        $newName = $this->options['upload_dir'] . DIRECTORY_SEPARATOR
            . self::createSaveFileName(
                $new_id,
                $this->options['file_name']
            );
        
        rename($oldName, $newName);
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
            throw new NotFoundHttpException('The requested file does not exist.');
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
