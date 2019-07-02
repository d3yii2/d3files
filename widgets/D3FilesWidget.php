<?php

namespace d3yii2\d3files\widgets;

use Yii;
use yii\base\Widget;
use d3yii2\d3files\D3Files;
use d3yii2\d3files\models\D3files as ModelD3Files;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class D3FilesWidget extends Widget
{
    /** @var  ActiveRecord */
    public $model;

    /** @var  string */
    public $model_name;

    /** @var  int */
    public $model_id;

    /** @var  string */
    public $title;

    /** @var  string */
    public $icon = 'glyphicon glyphicon-paperclip';

    /** @var  bool */
    public $hideTitle = false;

    /** @var  bool */
    public $readOnly;

    /** @var string file handling controller route. If empty, then use actual controller  */
    public $controllerRoute = '';

    public $viewByFancyBox = false;

    public $template = 'files';

    public $viewByFancyBoxExtensions = ['pdf','jpg','jpeg','png','txt','html'];

    /** @var  array */
    public $fileList;

    /** @var callable implented only in ea\eablankonthema\d3files_views\d3files\files_readonly.php */
    public $actionColumn;

    public function init()
    {
        parent::init();
        D3Files::registerTranslations();

        if(property_exists($this->model,'d3filesControllerRoute')){
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }
        $this->model_name = $this->model::className();

        if (!$this->fileList) {
            $this->fileList = ModelD3Files::fileListForWidget($this->model_name, $this->model_id);
        }
    }
    
    public function run()
    {
        
        if ($this->title === null) {
            $this->title = Yii::t('d3files', 'Attachments');
        }

        //url prefix (module/controller)
        $url_prefix = '/d3files/d3files/';

        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $url_prefix = $this->controllerRoute;
        }

        if ($this->readOnly) {
            return $this->render(
                $this->template . '_readonly',
                [
                    'model_name' => $this->model_name,
                    'model_id'   => $this->model_id,
                    'title'      => $this->title,
                    'icon'       => $this->icon,
                    'hideTitle'  => $this->hideTitle,
                    'fileList'   => $this->fileList,
                    'url_prefix' => $url_prefix,
                    'viewByFancyBox' => $this->viewByFancyBox,
                    'viewByFancyBoxExtensions' => $this->viewByFancyBoxExtensions,
                    'actionColumn' => $this->actionColumn
                ]
            );
        }

        return $this->render(
            $this->template,
            [
                'model_name' => $this->model_name,
                'model_id'   => $this->model_id,
                'title'      => $this->title,
                'icon'       => $this->icon,
                'hideTitle'  => $this->hideTitle,
                'fileList'   => $this->fileList,
                'url_prefix' => $url_prefix,
                'viewByFancyBox' => $this->viewByFancyBox,
                'viewByFancyBoxExtensions' => $this->viewByFancyBoxExtensions,
                'actionColumn' => $this->actionColumn
            ]
        );
        
    }
    
    public function getViewPath()
    {
        if (!$viewPath = Yii::$app->getModule('d3files')->viewPath) {
            $viewPath = dirname(__DIR__) . '/views';
        }
        return $viewPath . '/d3files/';
    }

    /**
     * Get the list of readed model files
     * @return array
     */
    public function getFileList(): array
    {
        return $this->fileList;
    }

    /**
     * Get element attributes for Modal box load script
     * @param array $attachmentUrl
     * @param array $file
     * @param string $modalSelector
     * @param string $modalContentSelector
     * @return array
     */
    public static function getModalLoadAttributes(array $attachmentUrl, array $file, string $modalSelector, string $modalContentSelector): array
    {

        $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

        $attrs = [
            'data-toggle' => 'modal',
            'data-src' => Url::to($attachmentUrl),
            'data-target' => $modalSelector,
            'data-content-target' => $modalContentSelector,
        ];

        if ('pdf' === $ext) {
            $attrs['data-load-action'] = 'pdf';
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $attrs['data-load-action'] = 'image';
        } else {
            $attrs['data-load-action'] = 'ajax';
        }

        return $attrs;
    }
}
