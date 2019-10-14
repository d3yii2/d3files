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
 * @var array $previewAttrs
 */

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="table-responsive">
    <table class="table d3files-table">
        <?php
        foreach ($fileList as $file) { ?>
            <tr>
                <td class="col-xs-10">
                    <?= Html::a(
                        '<i class="fa fa-cloud-download text-primary"></i> ' . $file['file_name'],
                        Url::to([
                            $urlPrefix . 'd3filesdownload',
                            'id' => $file['file_model_id']
                        ]),
                        [
                            'data-title' => Yii::t('d3files', 'Download'),
                            'data-placement' => 'top',
                            'data-toggle' => 'tooltip',
                            'class' => 'text-primary',
                        ]
                    ) ?>
                </td>
                <td class="col-xs-1">
                    <?php
                    if (isset($file['previewAttrs'])) {
                        echo $this->render($previewButton, ['icon' => $icon, 'previewAttrs' => $file['previewAttrs']]);
                     }?>
                </td>
                <?php
                if ($actionColumn && is_callable($actionColumn)) {
                    ?>
                    <td class="col-xs-1">
                        <?php
                        echo $actionColumn($file);
                        ?>
                    </td>
                    <?php
                }
                ?>
                <td class="text-center col-xs-1">
                    <?php
                    if (!$readOnly) {
                        echo Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            [$urlPrefix . 'd3filesdelete', 'id' => $file['file_model_id']],
                            [
                                'data-title' => Yii::t('d3files', 'Delete'),
                                'data-placement' => 'top',
                                'data-toggle' => 'tooltip',
                                'class' => 'd3files-delete text-primary',
                            ]
                        );
                    } ?>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>

