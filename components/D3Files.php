<?php
declare(strict_types=1);

namespace d3yii2\d3files\components;

use d3system\exceptions\D3ActiveRecordException;
use d3system\exceptions\D3Exception;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\Component;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Url;
use d3yii2\d3files\models\D3files as ModelD3Files;
use yii\web\UploadedFile;
use function get_class;

/**
 * Class D3Files
 * @package d3yii2\d3files\components
 */
class D3Files extends Component
{
    /**
     * Get file extension
     * @param array $file
     * @return string
     */
    public static function getFileExtension(array $file): string
    {
        return strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
    }

    /**
     * Return the first file of the fileList having extension
     * @param array $files
     * @param string $extension
     * @return array|null
     */
    public static function getFirstFileHavingExt(array $files, string $extension): ?array
    {
        foreach ($files as $file) {
            $fileExtension = self::getFileExtension($file);
            if ($extension === $fileExtension) {
                return $file;
            }
        }
        return null;
    }

    /**
     * @param array $files
     * @param string $extensions
     * @return bool
     */
    public static function hasFileWithExtension(array $files, string $extensions): bool
    {
        foreach ($files as $f) {
            if (self::fileHasExtension($f, $extensions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $file
     * @param string $extensions
     * @return bool
     * @return bool
     */
    public static function fileHasExtension(array $file, string $extensions): bool
    {
        $ext = self::getFileExtension($file);
        return preg_match($extensions, $ext) === 1;
    }

    /**
     * Filter the files by extension
     * @param array $files
     * @param string $ext
     * @return array
     */
    public static function getFilesListByExt(array $files, string $ext): array
    {
        $list = [];
        foreach ($files as $file) {
            $fileExt = self::getFileExtension($file);
            if ($ext === $fileExt) {
                $list[$file['id']] = $file;
            }
        }
        return $list;
    }

    /**
     * @param array $fileList
     * @param string $viewExtensions
     * @param array $urlParams
     * @return array
     */
    public static function getPreviewFilesList(array $fileList, string $viewExtensions, array $urlParams): array
    {
        $fl = [];
        foreach ($fileList as $file) {
            if (self::hasFileWithExtension([$file], $viewExtensions)) {
                $urlParams['id'] = $file['file_model_id'];
                $file['src'] = Url::to($urlParams, true);
                $fl[] = $file;
            }
        }
        return $fl;
    }

    /**
     * @param string $modelName
     * @param int $modelId
     * @param bool $forPath get data for building file path
     * @return array
     * @throws Exception
     */
    public static function getModelFilesList(
        string $modelName,
        int $modelId,
        bool $forPath = false
    ): array {
        $files = ModelD3Files::fileListForWidget($modelName, $modelId, $forPath);

        foreach ($files as $i => $f) {
            $files[$i]['model_id'] = $modelId;
        }
        return $files;
    }

    /**
     * @param int $modelNameId
     * @param int $modelId
     * @return array
     * @throws Exception
     */
    public static function getModelFilesListByNameId(int $modelNameId, int $modelId): array
    {
        $files = ModelD3Files::fileListForWidgetByNameId($modelNameId, $modelId);

        foreach ($files as $i => $f) {
            $files[$i]['model_id'] = $modelId;
        }
        return $files;
    }

    /**
     * @param array $list
     * @param string $id
     * @return array|null
     */
    public static function getFileFromListById(array $list, int $id): ?array
    {
        foreach ($list as $file) {
            if ($id === $file['id']) {
                return $file;
            }
        }
        return null;
    }

    /**
     * @param null $modelName
     * @return string
     * @throws ReflectionException
     */
    public static function getAllowedFileTypes($modelName = null): string
    {
        if ($modelName) {
            // Check for model defined attachment types first
            $model = new ReflectionClass($modelName);
            $modelFileTypes = $model->getConstant('D3FILES_ALLOWED_EXT_REGEXP');
            if ($modelFileTypes) {
                return $modelFileTypes;
            }
        }

        return Yii::$app->getModule('d3files')->fileTypes ?? FileHandler::FILE_TYPES;
    }

    /**
     * @param ActiveRecord $model
     * @param string $className
     * @param string $field
     * @throws \Exception
     */
    public static function uploadModelFile(ActiveRecord  $model, string $className, string $field = 'upload_file'): void
    {
        if ($model->primaryKey) {
            $uploadedFile = UploadedFile::getInstance($model, $field);
    
            if ($uploadedFile) {
                ModelD3Files::saveYii2UploadFile($uploadedFile, $className, $model->primaryKey);
            }
        }
    }

    /**
     * @param ActiveRecord $modelFrom
     * @param ActiveRecord $modelTo
     * @param string|null $modelFromClass
     * @param int|null $userId if not set, D3files uses current web user id
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     * @throws D3ActiveRecordException
     * @throws D3Exception
     * @throws \yii\base\Exception
     */
    public static function copyFilesBetweenModels(
        ActiveRecord $modelFrom,
        ActiveRecord $modelTo,
        ?string $modelFromClass = null,
        ?int $userId = null
    ): bool {
        $modelFromName = $modelFromClass ?? get_class($modelFrom);
        $modelToName = get_class($modelTo);

        $modelFromId = $modelFrom->id ?? null;
        $modelFiles = ModelD3Files::getRecordFilesList($modelFromName, $modelFromId);
    
        foreach ($modelFiles as $file) {
            // Just ignore non-existent files silently
            if (!file_exists($file['file_path'])) {
                continue;
            }
            
            $fileContent = file_get_contents($file['file_path']);
            
            $ext = pathinfo($file['file_name'], PATHINFO_EXTENSION);
 
            $fileTypes = self::getAllowedFileTypes($modelToName);
            if (!preg_match($fileTypes, $ext)) {
                continue;
            }
            
            ModelD3Files::saveContent(
                $file['file_name'],
                $modelToName,
                $modelTo->id ?? null,
                $fileContent,
                $fileTypes,
                $userId
            );
        }
        
        return true;
    }

    /**
     * @return bool
     */
    public static function isNotesEnabled(): bool
    {
        return Yii::$app->getModule('d3files')->enableNotes ?? false;
    }
}
