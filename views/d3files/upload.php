<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\vendor\dbrisinajumi\d3files\models\D3files */

?>
<tr data-key="<?= $model->id; ?>" class="d3files-row">
    <td>
        <?= Html::a($model->file_name, ['download', 'id' => $model->id], [
            'title' => 'Download',
        ]) ?>
    </td>
    <td>
        <?= Html::a(
            '<span class="glyphicon glyphicon-trash"></span>',
            ['delete', 'id' => $model->id],
            ['class' => 'd3files-delete', 'title' => 'Delete']
        ) ?>
    </td>
</tr>