<?php
declare(strict_types=1);

namespace d3yii2\d3files\widgets;

use Exception;
use Yii;
use yii\base\Widget;
use d3yii2\d3files\models\D3filesModel;
use d3yii2\d3files\D3Files;
use function dirname;

/**
 *
 */
class D3FilesFileDownloadWidget extends Widget
{
    public string $downloadUrl;
    public int $fileModelId;
    private string $fileName;


    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();
        D3Files::registerTranslations();
        
        $this->fileName = D3filesModel::findOne($this->fileModelId)
                    ->getD3files()
                    ->one()->file_name ?? null;
    }

    /**
     * @return string|null
     */
    public function run(): ?string
    {
        try {
            return $this->renderFile(
                $this->getViewPath() . 'file_download.php',
                [
                    'fileModelId' => $this->fileModelId,
                    'downloadUrl'   => $this->downloadUrl,
                    'fileName'      => $this->fileName,
                ]
            );
        } catch (Exception $exception) {
            Yii::error('D3FilesFileDownloadWidget:run Exception: ' . $exception->getMessage());
        }

        return '';
    }

    /**
     * @return string
     */
    public function getViewPath(): string
    {
        if (!$viewPath = Yii::$app->getModule('d3files')->viewPath) {
            $viewPath = dirname(__DIR__) . '/views';
        }
        return $viewPath . '/d3filedownload/';
    }
}
