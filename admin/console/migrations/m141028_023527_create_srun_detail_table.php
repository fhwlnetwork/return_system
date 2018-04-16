<?php
use yii\db\Schema;
use yii\db\Migration;

class m141028_023527_create_srun_detail_table extends Migration
{
    public function up()
    {
       $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }

        $this->createTable('{{%srun_detail}}', [
            'detail_id' => Schema::TYPE_BIGPK,
            'session_id' => Schema::TYPE_STRING . '(128) NOT NULL',
            'user_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'add_time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'drop_time' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'bytes_in' => Schema::TYPE_BIGINT . '(20) NOT NULL',
            'bytes_out' => Schema::TYPE_BIGINT . '(20) NOT NULL',
            'bytes_in6' => Schema::TYPE_BIGINT . '(20) NOT NULL',
            'bytes_out6' => Schema::TYPE_BIGINT . '(20) NOT NULL',
            'user_ip' => Schema::TYPE_STRING . '(16) NOT NULL',
            'user_ip6' => Schema::TYPE_STRING . '(16) NOT NULL',
            'user_mac' => Schema::TYPE_STRING . '(20) NOT NULL',
            'nas_ip' => Schema::TYPE_STRING . '(16) NOT NULL',
            'nas_port_id' => Schema::TYPE_STRING . '(128)',
            'vlan_id' => Schema::TYPE_STRING . '(64)',
            'line_type' => Schema::TYPE_INTEGER . '(2) NOT NULL',
            'login_mode' => Schema::TYPE_INTEGER . '(2) NOT NULL',
            'nas_type' => Schema::TYPE_INTEGER . '(4) NOT NULL',
            'ip_type' => Schema::TYPE_INTEGER . '(2) NOT NULL',
            'user_id' => Schema::TYPE_BIGINT . '(20) NOT NULL',
            'products_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'billing_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'control_id' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'traffic_down_ratio' => Schema::TYPE_INTEGER . '(4) NOT NULL',
            'traffic_up_ratio' => Schema::TYPE_INTEGER . '(4) NOT NULL',
            'billing_rate' => Schema::TYPE_FLOAT . '(14,8) NOT NULL',
            'billing_units' => Schema::TYPE_STRING . '(16) NOT NULL',
            'billing_mode' => Schema::TYPE_INTEGER . '(2) NOT NULL',
            'user_balance' => Schema::TYPE_FLOAT . ' (8,4) NOT NULL',
            'total_bytes' => Schema::TYPE_STRING . '(20) NOT NULL',
            'time_long' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'user_charge' => Schema::TYPE_FLOAT . ' (8,4) NOT NULL',
        ], $tableOptions);

        $this->createIndex('index', '{{%srun_detail}}', 'add_time');
        $this->createIndex('user_name', '{{%srun_detail}}', 'user_name');
        $this->createIndex('user_ip', '{{%srun_detail}}', 'user_ip');
        $this->createIndex('user_mac', '{{%srun_detail}}', 'user_mac');
        $this->createIndex('products_id', '{{%srun_detail}}', 'products_id');
        $this->createIndex('billing_id', '{{%srun_detail}}', 'billing_id');
        $this->createIndex('vlan_id', '{{%srun_detail}}', 'vlan_id');
        $this->createIndex('nas_port_id', '{{%srun_detail}}', 'nas_port_id');
        $this->createIndex('ip_type', '{{%srun_detail}}', 'ip_type');
        $this->createIndex('drop_time', '{{%srun_detail}}', 'drop_time');
    }

    public function down()
    {
         $this->dropTable('{{%srun_detail}}');

    }
}
