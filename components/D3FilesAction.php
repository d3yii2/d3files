<?php
declare(strict_types=1);

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
     * $_POST['model_name'] is used if controller actions are not disabled
     */
    public ?string $modelName = null;

    protected const STATUS = 'status';
    protected const MESSAGE = 'msg';
    protected const STATUS_ERROR = 'error';
    protected const STATUS_SUCCESS = 'success';

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init(): void
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
    }
}
