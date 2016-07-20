<?php
use yii\helpers\Html;
//use yii\web\View;

$uploadUrl = Yii::$app->urlManager->createUrl(
    ['d3files/d3files/upload', 'id' => $model_id]
);

$t_no_results = Yii::t('d3files', 'No results found.');

?>
<div class="d3files-widget">
<table class="table table-striped table-bordered" style="margin-bottom: 0px; border-bottom: 0;">
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
                    ['/d3files/d3files/download', 'id' => $row['id']],
                    ['title' => Yii::t('d3files', 'Download')]
                )?>
        </td>
    </tr>
    <?php
}
?>        
</table>    

</div>
