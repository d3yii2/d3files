<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

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
     * @param array $viewExtensions
     * @return bool
     */
    public static function hasViewExtension(array $files, array $viewExtensions): bool
    {
        foreach ($files as $f) {
            $ext = self::getFileExtension($f);
            if (in_array($ext, $viewExtensions, true)) {
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

}
