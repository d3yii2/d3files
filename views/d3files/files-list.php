<?php

use d3yii2\d3files\widgets\D3FilesWidget;
use d3yii2\pdfobject\widgets\PDFObject;
use yii\helpers\Url;

/**
 * @var bool $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 * @var  $actionColumn
 * @var string $icon
 * @var string $urlPrefix
 * @var int $model_id
 * @var int $model_name
 * @var bool $readOnly
 */

\d3yii2\d3files\D3FilesAsset::register($this);

$uploadUrl = Url::to([$urlPrefix . 'd3filesupload', 'id' => $model_id]);
?>

<div class="panel d3files-widget">
    <div class="panel-heading">
    <?php
    if (!$hideTitle) {
        ?>
         <div class="row">
             <div class="col-sm-12 ">
                 <div class="pull-left">
                    <h3 class="panel-title text-left">
                        <span class="<?php echo $icon; ?>"></span>
                        <?= $title; ?>
                    </h3>
                </div>
                <?php
                if (!$readOnly): ?>
                <div class="pull-right" data-toggle="tooltip" data-title="<?php echo Yii::t('d3files', 'Upload file'); ?>">
                    <label class="d3files-upload-btn btn btn-success btn-xs" data-title="<?php echo Yii::t('d3files', 'Upload file'); ?>">
                        <input type="file" class="d3file-input" style="display: none;" data-url="<?php echo $uploadUrl; ?>"
                               name="<?php echo $model_name; ?>"/>
                        <span class="glyphicon glyphicon-plus align-middle" style="cursor: pointer;"></span>
                    </label>
                </div>
                <?php
                endif; ?>
             </div>
         </div>
        <?php
    }
    if (!$readOnly): ?>
        <div class="row">
            <div class="col-sm-12 d3files-drop-zone"
                 title="<?php echo Yii::t('d3files', 'Drag&Drop a file here, upload will start automatically'); ?>"
                 style="border: 2px dashed #bbb; color: #bbb; text-align: center; padding: 8px;">
                <span class="glyphicon glyphicon-cloud-upload"></span>
                <?php echo Yii::t('d3files', 'Drag&Drop file here'); ?>
            </div>
        </div>
    <?php
    endif;
    if (isset($embedContent)): ?>
         <div class="row">
            <?= $embedContent ?>
         </div>
    <?php
     endif;
     ?>
    </div>
    <div class="panel-body no-padding">
        <?= $this->render('_list_table', $_params_) ?>
    </div>
</div>
<?php
$i18n = \yii\helpers\Json::encode([
    'aria_label' => Yii::t('d3files', 'Close'),
    'confirm' => Yii::t('d3files', 'Are you sure you want to delete this item?'),
    'no_results' => Yii::t('d3files', 'No results found.'),
]);
$this->registerJs('var JSVars = {i18n: ' . $i18n . '};')
?>