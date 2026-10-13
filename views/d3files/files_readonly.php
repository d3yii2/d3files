<?php
declare(strict_types=1);

use eaBlankonThema\widget\ThFancyBoxLink;
use yii\helpers\Html;

/**
 * @var bool $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 * @var  $actionColumn
 * @var bool $hideTitle
 * @var string  $icon
 * @var string  $title
 * @var string  $urlPrefix
 */

/*if ($viewType) {
    //Deprecated
    echo newerton\fancybox3\FancyBox::widget([
        'target' => '[data-fancybox]',
        'config' => [
            'type' => 'iframe',
            'iframe' => [
                'preload' => false
            ]
        ],
    ]);
}*/

?>
<div class="d3files-widget">
<table class="table table-striped table-bordered" style="margin-bottom: 0; border-bottom: 0;">
    <?php
    if (!$hideTitle) {
        ?>
        <tr style="border-bottom: 0;">
            <th style="border-bottom: 0;">
                <span class="<?= $icon ?>"></span>
                <?= $title ?>
            </th>
        </tr>
        <?php
    }
    ?>    
</table>
<table class="d3files-table table table-striped table-bordered">
<?php

foreach ($fileList as $row) {
    ?>
    <tr>
        <td class="col-xs-12">
            <?=Html::a(
                $row['file_name'],
                [$urlPrefix . 'd3filesdownload', 'id' => $row['file_model_id']],
                ['title' => Yii::t('d3files', 'Download')]
            ) ?>
        </td>
        <?php
        if ($actionColumn) {
            ?>
            <td class="col-xs-1">
                <?= $actionColumn($row) ?>
            </td>
            <?php
        }
        if ($viewType) {
            $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
            ?>
            <td class="col-xs-1">
                <?php
                if (in_array($ext, $viewByExtensions, true)) {
                    try {
                        echo ThFancyBoxLink::widget([
                        'text' => Yii::t('d3files', 'View'),
                        'options' => [
                            //'class' => 'unpaid-invoice-table-actions-col'
                        ],
                        'url' => [
                            $urlPrefix . 'd3filesopen',
                            'id' => $row['file_model_id']
                        ]
                        ]);
                    } catch (Exception $e) {
                        Yii::error($e->getMessage());
                        echo 'Cannot load widget';
                    }
                }
                ?>
            </td>
            <?php
        }
        ?>
    </tr>
    <?php
}
?>        
</table>    

</div>
