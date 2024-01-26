<?php

use yii\db\Migration;
use yii2d3\d3persons\models\Profile;

/**
 * Class m240126_212800_create_d3files_type_avatar
 */
class m240126_212800_create_d3files_type_avatar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('d3files_type', ['id' => 1, 'model_name' => Profile::class, 'type' => 'avatar']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240126_212800_create_d3files_type_avatar cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240126_212800_create_d3files_type_avatar cannot be reverted.\n";

        return false;
    }
    */
}
