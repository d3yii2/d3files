<?php

namespace d3yii2\d3files\components;

use d3yii2\d3files\models\D3files;
use d3yii2\d3files\widgets\D3FilesWidget;
use yii\grid\DataColumn;
use yii\helpers\Html;
use Yii;

/**
 * Class D3FilesColumn
 * @package d3yii2\d3files\components
 * @property object $model
 * @property array $dataProviderIds
 * @property array $recordsWithLabels

 */
class D3FilesColumn extends DataColumn
{
    public $model;
    public $modelClass;
    public $listBoxOptions = [];

    private $dataProviderIds = [];
    private $recordsWithFiles = [];
    private $controllerRoute = false;

    /**
     * Set the initial properties on class init
     */
    public function init(): void
    {
        $this->initFiles();

        if(property_exists($this->model,'d3filesControllerRoute')) {
            $this->controllerRoute = $this->model->d3filesControllerRoute;
        }

        $this->listBoxOptions = [
            'class' => 'form-control limiter-max__250',
            'prompt' => \Yii::t('d3files', 'Filter by Attachment')
        ];

        parent::init();
    }

    /**
     * Read all the records containing attachments into $this->recordsWithFiles array
     */
    private function initFiles(): void
    {
        $rows = $this->grid->dataProvider->getModels();

        foreach ($rows as $row) {
            $this->dataProviderIds[] = $row->id;
        }

        $model = $this->model;

        $recordsWithFiles = D3files::getAllByModelRecordIds($this->modelClass, $this->dataProviderIds);

        foreach ($recordsWithFiles as $fileModel) {
            if (!isset($this->recordsWithFiles[$fileModel['model_id']])) {
                $this->recordsWithFiles[$fileModel['model_id']] = [];
            }

            $this->recordsWithFiles[$fileModel['model_id']][$fileModel['id']] = $fileModel;
        }
    }

    /**
     * Render the labels inside grid data cell
     * @param $model
     * @param $key
     * @param $index
     * @return string
     * @throws \Exception
     */
    public function renderDataCellContent($model, $key, $index): string
    {
        if (empty($this->recordsWithFiles[$model->id])) {
            //return '';
        }

        $search = Yii::$app->request->get('RkInvoiceSearch');

        $files = !empty($model->filesList) ? json_decode($model->filesList, true) : [];

        $filesCount = count($files);

        $params = [
            'model' => $this->modelClass,
            'model_id' => $model->id,
            'readOnly' => true,
            'viewByFancyBox' => true,
            'template' => $filesCount > 1 ? 'dropdown-list' : 'list',
            'fileList' => $files,
        ];

        if (!empty($search['attachment_type'])) {
            $params['viewByFancyBoxExtensions'] = [$search['attachment_type']];
        }

        $filesList = D3FilesWidget::widget($params);

        return $filesList;
    }

    /**
     * Renders the filter cell content.
     * The default implementation simply renders a space.
     * This method may be overridden to customize the rendering of the filter cell (if any).
     * @return string
     * @throws \yii\db\Exception
     */
    protected function renderFilterCellContent(): string
    {
        $items = D3files::forListBox($this->modelClass, $this->dataProviderIds);

        $dropdown = Html::activeDropDownList(
            $this->model,
            'attachment_type',
            $items,
            $this->listBoxOptions
        );

        return $dropdown;
    }
}