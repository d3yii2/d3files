<?php
use yii\db\Migration;

class m160714_080102_create_table_d3files_model extends Migration
{

	/**
	 * Create table
	 */
	public function up()
	{

		$this->execute("
            CREATE TABLE `d3files_model`(  
             `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
             `d3files_id` INT UNSIGNED NOT NULL,
             `model_name` VARCHAR(50),
             `model_id` BIGINT,
             `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
             PRIMARY KEY (`id`),
             FOREIGN KEY (`d3files_id`) REFERENCES `d3files`(`id`)
           ) ENGINE=INNODB CHARSET=utf8;
           
            ALTER TABLE `d3files`   
              DROP COLUMN `model_name`, 
              DROP COLUMN `model_id`, 
              DROP INDEX `model_name`;       
              
            ALTER TABLE `d3files_model`   
              ADD COLUMN `is_file` TINYINT(1) UNSIGNED DEFAULT 1  NOT NULL AFTER `d3files_id`,
              ADD INDEX (`model_id`,`model_name`(4) );

        ");
        
	}

	/**
	 * Drop table
	 */
	public function down()
	{
		$this->dropTable('d3files_model');
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
