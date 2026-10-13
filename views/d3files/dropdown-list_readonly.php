<?php
declare(strict_types=1);

use d3yii2\d3files\D3FilesAsset;
use d3yii2\d3files\widgets\D3FilesWidget;
use eaBlankonThema\widget\ThModal;

D3FilesAsset::register($this);
/**
 * @var string $urlPrefix
 * @var string $viewType
 * @var array $viewByExtensions
 * @var array $fileList
 */

if (empty($fileList)) {
    return;
} ?>
<label>
    <select class="form-control d3files-list th-dropdown-load">
        <option value="default" selected="selected"><?= Yii::t('d3files', 'Select attachment') ?></option>
        <?php

        foreach ($fileList as $row) {
            $ext = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
            if ($viewType && in_array($ext, $viewByExtensions, true)) {
                $fileUrl = [
                        $urlPrefix . 'd3filesopen',
                        'id' => $row['file_model_id']
                ];

                $dataAttributes = [
                    $fileUrl,
                    '#' . ThModal::MODAL_ID,
                    '.' . ThModal::MODAL_CONTENT_CLASS
                ];

                $dataAttributesStr = '';

                foreach ($dataAttributes as $key => $val) {
                    $dataAttributesStr .= ' ' . $key . '="' . $val . '"';
                } ?>
                <option value="<?= $row['file_name'] ?>"<?= $dataAttributesStr ?>><?= $row['file_name'] ?></option>
                <?php
            }
        }
        ?>
    </select>
</label>
