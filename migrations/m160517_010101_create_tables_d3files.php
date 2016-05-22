<?php
use yii\db\Migration;

class m160517_010101_create_tables_d3files extends Migration
{

	/**
	 * Creates initial version of the table
	 */
	public function up()
	{

		$this->execute("
            CREATE TABLE `d3files` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `type_id` smallint(5) unsigned DEFAULT NULL,
              `file_name` varchar(255) NOT NULL,
              `add_datetime` datetime NOT NULL,
              `user_id` int(11) NOT NULL,
              `deleted` tinyint(1) NOT NULL DEFAULT '0',
              `notes` text,
              `model_name` varchar(50) CHARACTER SET ascii NOT NULL,
              `model_id` bigint(20) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `model_name` (`model_name`,`model_id`),
              KEY `type_id` (`type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ");
        
	}

	/**
	 * Drops the table
	 */
	public function down()
	{
        $this->dropTable('d3files');
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
