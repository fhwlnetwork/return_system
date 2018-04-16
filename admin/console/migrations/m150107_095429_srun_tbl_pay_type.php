<?php

use yii\db\Schema;
use yii\db\Migration;

class m150107_095429_srun_tbl_pay_type extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%pay_type}}', [
            'id' => Schema::TYPE_PK,
            'type_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'default' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'create_at' => Schema::TYPE_INTEGER.' NOT NULL',
            'mgr_name' => Schema::TYPE_STRING.'(64) NOT NULL',
            'FOREIGN KEY (id) REFERENCES pay_type (id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%pay_type}}');
    }
}
