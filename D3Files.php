<?php
namespace d3yii2\d3files;

use Yii;
use yii\base\Module;
use yii\db\ActiveRecord;

/**
 * d3files module definition class
 */
class D3Files extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'd3yii2\d3files\controllers';
    
    public $uploadDir;
    public $fileTypes;
    public $disableController;
    public $hashSalt;
    public $sharedExpireDays;
    public $sharedLeftLoadings;
    public $imageExtensions = ['jpg', 'gif', 'png', 'bmp'];
    public $models = [];

    public function init()
    {
        parent::init();
        self::registerTranslations();
    }
    
    public static function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['d3files'] = [
            'class'            => 'yii\i18n\PhpMessageSource',
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

    /**
     * @param ActiveRecord $model
     * @return array
     */
    public function getModelAllowedExtensions(ActiveRecord $model): array
    {
        if (empty($this->models)) {
            return [];
        }

        foreach ($this->models as $item) {
            if ($item['class'] === $model::className()) {
                return explode('|', $item['allowedExtensions']);
            }
        }

        return [];
    }

}
