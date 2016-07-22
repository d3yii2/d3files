<?php
use yii\db\Migration;

class m160721_010101_alter_table_d3files extends Migration
{

    /**
     * Alter table
     */
    public function up()
    {

        $this->execute("
            ALTER TABLE `d3files` DROP `deleted`;
        ");

    }

    /**
     * Drop changes
     */
    public function down()
    {
        $this->execute("
            ALTER TABLE `d3files` ADD `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'
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
