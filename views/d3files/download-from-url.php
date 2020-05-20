<?php

use d3yii2\d3files\D3FilesAsset;
use eaBlankonThema\widget\ThAlertList;
use yii\bootstrap\Html;
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
 * @var bool $hideTitle
 * @var array $_params_
 * @var string $uploadButtonPlacement
 */

D3FilesAsset::register($this);

$uploadUrl = Url::to([$urlPrefix . 'd3downloadfromurl', 'id' => $model_id]);
?>

<div id="d3files-widget-<?= $model_id ?>" class="panel d3files-widget">
    <div class="panel-heading">
        <?php if (!$hideTitle): ?>
            <div class="row">
                <div class="col-sm-12 ">
                    <div class="pull-left">
                        <h3 class="panel-title text-left">
                            <span class="<?= $icon ?>"></span>
                            <?= $title ?>
                        </h3>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($embedContent)) : ?>
            <div class="row">
                <?= $embedContent ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="panel-body no-padding">
        <?= ThAlertList::widget() ?>

        <?= Html::beginForm(
            $uploadUrl,
            'POST',
            ['class' => 'col-sm-12',]
        ) ?>

        <?= Html::hiddenInput(
            'model_name',
            $model_name
        ) ?>

        <?= Html::textInput(
            'url',
            null,
            [
                'class' => 'form-group',
                'style' => 'width:100%;margin-top: 10px;'
            ]
        ) ?>

        <div class="col-md-12 pull-right">
            <?= \yii\helpers\Html::submitButton(
                '<span class="glyphicon glyphicon-check"></span> ' .
                Yii::t('d3lietvediba', 'Save'),

                [
                    'class' => 'btn btn-success pull-right',
                    'style' => 'margin-bottom: 10px;'

                ]
            )
            ?>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>
