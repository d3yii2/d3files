<?php

use eaBlankonThema\widget\ThFancyBoxLink;
use yii\helpers\Html;

/**
 * @var bool $modalPreview
 * @var array $viewByExtensions
 * @var array $fileList
 * @var  $actionColumn
 */

if($modalPreview) {
    echo newerton\fancybox3\FancyBox::widget([
        'target' => '[data-fancybox]',
        'config' => [
            'type' => 'iframe',
            'iframe' => [
                'preload' => false
            ]
        ],
    ]);
}

?>
<div class="d3files-widget">
<table class="table table-striped table-bordered" style="margin-bottom: 0; border-bottom: 0;">
    <?php
    if (!$hideTitle) {
        ?>
        <tr style="border-bottom: 0;">
            <th style="border-bottom: 0;">
                <span class="<?php echo $icon; ?>"></span>
                <?php echo $title; ?>
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
        if($actionColumn) {
            ?>
            <td class="col-xs-1">
                <?php
                echo call_user_func($actionColumn,$row);
                ?>
            </td>
            <?php
        }
        if($modalPreview) {
            $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
            ?>
            <td class="col-xs-1">
                <?php
                if(in_array($ext,$viewByExtensions, true)){
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
