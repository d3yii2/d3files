<?php
use yii\db\Migration;

class m160721_010103_alter_table_d3files_model extends Migration
{

    /**
     * Alter table
     */
    public function up()
    {

        $this->execute("
            ALTER TABLE `d3files_model` CHANGE `model_name` `model_name_id` SMALLINT UNSIGNED NOT NULL;
            ALTER TABLE `d3files_model` ADD INDEX (`model_name_id`);
            ALTER TABLE `d3files_model` ADD FOREIGN KEY (`model_name_id`) REFERENCES `d3yii2`.`d3files_model_name` (
             `id`
            );

        ");

    }

    /**
     * Drop changes
     */
    public function down()
    {
        $this->execute("
            ALTER TABLE `d3files_model` DROP FOREIGN KEY `d3files_model_ibfk_2`;
            ALTER TABLE `d3files_model` DROP INDEX `model_name_id`;
            ALTER TABLE `d3files_model` CHANGE `model_name_id` `model_name` VARCHAR(50) CHARACTER SET armscii8 COLLATE armscii8_general_ci NOT NULL;
        ");
    }

    /**
     * Creates initial version of the table in a transaction-safe way.
     * Uses $this->up to not duplicate code.
     */
    public function safeUp()
    {
        $this->up();
    }

    /**
     * Drops the table in a transaction-safe way.
     * Uses $this->down to not duplicate code.
     */
    public function safeDown()
    {
        $this->down();
    }
}
