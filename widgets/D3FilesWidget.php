<?php

namespace d3yii2\d3files\widgets;

use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\widget\ThButton;
use eaBlankonThema\widget\ThModal;
use eaBlankonThema\yii2\web\BlankonView;
use Exception;
use Yii;
use yii\base\Event;
use yii\base\Widget;
use d3yii2\d3files\D3Files;
use d3yii2\d3files\models\D3files as ModelD3Files;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Class`D3FilesWidget`
 * @package d3yii2\d3files\widgets
 */
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

    /** @deprecated $viewByFancyBox */
    public $viewByFancyBox = false;

    public $modalPreview = true;

    public $template = self::TEMPLATE_FILES;

    /** @deprecated $viewByFancyBoxExtensions */
    public $viewByFancyBoxExtensions = ['pdf','jpg','jpeg','png','txt','html'];

    public $viewByExtensions = ['pdf','jpg','jpeg','png','txt','html'];

    /** @var  array */
    public $fileList;

    /** @var callable implented only in ea\eablankonthema\d3files_views\d3files\files_readonly.php */
    public $actionColumn;

    /** @var string */
    public $urlPrefix = '/d3files/d3files/';

    public $dialogWidgetClass = 'eaBlankonThema\widget\ThModal';
    public $pdfObjectOptions = [];

    public const TEMPLATE_DROPDOWN_LIST = 'dropdown-list';
    public const TEMPLATE_FILES = 'files';

    /**
     * @throws \yii\db\Exception
     */
    public function init(): void
    {
        D3Files::registerTranslations();

        if(property_exists($this->model,'d3filesControllerRoute')){
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }

        // Disabled controller actions, remove url prefix
        if (Yii::$app->getModule('d3files')->disableController) {
            $this->urlPrefix = $this->controllerRoute;
        }

        $this->model_name = $this->model::className();

        if (!$this->fileList) {
            $this->fileList = ModelD3Files::fileListForWidget($this->model_name, $this->model_id);
        }

        //@FIXME - backward compatibility
        if ($this->viewByFancyBox) {
            $this->modalPreview = true;
        }

        //@FIXME - backward compatibility
        if ($this->viewByFancyBoxExtensions) {
            $this->viewByExtensions = $this->viewByFancyBoxExtensions;
        }

        if ($this->modalPreview) {

            $hasPdf = false;
            $hasAjax = false;

            foreach ($this->fileList as $file) {
                $ext = self::getFileExtension($file);

                if (!in_array($ext, $this->viewByExtensions, true)) {
                    continue;
                }

                if ('pdf' === $ext) {
                    $hasPdf = true;
                } else {
                    $hasAjax = true;
                }
            }

            Yii::$app->view->on(BlankonView::EVENT_BEGIN_PAGE, function ($event) use ($hasAjax) {

                D3FilesPreviewAsset::register(Yii::$app->view);

                if ($hasAjax) {
                    \eaBlankonThema\assetbundles\AjaxAsset::register(Yii::$app->view);
                }

                //@FIXME - this can block any other attached events?
                 $event->handled = true;
            });

            if ($hasPdf) {

                Yii::$app->view->on(BlankonView::EVENT_END_BODY, function ($event) {

                 if (empty($this->pdfObjectOptions['targetElementClass'])) {

                        $r = new \ReflectionClass($this->dialogWidgetClass);
                        $modalContentClass = $r->getConstant('MODAL_CONTENT_CLASS');

                        if (null !== $modalContentClass) {
                            $this->pdfObjectOptions['targetElementClass'] = $modalContentClass;
                        }
                    }

                    echo \d3yii2\pdfobject\widgets\PDFObject::widget($this->pdfObjectOptions);

                    //@FIXME - this can block any other attached events?
                    //$event->handled = true;
                });
            }

            $modalOptions = [];

            // Make modal 80% height of the page
            $modalOptions['dialogHtmlOptions'] = ['style' => 'height:80%'];

            $modalOptions['toolbarContent'] = $this->getPrevNextFileButtons();

            $this->dialogWidgetClass::widget($modalOptions);
        }
    }

    /**
     * @return string|void
     */
    public function run()
    {
        
        if ($this->title === null) {
            $this->title = Yii::t('d3files', 'Attachments');
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
                'urlPrefix' => $this->urlPrefix,
                'modalPreview' => $this->modalPreview,
                'viewByExtensions' => $this->viewByExtensions,
                'actionColumn' => $this->actionColumn,
                'readOnly' => $this->readOnly,
            ]
        );
    }

    /**
     * @return string
     */
    public function getViewPath(): string
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
     * @param string $attachmentUrl
     * @param array $file
     * @param string $modalSelector
     * @param string $modalContentSelector
     * @return array
     */
    public static function getModalLoadAttributes(string $attachmentUrl, array $file, string $modalSelector, string $modalContentSelector): array
    {
        $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

        $attrs = [
            'data-toggle' => 'modal',
            'data-src' => $attachmentUrl,
            'data-target' => $modalSelector,
            'data-content-target' => $modalContentSelector,
        ];

        return $attrs;
    }

     /**
     * Get file extension
     * @param array $file
     * @return string
     */
    public static function getFileExtension(array $file): string
    {
        $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

        return $ext;
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
            $fileExtension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
            if ($extension === $fileExtension) {
                return $file;
            }
        }
        return null;
    }

    /**
     * Filter the files by extension
     * @param array $files
     * @param string $ext
     * @return array
     */
    public static function getFilesListByExt(array $files, string $ext): array {
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
     * @param array|null $currentFile
     * @return string
     * @throws Exception
     */
    public function getPrevNextFileButtons(?array $fileList = [], ?array $currentFile = null): string
    {

        $attrs = [
            //'data-target' => '#' . ThModal::MODAL_ID,
            //'data-content-target' => ThModal::MODAL_CONTENT_CLASS,
            'type' => ThButton::TYPE_SUCCESS,
            'label' => Yii::t('d3files', 'Previous Attachment'),
            'htmlOptions' => ['class' => 'd3files-preview-prev-button']
        ];

        //@FIXME - ThButton nevar padot css klasi (htmlOptions tiek pārrakstīts)
        //$buttons = ThButton::widget($attrs);
        $buttons = '<a id="w80" class="btn btn-success d3files-preview-prev-button">' . Yii::t('d3files', 'Previous Attachment') . '</a>';

        $attrs['label'] = Yii::t('d3files', 'Next Attachment');
        $attrs['htmlOptions']['class'] = 'd3files-preview-next-button';

        $buttons .= '<a id="w80" class="btn btn-success d3files-preview-next-button">' . Yii::t('d3files', 'Next Attachment') . '</a>';
        //$buttons .= ' ' . ThButton::widget($attrs);

        return $buttons;
    }
}
