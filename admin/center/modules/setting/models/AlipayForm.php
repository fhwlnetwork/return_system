<?php
namespace center\modules\setting\models;

use Yii;
use yii\base\Model;

/**
 * Alipay form
 */
class AlipayForm extends Model
{
    public $seller_id; //卖家支付宝用户号
    public $key; //安全检验码，以数字和字母组成的32位字符
    public $alipay_key; //支付宝公钥
    public $private_key; //私钥
    public $seller_email; //卖家支付宝账号
    public $subject; //商品名称/订单名称
    public $self_service_notify_url; //自服务回调地址
    public $app_notify_url; //app 回调地址
    public $subject_mode = 0; //订单名称设置模式
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['seller_id', 'key', 'private_key', 'seller_email', 'self_service_notify_url', 'app_notify_url', 'subject_mode'], 'required'],
            ['seller_id', 'match', 'pattern' => '/^2088[0-9]{12}$/'],
            ['key', 'match', 'pattern' => '/^[a-zA-Z0-9]{32}$/'],
            //支付宝公钥
            [['alipay_key'], 'file', 'extensions' => ['pem']],
            [['private_key', 'subject_mode', 'subject'], 'string'],
            ['seller_email', 'email'],
            [['self_service_notify_url', 'app_notify_url'], 'url', 'defaultScheme' => 'http'],
            [['subject_mode'], 'checkSubject'],
        ];
    }

    public function checkSubject($attributes, $params)
    {
        if ($this->subject_mode == 0 && empty($this->subject)) {
            $this->addError($attributes, Yii::t('app', 'T50013'));
        }
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'seller_id' => Yii::t('app', 'T50001'),
            'key' => Yii::t('app', 'T50002'),
            'alipay_key' => Yii::t('app', 'T50008'),
            'seller_email' => Yii::t('app', 'T50003'),
            'subject' => Yii::t('app', 'T50004'),
            'self_service_notify_url' => Yii::t('app', 'T50005'),
            'app_notify_url' => Yii::t('app', 'T50006'),
            'private_key' => Yii::t('app', 'T50007'),
            'subject_mode' => Yii::t('app', 'T50009'),
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

    public function uploadFile()
    {
        if ($this->alipay_key) {
            @chmod("/srun3/www/srun4-api/rest/alipay/key/", 0777);
            $this->alipay_key->saveAs('/srun3/www/srun4-api/rest/alipay/key/alipay_public_key.' . $this->alipay_key->extension);
            $this->alipay_key = 'alipay_public_key.' . $this->alipay_key->extension;
        }
    }
}