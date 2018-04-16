<?php

use yii\db\Schema;
use yii\db\Migration;

class m150126_023337_checkout_list extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%checkout_list}}', [
            'id' => Schema::TYPE_PK,
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'spend_num' => Schema::TYPE_FLOAT . '(8,2) NOT NULL DEFAULT 0.00',
            'product_id' => Schema::TYPE_SMALLINT.' NOT NULL',
            'buy_id' => Schema::TYPE_SMALLINT.' NOT NULL',
            'flux' => Schema::TYPE_FLOAT.'(12,2) NOT NULL',
            'minutes' => Schema::TYPE_BIGINT.' NOT NULL',
            'sum_times' => Schema::TYPE_INTEGER.' NOT NULL',
            'create_at' => Schema::TYPE_INTEGER.' NOT NULL',
            'remark' => Schema::TYPE_STRING.'(255) NOT NULL',
            'FOREIGN KEY (id) REFERENCES checkout_list (id) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        // 以下为普通索引
        $this->createIndex('index', '{{%checkout_list}}', ['user_name','product_id']);
    }

    public function down()
    {
        echo "m150126_023337_checkout_list cannot be reverted.\n";

        return false;
    }
}
