<?php
namespace d3yii2\d3files\components;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Class D3FilesAction
 * @package d3yii2\d3files\components
 */
class D3FilesAction extends Action
{

    /**
     * @var string|string[] parent model name (with namespace)
     * $_POST['model_name'] is used if controller actions are not disabled
     */
    public $modelName;

    protected const STATUS = 'status';
    protected const MESSAGE = 'msg';
    protected const STATUS_ERROR = 'error';
    protected const STATUS_SUCCESS = 'success';

    public function init(): void
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
}
