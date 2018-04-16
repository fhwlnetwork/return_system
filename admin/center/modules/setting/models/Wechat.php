<?php
namespace center\modules\setting\models;

use Yii;
use yii\base\Model;

/**
 *  wechatform
 */
class Wechat extends Model
{
    public $appid;
    public $key;
    public $mchid;
    public $appsecret;
    public $mode;
    public $body =0;
    public $notify_url;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['appid', 'key', 'mchid', 'appsecret','mode','body','notify_url'], 'required'],
        ];
    }


    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'appid' => Yii::t('app', 'APPID'),
            'mchid' => Yii::t('app', 'wechat001'),
            'key' => Yii::t('app', 'wechat002'),
            'appsecret' => Yii::t('app', 'wechat003'),
            'mode' => Yii::t('app', 'wechat004'),
            'body' => Yii::t('app', 'wechat005'),
            'notify_url' => Yii::t('app', 'wechat006'),
        ];
    }

    public function getAttributeList(){
        return [
            'subject_mode'=>[
                '0' => Yii::t('app', 'T50010'),
                '1' => Yii::t('app', 'T50011'),
                '2' => Yii::t('app', 'T50012'),
            ]
        ];
    }


}