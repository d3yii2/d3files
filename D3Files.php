<?php
namespace d3yii2\d3files;

use Yii;
use yii\base\Module;
use yii\i18n\PhpMessageSource;

/**
 * d3files module definition class
 */
class D3Files extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'd3yii2\d3files\controllers';
    
    /** @var string|callable upload directory */
    public $uploadDir;
    public $fileTypes;
    public $disableController;
    public $hashSalt;
    public $sharedExpireDays;
    public $sharedLeftLoadings;
    public $imageExtensions = ['jpg', 'gif', 'png', 'bmp'];
    public $models = [];
    public $enableNotes;

    public function init()
    {
        parent::init();
        self::registerTranslations();
    }
    
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

    public function getView($view)
    {
        $this->viewPath or $this->viewPath = __DIR__ . '/views';
        return $this->viewPath . '/' . $view . '.php';
    }
}
