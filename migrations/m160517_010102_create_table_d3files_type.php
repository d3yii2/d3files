<?php
use yii\db\Migration;

class m160517_010102_create_table_d3files_type extends Migration
{

	/**
	 * Create table
	 */
	public function up()
	{

		$this->execute("
            CREATE TABLE `d3files_type` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `type` varchar(50) NOT NULL,
              `model_name` varchar(50) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;
        ");
        
	}

	/**
	 * Drop table
	 */
	public function down()
	{
		$this->dropTable('d3files_type');
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
