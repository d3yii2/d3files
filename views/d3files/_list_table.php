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
 */

use d3yii2\d3files\widgets\D3FilesWidget;
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
                    $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                    if (in_array($ext, $viewByExtensions, true)) {

                        $attrs = [
                            'text' => Yii::t('d3files', 'Preview atachment'),
                            'file' => $file,
                            'fileList' => $fileList,
                            'urlPrefix' => $urlPrefix,
                            'viewByExtensions' => $viewByExtensions,
                            'icon' => $icon,
                            'modelId' => $modelId,
                        ];

                        $view = D3FilesWidget::VIEW_TYPE_MODAL === $viewType
                            ? D3FilesWidget::VIEW_MODAL_BUTTON
                            : D3FilesWidget::VIEW_INLINE_BUTTON;

                        echo $this->render($view, $attrs);
                    } ?>
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
