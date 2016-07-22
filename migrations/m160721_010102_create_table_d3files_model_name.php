<?php
use yii\db\Migration;

class m160721_010102_create_table_d3files_model_name extends Migration
{

    /**
     * Create table
     */
    public function up()
    {

        $this->execute("
            CREATE TABLE `d3files_model_name` (
             `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `name` VARCHAR(200) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL
            ) ENGINE = InnoDB;
            ALTER TABLE `d3files_model_name` ADD UNIQUE (`name`);
        ");

    }

    /**
     * Drop table
     */
    public function down()
    {
        $this->dropTable('d3files_model_name');
    }

    /**
     * Create table in a transaction-safe way.
     * Uses $this->up to not duplicate code.
     */
    public function safeUp()
    {
        $this->up();
    }

    /**
     * Drop table in a transaction-safe way.
     * Uses $this->down to not duplicate code.
     */
    public function safeDown()
    {
        $this->down();
    }
}
