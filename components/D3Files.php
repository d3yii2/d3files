<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;
use d3yii2\d3files\models\D3files as ModelD3Files;

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
     * @param string $viewExtensions
     * @return bool
     */
    public static function hasViewExtension(array $files, string $viewExtensions): bool
    {
        foreach ($files as $f) {
            $ext = self::getFileExtension($f);
            if (preg_match($viewExtensions, $ext)) {
                return true;
                break;
            }
        }
        return false;
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
     * @param string $contentTarget
     * @return array
     */
    public static function getPreviewFilesList(array $fileList, string $viewExtensions, array $urlParams, string $contentTarget): array
    {
        $fl = [];
        foreach ($fileList as $i => $file) {
            if (!self::hasViewExtension([$file], $viewExtensions)) {
                continue;
            }
            $file['content-target'] = $contentTarget;
            $urlParams['id'] = $file['file_model_id'];
            $file['src'] = Url::to($urlParams, true);
            $fl[] = $file;
        }

        return $fl;
    }

    /**
     * @param string $modelName
     * @param string $modelId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getModelFilesList(string $modelName, string $modelId): array
    {
        $files = ModelD3Files::fileListForWidget($modelName, $modelId);

        foreach ($files as $i => $f) {
            $files[$i]['model_id'] = $modelId;
        }
        return $files;
    }

    /**
     * @param array $list
     * @param int $id
     * @return array|null
     */
    public static function getFileFromListById(array $list, string $id): ?array
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
     * @throws \ReflectionException
     */
    public static function getAllowedFileTypes($modelName = null): string
    {
        if ($modelName) {
            // Check for model defined attachment types first
            $model = new \ReflectionClass($modelName);
            $modelFileTypes = $model->getConstant('D3FILES_ALLOWED_EXT_REGEXP');
            if ($modelFileTypes) {
                return $modelFileTypes;
            }
        }

        return Yii::$app->getModule('d3files')->fileTypes ?? FileHandler::FILE_TYPES;
    }
}
