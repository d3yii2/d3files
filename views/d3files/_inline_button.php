<?php

use yii\helpers\Html;

/**
 * @var string $icon
 * @var array $previewAttrs
 */
if(!isset($previewAttrs)){
    $previewAttrs = [];
}
echo Html::a('<span class="' . $icon . '"></span>', 'javascript:void(0)', $previewAttrs);
