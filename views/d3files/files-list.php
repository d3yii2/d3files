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

$t_aria_label = Yii::t('d3files', 'Close');
$t_confirm = Yii::t('d3files', 'Are you sure you want to delete this item?');
$t_no_results = Yii::t('d3files', 'No results found.');

?>
<div class="panel d3files-widget">
    <?php
    if (!$hideTitle) {
        ?>

        <div class="panel-heading">
            <h3 class="panel-title text-left">
                <span class="<?php echo $icon; ?>"></span>
                <?php echo $title; ?>
                <label style="margin: 0; margin-left: 5px;" data-toggle="tooltip" data-placement="top"
                       data-title="<?php echo Yii::t('d3files', 'Upload file'); ?>">
                    <input type="file" class="d3file-input" style="display: none;" data-url="<?php echo $uploadUrl; ?>"
                           name="<?php echo $model_name; ?>"/>
                    <span class="glyphicon glyphicon-plus text-primary" style="cursor: pointer;"></span>
                </label>
            </h3>
        </div>
        <?php
    }
    ?>
    <div class="panel-heading">
        <div class="d3files-drop-zone"
             title="<?php echo Yii::t('d3files', 'Drag&Drop a file here, upload will start automatically'); ?>"
             style="border: 2px dashed #bbb; color: #bbb; text-align: center; padding: 8px;">
            <span class="glyphicon glyphicon-cloud-upload"></span>
            <?php echo Yii::t('d3files', 'Drag&Drop file here'); ?>
        </div>
        <?php
        if (isset($embedContent)) {
            echo $embedContent;
        } ?>
    </div>
    <div class="panel-body no-padding">
        <?= $this->render(
            '_list_table',
            [
                'viewType' => $viewType,
                'viewByExtensions' => $viewByExtensions,
                'fileList' => $fileList,
                'actionColumn' => $actionColumn,
                'icon' => $icon,
                'urlPrefix' => $urlPrefix,
                'readOnly' => $readOnly,
                'modelId' => $model_id,
            ]
        ) ?>
    </div>
</div>