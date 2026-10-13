<?php
declare(strict_types=1);

namespace d3yii2\d3files\widgets;

use Exception;
use yii\widgets\ActiveForm;

/**
 * Class D3FilesSelectWidget
 * @package d3yii2\d3files\widgets
 */
class D3FilesSelectWidget extends D3FilesWidget
{
    public ?ActiveForm $form = null;
    

    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();
        $this->viewFile = '_checkbox-list-table';
    }

    /**
     * @return array|null
     */
    public function getViewParams(): ?array
    {
        $params = [
            'fileList' => $this->fileList,
            'form' => $this->form,
        ];
        return array_merge(
            parent::getViewParams(),
            $params
        );
    }
}
