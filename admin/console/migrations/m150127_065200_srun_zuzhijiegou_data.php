<?php

use yii\db\Schema;
use yii\db\Migration;

class m150127_065200_srun_zuzhijiegou_data extends Migration
{
    public function up()
    {
        $this->batchInsert('{{%srun_jiegou}}', ['id', 'name', 'path', 'pid', 'level', 'status'], [
            ['1', '/', '0', '0', '0', '1'],
        ]);
    }

    public function down()
    {
        //$this->dropTable('{{%srun_jiegou}}');
    }
}
