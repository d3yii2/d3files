<?php

namespace d3yii2\d3files\components;

use d3yii2\d3files\models\D3files;
use d3yii2\d3files\models\D3filesModelName;
use d3yii2\d3files\widgets\D3FilesPreviewWidget;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\grid\DataColumn;
use yii\helpers\Html;

/**
 * Class D3FilesColumn
 * @package d3yii2\d3files\components
 * @property ActiveRecord $model
 * @property string $modelClass
 * @property array $filterListBoxOptions
 * @property bool $showFilter
 * @property array $previewOptions
 * @property array $dataProviderIds
 * @property array $recordsWithLabels
 */
class D3FilesColumn extends DataColumn
{
    public $model;
    public $modelClass;
    public $nameModel;
    public $filterListBoxOptions = [];
    public $showFilter = false;
    public $previewOptions = [];
    private $dataProviderIds = [];
    private $recordsWithFiles = [];
    public $nextButtonLabel;
    public $prevButtonLabel;
    /**
     * Set the initial properties on class init
     */
    public function init(): void
    {
        $this->initFiles();

        if ($this->showFilter) {
            $this->filterListBoxOptions = array_merge(
                [
                    'class' => 'form-control limiter-max__250',
                    'prompt' => Yii::t('d3files', 'Filter by Attachment')
                ],
                $this->filterListBoxOptions
            );
        }

        parent::init();
    }

    /**
     * Read all the records containing attachments into $this->recordsWithFiles array
     */
    private function initFiles(): void
    {
        $rows = $this->grid->dataProvider->getModels();

        foreach ($rows as $row) {
            $this->dataProviderIds[] = is_array($row) ? $row['id'] : $row->id;
        }

        try {
            $recordsWithFiles = D3files::getAllByModelRecordIds($this->modelClass, $this->dataProviderIds);
            $this->nameModel = D3filesModelName::findOne(['name' => $this->modelClass]);
        } catch (Exception $exception) {
            Yii::error('D3FilesColumn::initFiles exception: ' . $exception->getMessage());
            return;
        }

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
        try {
            $modelId = is_array($model) ? $model['id'] : $model->id;
            if (empty($this->recordsWithFiles[$modelId])) {
                return '';
            }

            $modelFiles = $this->recordsWithFiles[$modelId];

            //@FIXME - old dummy dependency
            //$search = Yii::$app->request->get('RkInvoiceSearch');

            $options = array_merge(
                [
                    'model' => $model,
                    'nameModel' => $this->nameModel,
                    'fileList' => $modelFiles,
                    'showPrevNextButtons' => true,
                    'view' => D3FilesPreviewWidget::VIEW_MODAL_BUTTON,
                    'nextButtonLabel' => $this->nextButtonLabel,
                    'prevButtonLabel' => $this->prevButtonLabel,
                    'useInColumn' => true,
                ],
                $this->previewOptions
            );

            //@FIXME - old dummy dependency
            /*if (!empty($search['attachment_type'])) {
                $options['viewByExtensions'] = [$search['attachment_type']];
            }*/

            return D3FilesPreviewWidget::widget($options);
        }catch (\Exception $exception){
            Yii::error('D3FilesColumn::renderDataCellContent exception: ' . $exception->getMessage());
        }
        return '';
    }

    /**
     * Renders the filter cell content.
     * The default implementation simply renders a space.
     * This method may be overridden to customize the rendering of the filter cell (if any).
     * @return string
     * @throws Exception
     */
    protected function renderFilterCellContent(): string
    {
        if (!$this->showFilter) {
            return '';
        }

        $items = D3files::forListBox($this->modelClass, $this->dataProviderIds);

        return Html::activeDropDownList(
            $this->model,
            'attachment_type',
            $items,
            $this->filterListBoxOptions
        );
    }
}