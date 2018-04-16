<?php

use yii\db\Schema;
use yii\db\Migration;

class m150107_095429_srun_tbl_users extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }

        if ($this->db->createCommand("SHOW TABLES LIKE 'users'")->queryAll()) {
            $this->down();
        }

        // 创建数据表
        $this->createTable('{{%users}}', [
            'user_id' => Schema::TYPE_PK,
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_real_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'balance' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0.00',
            'billing_check_day' => Schema::TYPE_INTEGER . ' NOT NULL',
            'group_id' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'user_phone' => Schema::TYPE_STRING . '(15) NOT NULL',
            'user_email' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_address' => Schema::TYPE_STRING . '(128) NOT NULL',
            'user_cert_type' => Schema::TYPE_SMALLINT . ' NOT NULL',
            'user_cert_num' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_gender' => Schema::TYPE_SMALLINT . ' NOT NULL',
            'user_desc' => Schema::TYPE_STRING . '(128) NOT NULL',
            'user_create_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'user_update_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'user_expire_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'billing_start_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'billing_pause_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            //'FOREIGN KEY (user_id) REFERENCES srun_jiegou_data (user_id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        // user_name为唯一索引
        $this->createIndex('user_name', '{{%users}}', 'user_name', true);
        // 以下为普通索引
        $this->createIndex('user_real_name', '{{%users}}', 'user_real_name');
        $this->createIndex('user_status', '{{%users}}', 'user_status');
        $this->createIndex('group_id', '{{%users}}', 'group_id');
        $this->createIndex('user_phone', '{{%users}}', 'user_phone');
        $this->createIndex('user_cert_num', '{{%users}}', 'user_cert_num');
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
