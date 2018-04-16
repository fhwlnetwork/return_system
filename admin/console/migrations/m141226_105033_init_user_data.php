<?php

use yii\db\Schema;
use yii\db\Migration;

class m141226_105033_init_user_data extends Migration
{
    public function up()
    {
        $this->batchInsert('{{%user}}', ['username', 'auth_key', 'password_hash', 'email', 'status', 'created_at', 'updated_at'], [
            ['srun', 'wgsmx0m7uSQ98aBEJ8W-OBowbwqozctq', '$2y$13$2xW..AD7LnU6vG7mZm6sneWuQw5x5P8vu7eE30JS/NVWpI3/JroEG', 'liwenyu66@126.com', '10', '1423536014', '1423536014']
        ]);
    }

    public function down()
    {
        echo "m141226_105033_init_user_data cannot be reverted.\n";

        return false;
    }
}
