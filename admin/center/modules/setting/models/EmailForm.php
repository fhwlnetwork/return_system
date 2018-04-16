<?php
namespace center\modules\setting\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class EmailForm extends Model
{
    public $host;
    public $port;
    public $nickname;
    public $username;
    public $password;
    public $encryption = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['host', 'port', 'username', 'password', 'encryption'], 'required'],
            [['host', 'password', 'nickname'], 'safe'],
            ['username', 'email'],
            [['port', 'encryption'], 'integer'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'host' => Yii::t('app', 'T40001'),
            'port' => Yii::t('app', 'T40002'),
            'username' => Yii::t('app', 'T40003'),
            'password' => Yii::t('app', 'T40004'),
            'nickname' => Yii::t('app', 'T40007'),
            'encryption' => Yii::t('app', 'T40005')
        ];
    }

    /**
     * 发送邮件
     * @param $receiver_email
     * @param $subject
     * @param $content
     * @param string $type
     * @param null $attachment
     */
    public static function sendEmail($receiver_email, $subject, $content, $type = 'text', $attachment = null)
    {
        try {
            $send = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['nickName']])
                ->setTo($receiver_email)
                ->setSubject($subject);
            if ($type == 'text') {
                $send->setTextBody($content);
            }
            if ($type == 'html') {
                $send->setHtmlBody($content);
            }
            if (!is_null($attachment)) {
                $send->attach($attachment);
            }
            $send->send();

            return true;
        } catch (\Exception $e) {
            echo $e->getMessage()."\r\n";
            return false;
        }

    }
}