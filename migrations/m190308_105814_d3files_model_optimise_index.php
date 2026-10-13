<?php
declare(strict_types=1);

use yii\db\Migration;

/**
* Class m190308_105814_d3files_model_optimise_index*/
class m190308_105814_d3files_model_optimise_index extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp(): void
    {
        $this->execute('
            ALTER TABLE `d3files_model`   
              DROP INDEX `model_id`,
              ADD  INDEX `model_id` (`model_id`, `model_name_id`, `deleted`),
              DROP INDEX `model_name_id`,
              ADD  INDEX `model_name_id` (`model_name_id`, `d3files_id`, `deleted`);

        ');
    }

    /**
     * @return false
     */
    public function safeDown(): bool
    {
        echo "m190308_105814_d3files_model_optimise_index cannot be reverted.\n";
        return false;
    }

}