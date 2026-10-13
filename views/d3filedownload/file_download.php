<?php
declare(strict_types=1);

use yii\helpers\Html;
use yii\bootstrap\Modal;

/**
 * @var string $fileName
 * @var string $downloadUrl
 * @var int $fileModelId
 */

$imageExtensions = Yii::$app->getModule('d3files')->imageExtensions ?? [];
$fileExtension   = pathinfo($fileName, PATHINFO_EXTENSION);

if (in_array($fileExtension, $imageExtensions, true)) {
    Modal::begin(['id' => 'modal-image', 'size' => 'modal-lg']);
    echo Html::img(
        [$downloadUrl, 'id' => $fileModelId],
        ['style' => 'max-width: 100%; max-height: 100%']
    );
    Modal::end();

    echo Html::a(
        Html::img(
            [$downloadUrl, 'id' => $fileModelId],
            ['style' => 'width: 100px;',]
        ),
        '#',
        ['data-toggle' => 'modal', 'data-target' => '#modal-image']
    );
} else {
    echo Html::a(
        Yii::t('d3files', 'Download') . ' ' . $fileName,
        [$downloadUrl, 'id' => $fileModelId],
        ['title' => Yii::t('d3files', 'Download')]
    );
}
