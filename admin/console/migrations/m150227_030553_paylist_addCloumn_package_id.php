<?php

use yii\db\Schema;
use yii\db\Migration;

class m150227_030553_paylist_addCloumn_package_id extends Migration
{
    public function up()
    {
        $this->addColumn('{{%pay_list}}','package_id','int(11) not null');
    }

    public function down()
    {
        echo "m150227_030553_paylist_addCloumn_package_id cannot be reverted.\n";

        return false;
    }
}
