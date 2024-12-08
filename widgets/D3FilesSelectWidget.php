<?php

namespace d3yii2\d3files\widgets;

use d3system\exceptions\D3Exception;
use d3yii2\d3files\D3FilesPreviewAsset;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\pdfobject\widgets\PDFObject;
use eaBlankonThema\assetbundles\AjaxAsset;
use Exception;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;
use d3yii2\d3files\components\D3Files;
use yii\web\View;
use yii\widgets\ActiveForm;
use function is_callable;

/**
 * Class D3FilesSelectWidget
 * @package d3yii2\d3files\widgets
 */
class D3FilesSelectWidget extends D3FilesWidget
{
    public ?ActiveForm $form = null;
    

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();
        $this->view = '_checkbox-list-table';
    }

    /**
     * @return array|null
     */
    public function getViewParams(): ?array
    {
        $params = [
            'fileList' => $this->fileList,
            'form' => $this->form,
        ];
        return array_merge(
            parent::getViewParams(),
            $params
        );
    }
}
