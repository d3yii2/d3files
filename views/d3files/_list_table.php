<?php
/**
 * @var bool $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 * @var $actionColumn
 * @var string $icon
 * @var string $urlPrefix
 * @var bool $readOnly
 * @var int $modelId
 * @var string $previewButton
 * @var array $previewButtonAttrs
 */

use yii\helpers\Html;
use yii\helpers\Url;
use \d3yii2\d3files\components\D3Files;

?><div class="table-responsive"><table class="table d3files-table"><?php
        foreach ($fileList as $file) {
            if (!D3Files::hasViewExtension([$file], $viewByExtensions)) {
                continue;
            }

            ?><tr><td class="col-xs-10"><?= Html::a(
                        '<i class="fa fa-cloud-download text-primary"></i> ' . $file['file_name'],
                        Url::to([
                            $urlPrefix . 'd3filesdownload',
                            'id' => $file['file_model_id'],
                            'model_name' => $model_name
                        ]),
                        [
                            'data-title' => Yii::t('d3files', 'Download'),
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                            'class' => 'text-primary',
                        ])
                ?></td><td class="col-xs-1"><?php
                    if (isset($previewButton)) {
                        echo $this->render($previewButton, ['icon' => $icon, 'file' => $file, 'fileList' => $fileList]);
                     }
                if ($actionColumn && is_callable($actionColumn)) {
                    ?><?$actionColumn($file)?><?php
                }
                ?></td><td class="text-center col-xs-1"><?php
                    if (!$readOnly) {
                        echo Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            [
                                $urlPrefix . 'd3filesdelete',
                                'id' => $file['file_model_id'],
                                'model_name' => $model_name
                            ],
                            [
                                'data-title' => Yii::t('d3files', 'Delete'),
                                'data-placement' => 'top',
                                'data-toggle' => 'tooltip',
                                'class' => 'd3files-delete text-primary',
                            ]
                        );
                    }
                 ?></td></tr><?php
        }
        ?></table></div>

