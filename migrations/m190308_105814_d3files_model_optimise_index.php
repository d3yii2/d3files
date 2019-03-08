<?php

use yii\db\Migration;

/**
* Class m190308_105814_d3files_model_optimise_index*/
class m190308_105814_d3files_model_optimise_index extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    {
        $this->execute('
            ALTER TABLE `d3files_model`   
              DROP INDEX `model_id`,
              ADD  INDEX `model_id` (`model_id`, `model_name_id`, `deleted`),
              DROP INDEX `model_name_id`,
              ADD  INDEX `model_name_id` (`model_name_id`, `d3files_id`, `deleted`);

        ');
    }

    public function safeDown()
    {
        echo "m190308_105814_d3files_model_optimise_index cannot be reverted.\n";
        return false;
    }

}