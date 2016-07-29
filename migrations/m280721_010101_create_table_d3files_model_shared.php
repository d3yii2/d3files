<?php
use yii\db\Migration;

class m280721_010101_create_table_d3files_model_shared extends Migration
{

    /**
     * Create table
     */
    public function up()
    {

        $this->execute("
            CREATE TABLE `d3files_model_shared` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `d3files_model_id` int(10) unsigned NOT NULL,
              `expire_date` date NOT NULL,
              `left_loadings` tinyint(4) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `d3files_id` (`d3files_model_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


            ALTER TABLE `d3files_model_shared`
              ADD CONSTRAINT `d3files_model_shared_ibfk_1` FOREIGN KEY (`d3files_model_id`) REFERENCES `d3files_model` (`id`);
        ");

    }

    /**
     * Drop table
     */
    public function down()
    {
        $this->dropTable('d3files_model_shared');
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
