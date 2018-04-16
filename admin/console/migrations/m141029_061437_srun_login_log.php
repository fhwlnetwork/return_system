<?php

use yii\db\Schema;
use yii\db\Migration;

class m141029_061437_srun_login_log extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%srun_login_log}}', [
            'id' => Schema::TYPE_BIGPK,
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_ip' => Schema::TYPE_STRING . '(64) NOT NULL',
            'nas_ip' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_mac' => Schema::TYPE_STRING . '(64) NOT NULL',
            'nas_port_id' => Schema::TYPE_STRING . '(64) NOT NULL',
            'err_msg' => Schema::TYPE_STRING . '(256) NOT NULL',
            'log_time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
        ], $tableOptions);
        $this->createIndex('index', '{{%srun_login_log}}', 'user_name', 'user_ip', 'user_mac', 'nas_port_id', 'err_msg', 'log_time');
    }

    public function down()
    {
        $this->dropTable('{{%srun_login_log}}');
    }
}
