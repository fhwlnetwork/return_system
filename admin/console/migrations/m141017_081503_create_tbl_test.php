<?php

use yii\db\Schema;
use yii\db\Migration;

class m141017_081503_create_tbl_test extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }
        // 创建数据表
        $this->createTable('{{%test}}', [
            'id' => Schema::TYPE_PK,
            'title' => Schema::TYPE_STRING . '(128) NOT NULL',
            'introduce' => Schema::TYPE_STRING . '(256) NOT NULL',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            'created_at' => Schema::TYPE_DATE . ' NOT NULL',
        ], $tableOptions);

        // 插入测试数据
        $this->batchInsert('{{%test}}', ['title', 'introduce', 'status', 'created_at'], [
            ['这是测试内容', '恭喜，看到此内容说明已经安装成功', 1, '2014-10-13'],
            [
                '第二代iPad Air 5大亮点：拍照媲美iPhone 5',
                '虽然比不上 iPhone 5 及后续机型采用的 in-cell 屏幕，但第二代 iPad Air 的一体化“全层压显示屏”显示效果也会有提升。最重要的是抗反射涂层的加入，会减弱 iPad 在户外使用时变成“镜子”的尴尬。官方数据是“炫光减少达 56%，再创 iPad 反光率新低。”',
                1,
                '2014-10-15'
            ],
            [
                '反传统的创业经：粮草未动，兵马先行',
                '我走进了在俄罗斯莫斯科的一间会议室，布兰森就在我前面十英尺的座位上坐着。虽然在我们周围还有100多个人，但是我却觉得我们的谈话令人感到舒适自然，就像在自家的会客厅一样。他时而微笑时而放声大笑。他的回答令人感到真实诚恳没有半点造作。',
                2,
                '2014-10-16'
            ],
            [
                '硬件之路，自由之心，Google是怎么把Nexus玩儿坏的',
                '等待美国版小米手机的 Google 粉失望了。嗯，是这样的。过去几年他们一直习惯了一款物美价廉的 Nexus 手机，价格和国内的小米相仿，但是运行的是纯净的 Android 系统。低调的外观、流畅的体验、还有 Android Design 和 Xda 各路大神加持，“逼格不知道比米粉高到哪里去了”。',
                3,
                '2014-10-17'
            ],
            [
                '顺丰创始人王卫：闯入电商的快递巨头',
                '如今，在中国一些大中城市里，随处可见顺丰嘿客的身影，但是顺丰创始人王卫却不满足于现状，仍在积极扩张嘿客的势力范围。近日，王卫与中国石化销售有限公司签订了业务合作框架协议，根据协议，顺丰嘿客将在广东中石化易捷便利店中试点运营。由此可见，王卫正在积极探索顺丰的“快递+电商”模式。',
                2,
                '2014-10-18'
            ],
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%test}}');
    }
}
