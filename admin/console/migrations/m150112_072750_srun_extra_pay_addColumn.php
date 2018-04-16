<?php

use yii\db\Schema;
use yii\db\Migration;

class m150112_072750_srun_extra_pay_addColumn extends Migration
{
    public function up()
    {
        $this->addColumn('{{%extra_pay}}','is_must','boolean not null default 0');
    }

    public function down()
    {
        echo "m150112_072750_srun_extra_pay_addColumn cannot be reverted.\n";

        return false;
    }
}
