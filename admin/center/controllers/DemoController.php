<?php
namespace center\controllers;

use yii\rest\ActiveController;

class DemoController extends ActiveController
{
    public $modelClass = 'center\models\Demo';

    public function actionTest()
    {
        return 'Hello World!';
    }

    // php代理 实现get post请求
    public function actionProxy(){
        $rs = [];
        if(\Yii::$app->request->isPost){
            $url = \Yii::$app->request->post('url');
            $post_data = \Yii::$app->request->post();

            $rs = $this->post($url,$post_data);
        }elseif (\Yii::$app->request->isGet){
            $url = \Yii::$app->request->get('url');

            $rs = $this->get($url);
        }

        return $rs;
    }

    // get
    private function get($url){
        //初始化
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);

        return $output;
    }

    // post
    private function post($url,$post_data){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}