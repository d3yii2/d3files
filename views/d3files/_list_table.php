<?php
/**
 * @var bool $viewType
 * @var string $viewByExtensions
 * @var array $fileList
 * @var $actionColumn
 * @var string $icon
 * @var string $urlPrefix
 * @var bool $readOnly
 * @var int $modelId
 * @var string $previewButton
 * @var array $previewButtonAttrs
 * @var string $model_name
 */

use d3yii2\d3files\components\D3Files;
use yii\helpers\Html;
use yii\helpers\Url;

?><div class="table-responsive"><table class="table d3files-table"><?php
    foreach ($fileList as $file) {
        if (!D3Files::hasFileWithExtension([$file], $viewByExtensions)) {
            continue;
        }

        ?><tr><td class="col-xs-10"><?=
            Html::a(
                '<i class="fa fa-cloud-download text-primary"></i> ' . $file['file_name'],
                Url::to([
                    $urlPrefix . 'd3filesdownload',
                    'id' => $file['file_model_id'],
                    'model_name' => $model_name,
                ]),
                [
                    'data-title' => Yii::t('d3files', 'Download'),
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'class' => 'text-primary',
                ]
            ) ?></td><td class="col-xs-1"><?php

        if (isset($previewExtensions, $previewFileList) && D3Files::fileHasExtension($file, $previewExtensions)) {
            echo $this->render($previewButton, compact('icon', 'file', 'previewFileList'));
        }
        if ($actionColumn && is_callable($actionColumn)) {
            echo $actionColumn($file);
        } ?></td><td class="text-center col-xs-1"><?php
        if (!$readOnly) {
            echo Html::a(
                '<span class="glyphicon glyphicon-trash"></span>',
                [
                    $urlPrefix . 'd3filesdelete',
                    'id' => $file['file_model_id'],
                    'model_name' => $model_name,
                ],
                [
                    'data-title' => Yii::t('d3files', 'Delete'),
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'class' => 'd3files-delete text-primary',
                ]
            );
        }
        ?></td>
        <?php if (!$readOnly): ?>
            <td>
            <?= Html::a(
                '<span class="glyphicon glyphicon-book"></span>',
                '#',
                [
                    'onclick' => 'event.preventDefault()',
                    'data-title' => Yii::t('d3files', 'Edit notes'),
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'class' => 'd3files-edit-notes text-primary',
                ]
            ) ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php
        if (!$readOnly): ?>
            <tr class="d3files-row-notes" style="display:none">
                <td colspan="5">
                    <?= Html::textarea('d3files-notes', $file['notes'], ['class' => 'd3files-notes-field', 'rows' => 2, 'cols' => 2]) ?>
                    <?= Html::button(
                            Yii::t('d3files', 'Save Note'),
                            [
                                'value' => $file['notes'],
                                'class' => 'btn btn-sm btn-success d3files-save-notes',
                                'data-url' => Url::to([$urlPrefix . 'd3fileseditnotes', 'id' => $file['id']])
                            ]
                    ) ?>
                </td>
            </tr>
        <?php
        endif;
    } ?></table></div>

