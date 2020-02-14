<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var string $file_name
 * @var int $file_model_id
 * @var string $icon
 * @var string $model_name
 * @var string $previewButtonContent
 * @var array $file
 * @var array $fileList
 */

?>
<tr data-key="<?= $id ?>" class="d3files-row">
    <td class="col-xs-11">
        <?= Html::a(
            $file_name,
            ['d3filesdownload', 'id' => $file_model_id],
            ['title' => Yii::t('d3files', 'Download')]
        ) ?>
    </td>
    <?php if (isset($previewButtonContent)): ?>
    <td class="col-xs-1">
        <?= $previewButtonContent ?>
    </td>
    <?php endif; ?>
    <td class="text-center col-xs-1">
        <?= Html::a(
            '<span class="glyphicon glyphicon-trash"></span>',
            ['d3filesdelete', 'id' => $file_model_id, 'model_name' => $model_name],
            ['class' => 'd3files-delete', 'title' => Yii::t('d3files', 'Delete')]
        ) ?>
    </td>
</tr>