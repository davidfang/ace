<?php

use yii\db\Schema;
use yii\db\Migration;

class m150720_065701_admin_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%admin_user}}', [
            'id' => Schema::TYPE_PK,
            'fromusername' => Schema::TYPE_STRING . '  NULL',
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',

            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'userphoto' => Schema::TYPE_STRING . '(64)  NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);
        $Security = new \yii\base\Security();
        $pw1 = $Security->generatePasswordHash('admin');
        $auth_key1 = $Security->generateRandomString();
        $pw2 = $Security->generatePasswordHash('demo');
        $auth_key2 = $Security->generateRandomString();
        $time = time();
        $sql = "INSERT INTO {{%admin_user}} (`id`, `username`, `password_hash`,`auth_key`,`email`,`created_at`,`updated_at`) VALUES
(1, 'admin', '$pw1','$auth_key1','example@abc.com',$time,$time),
(2, 'demo', '$pw2','$auth_key2','example@abc.com',$time,$time);";
        $this->execute($sql);
    }

    public function down()
    {
        //echo "m150720_065701_admin_user cannot be reverted.\n";
        $this->dropTable('{{%admin_user}}');
        return false;
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
