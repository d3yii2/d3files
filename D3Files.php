<?php
declare(strict_types=1);

namespace d3yii2\d3files;

use Yii;
use yii\base\Module;
use yii\i18n\PhpMessageSource;

/**
 * d3files module definition class
 */
class D3Files extends Module
{
    public string|\Closure $uploadDir = '.';

    public string $fileTypes = '';

    public bool $disableController = false;

    public ?string $hashSalt = null;

    public ?int $sharedExpireDays = null;

    public ?int $sharedLeftLoadings = null;

    public array $imageExtensions = ['jpg', 'gif', 'png', 'bmp'];

    public bool $enableNotes = false;

    //public ?string $viewPathAlias = null;

    /**
     * Initializes the module.
     *
     * This method is called after the module is created and initialized with property values
     * given in configuration. The default implementation will initialize [[controllerNamespace]]
     * if it is not set.
     *
     * If you override this method, please make sure you call the parent implementation.
     */
    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();

        $this->controllerNamespace = 'd3yii2\d3files\controllers';

        self::registerTranslations();
    }

    /**
     * @return void
     */
    public static function registerTranslations(): void
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['d3files'] = [
            'class'            => PhpMessageSource::class,
            'sourceLanguage'   => 'en-US',
            'basePath'         => __DIR__ . '/messages',
            'forceTranslation' => true
        ];
    }

    /**
     * @param $view
     * @return string
     */
    /**
     * @param $view
     * @return string
     */
    public function getView($view): string
    {
        $this->viewPath or $this->viewPath = __DIR__ . '/views';
        return $this->viewPath . '/' . $view . '.php';
    }
}
