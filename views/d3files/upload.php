<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model d3yii2\d3files\models\D3files */

?>
<tr data-key="<?= $id; ?>" class="d3files-row">
    <td class="col-xs-11">
        <?= Html::a($file_name, ['download', 'id' => $id], [
            'title' => Yii::t('d3files', 'Download'),
        ]) ?>
    </td>
    <td class="text-center col-xs-1">
        <?= Html::a(
            '<span class="glyphicon glyphicon-trash"></span>',
            ['delete', 'id' => $file_model_id],
            ['class' => 'd3files-delete', 'title' => Yii::t('d3files', 'Delete')]
        ) ?>
    </td>
</tr>