<?php

namespace center\modules\setting\models;

use common\models\Redis;
use yii;
use yii\db\ActiveRecord;
use center\modules\log\models\LogWriter;

/**
 * Class Sms
 * @package center\modules\setting\models
 */
class Sms extends ActiveRecord
{
    public static $keyName = 'sms';
    public $name; //sms接口名称
    public $class; //sms接口类名称
    public $setting; //设置项
    public $sms_type = 0; //默认 深澜短信  0 深澜短信平台 1与第三方平台对接
    public $sign; //签名

    private $_oldData = [];

    public static function tableName()
    {
        return 'setting';
    }

    public function rules()
    {
        return [
            [['name', 'class', 'sms_type'], 'required'],
            [['setting', 'name', 'class', 'sms_type', 'sign'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'sms_type' => Yii::t('app', 'sms type'),
            'name' => Yii::t('app', 'sms name'),
            'class' => Yii::t('app', 'sms class'),
            'setting' => Yii::t('app', 'sms setting'),
            'sign' => Yii::t('app', 'sms sign'),
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->key = self::$keyName;
        $this->value = json_encode([
            'name' => $this->name,
            'class' => $this->class,
            'sign' => $this->sign,
            'setting' => $this->setting,
            'sms_type' => $this->sms_type,
        ]);

        return parent::save($runValidation, $attributeNames);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        //写日志开始 获取脏数据
        $dirtyArr = LogWriter::dirtyData($this->_oldData, $this->getCurrentData());
        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $insert ? $this->name : $this->_oldData['name'],
                'action' => $insert ? 'add' : 'edit',
                'action_type' => 'Setting Sms',
                'content' => yii\helpers\Json::encode($dirtyArr),
                'class' => __CLASS__,
            ];
            LogWriter::write($logData);
        }
        //写日志结束

        //将配置写入到 配置文件中
        $this->putIniFile();
    }

    protected function putIniFile()
    {
        $str = '';
        $array = [];

        //处理原始数据  将 class 以及  setting 数据进行处理
        $data = json_decode($this->value, true);
        foreach ($data as $key => $value) {
            //处理短信参数设置部分
            if ($key == 'setting') {
                $setting = array_filter(explode('&', $value));
                if (is_array($setting)) {
                    foreach ($setting as $val) {
                        $tmp = explode('=', $val);
                        $array[trim($tmp[0])] = isset($tmp[1]) ? $tmp[1] : ' ';
                    }
                }
            } elseif ($key == 'class') {
                //处理 class 部分程序  如果是深澜短信
                if ($this->sms_type == 0) {
                    //如果写了签名, 那么就用 class中的类， 否则就用老版本短信发送
                    if ($this->sign) {
                        $array['url'] = $this->class;
                    } else {
                        $array['url'] = 'http://msg.srun.com/message/backend/web/index.php/send/message';
                    }
                } else {
                    $array['url'] = isset($value) ? $value : ' ';
                }
            } else {
                $array[$key] = isset($value) ? $value : ' ';
            }
        }

        if ($this->sms_type == 1) {
            $array['key'] = 'UserSRunRESTfulChannel';
            $array['sign'] = '';
        } else {
            $array['key'] = 'UserSRunChannel';
        }

        //处理些文件数据
        foreach ($array as $key => $value) {
            $str .= $key . "=" . "\"" . $value . "\"\n";
        }
        $dir = '/srun3/etc/srun_mesg.conf';
        if (file_exists($dir)) {
            file_put_contents($dir, $str);
        } else {
            yii\helpers\FileHelper::createDirectory($dir, 0664);
        }

        //将数据写入到 redis
        Redis::executeCommand('set', 'key:config:sms', [json_encode($array)]);
    }

    public function afterFind()
    {
        parent::afterFind();
        self::parseSms($this);
        $this->_oldData = $this->getCurrentData();
    }

    public function getCurrentData()
    {
        $normalField = ['name', 'class', 'setting'];
        $list = [];
        //给普通字段赋值
        foreach ($normalField as $field) {
            $list[$field] = $this->$field;
        }
        return $list;
    }

    public static function parseSms($model)
    {
        if ($model->value) {
            $data = json_decode($model->value, true);
            $model->name = isset($data['name']) ? $data['name'] : '';
            $model->sign = isset($data['sign']) ? $data['sign'] : '';
            $model->class = isset($data['class']) ? $data['class'] : '';
            $model->setting = isset($data['setting']) ? $data['setting'] : '';
        }
    }

    private static $_setting = null;

    /**
     * 获取设置项
     * @return array|bool|null
     */
    public static function getSetting()
    {
        if (self::$_setting == null) {
            //获取设置值
            $model = self::findOne(['key' => self::$keyName]);
            if (!$model) {
                return false;
            }
            self::parseSms($model);
            $list = [
                'name' => $model->name,
                'class' => $model->class,
            ];
            if ($model->setting) {
                $arr1 = explode("\n", $model->setting);
                if (is_array($arr1)) {
                    foreach ($arr1 as $str) {
                        $arr2 = explode('=', $str);
                        if (is_array($arr2) && count($arr2) == 2) {
                            $list['setting'][trim($arr2[0])] = trim($arr2[1]);
                        }
                    }
                }
            }
            self::$_setting = $list;
        }
        return self::$_setting;
    }

    public function set()
    {
        $this->name = $this->getAttributeList()['name'];
        $this->class = $this->getAttributeList()['class'];
        $this->setting = $this->getAttributeList()['setting'];
    }

    public function getAttributeList()
    {
        return [
            'name' => Yii::t('app', 'setting_Sms_font1'),
            'class' => 'common\models\SrunSms',
            'setting' => '
name = srun
token = e10adc3949ba59abbe56e057f20f883e
phone =
content = ' . Yii::t('app', 'setting_Sms_font2') . '',
            'sms_type' => [Yii::t('app', 'sms type sRun'), Yii::t('app', 'sms type restful')]
        ];
    }
}