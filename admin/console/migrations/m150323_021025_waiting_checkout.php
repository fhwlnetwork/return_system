<?php

use yii\db\Schema;
use yii\db\Migration;

class m150323_021025_waiting_checkout extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        // 创建数据表
        $this->createTable('{{%waiting_checkout}}', [
            'checkout_id' => Schema::TYPE_BIGPK,
            'user_id' => Schema::TYPE_BIGINT . ' NOT NULL DEFAULT 0',
            'products_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'checkout_date' => Schema::TYPE_INTEGER.' NOT NULL DEFAULT 0',
        ], $tableOptions);
        // 以下为普通索引
        $this->createIndex('index', '{{%waiting_checkout}}', ['user_id','products_id'], true);
    }

    public function down()
    {
        echo "m150323_021025_waiting_checkout cannot be reverted.\n";

        return false;
    }
}
