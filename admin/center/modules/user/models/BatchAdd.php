<?php
/**
 * 批量开户模型
 */

namespace center\modules\user\models;

use common\models\UserModel;
use yii;
use center\modules\Core\models\BaseModel;
use center\modules\financial\models\PayList;
use center\modules\financial\models\PayType;
use center\modules\log\models\LogWriter;
use center\modules\strategy\models\IpPart;
use center\modules\strategy\models\Product;

class BatchAdd extends yii\base\Model
{
    // 批量开户
    public $pre = ''; //前缀
    public $user_start = '';//开始数字
    public $user_stop = '';//结束数字
    public $num_len = '';//数字长度
    public $user_gen_method = 1;//生成方式
    public $suffix = ''; //后缀
    public $user_password = 1; //密码方式
    public $user_pass_value = ''; //密码值
    public $pw_type = 2; //密码生成方式
    public $passwd_len = ''; //密码长度
    public $group_id = '';//组织结构id
    public $user_allow_chgpass = 1; //是否允许修改密码
    public $user_expire_time = ''; //过期时间
    public $gen_num = ''; //生成数量
    public $user_available = 0; //用户状态
    public $products_id = []; // 产品
    public $balance = 0; //余额
    public $payType;//缴费方式
    public $begin_time;
    public $major_id;
    public $stop_time;
    public $major_name;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            [['user_start', 'user_stop', 'gen_num', 'group_id', 'products_id'], 'required'],
            [['gen_num', 'num_len'], 'integer', 'min' => 1],
            ['gen_num', 'gen_num_validate'],
            ['group_id', 'groupMust'],
            [['user_start', 'user_stop'], 'integer', 'min' => 0],
            ['user_pass_value', 'default', 'value' => '123456'],
            ['passwd_len', 'default', 'value' => '6'],
        ];
    }

    /**
     * 自定义rules：用户名起始数字至截止数字的数量不能小于生成用户数
     * @param $attribute
     * @param $params
     */
    public function gen_num_validate($attribute, $params)
    {
        $params = Yii::$app->request->post()['BatchAdd'];
        if (($params['user_stop'] - $params['user_start'] + 1) < $params['gen_num']) {
            $this->addError($attribute, Yii::t('app', 'batch add help6'));
        }
    }

    /**
     * 自定义rules：必须选择用户组
     * @param $attribute
     * @param $params
     */
    public function groupMust($attribute, $params)
    {
        $params = Yii::$app->request->post()['BatchAdd'];
        if ($params['group_id'] < 1) {
            $this->addError($attribute, Yii::t('app', 'user base help20'));
        }
    }
    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        return [
            'default' => ['pre', 'user_start', 'user_stop', 'num_len', 'user_gen_method', 'suffix', 'user_password',
                'user_pass_value', 'pw_type', 'passwd_len', 'user_allow_chgpass', 'user_expire_time', 'gen_num',
                'user_status', 'group_id', 'begin_time', 'stop_time', 'major_id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pre' => Yii::t('app', 'batch add pre'),
            'user_start' => Yii::t('app', 'batch add user start'),
            'user_stop' => Yii::t('app', 'batch add user stop'),
            'num_len' => Yii::t('app', 'batch add num len'),
            'user_gen_method' => Yii::t('app', 'batch add user gen method'),
            'suffix' => Yii::t('app', 'batch add suffix'),
            'user_password' => Yii::t('app', 'batch add user password'),
            'user_pass_value' => Yii::t('app', 'batch add user pass value'),
            'pw_type' => Yii::t('app', 'batch add pw type'),
            'passwd_len' => Yii::t('app', 'batch add passwd len'),
            'user_allow_chgpass' => Yii::t('app', 'batch add user allow chgpass'),
            'user_expire_time' => Yii::t('app', 'user expire time'),
            'gen_num' => Yii::t('app', 'batch add gen num'),
            'user_available' => Yii::t('app', 'user available'),
            'user_status' => Yii::t('app', 'user status'),
            'products_id' => Yii::t('app', 'user products id'),
            'balance' => Yii::t('app', 'batch add balance'),
            'begin_time' => '入学时间',
            'stop_time' => '毕业时间',
            'major_id' => '专业性质'
        ];
    }

    public function getAttributesList()
    {
        return [
            'user_gen_method' => [
                '1' => Yii::t('app', 'user gen method1'),
                '2' => Yii::t('app', 'user gen method2'),
            ],
            'user_password' => [
                '1' => Yii::t('app', 'batch user password1'),
                '2' => Yii::t('app', 'batch user password2'),
            ],
            'pw_type' => [
                '1' => Yii::t('app', 'batch pw type1'),
                '2' => Yii::t('app', 'batch pw type2'),
            ],
            'user_allow_chgpass' => [
                '1' => Yii::t('app', 'yes'),
                '0' => Yii::t('app', 'no'),
            ],
            'user_available' => [
                '0' => Yii::t('app', 'user available0'),
                '1' => Yii::t('app', 'user available1'),
            ],
        ];
    }

    /**
     * 批量开户
     * @return array|bool
     */
    public function batch_add_user()
    {
        $excel_err = $excel_ok = array();
        //用户名截止数字减去起始数字不能小于生成用户数
        if ($this->user_stop - $this->user_start < $this->gen_num - 1) {
            return false;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            //查询是否有ip段设置
            $user_names = $users = array();
            // 固定密码
            if ($this->user_password == 1) {
                $password = $this->user_pass_value;
            } // 生成随机密码
            else {
                $password = $this->get_raw_password();
            }
            for ($i = 0; $i < $this->gen_num; $i++) {
                $user = array();
                // 用户名随机产生
                if ($this->user_gen_method == 2) {
                    mt_srand(( double )microtime() * 1000000);
                    $randVal = mt_rand($this->user_start, $this->user_stop);
                    $str_i_len = strlen(strval($randVal));
                    $user_name = $this->get_rand_user_name($str_i_len, $randVal);
                } // 随机产生顺序产生
                else {
                    $str_i_len = strlen(strval($i + $this->user_start));
                    $user_name = $this->get_user_name($str_i_len, $i);
                }
                // 如果生成的用户名是第一次生成，放进用户名数组，
                // 如果为了保证生成的数量，那么可以在此处就判断用户名是否存在，但是效率会很低
                //如果没有生成有效的用户名，跳过
                if (empty($user_name)) {
                    $i--;
                    continue;
                }
                if (!in_array($user_name, $user_names)) {
                    $user_names[] = $user_name;
                    $users[$user_name] = array_merge(['user_name' => $user_name, 'user_password' => $password], $this->attributes);
                } // 如果生成重复的用户名，返回循环一次
                else {
                    $i--;
                    continue;
                }
            }
            $userModel = new UserModel();
            $rs = $userModel->batchSave($users);
            // 所有Excel文件中的数据已经处理完毕
            // 开始整合发生错误的数组和正确的数组
            $excel_err = isset($rs['excel_err']) ? $rs['excel_err'] : [];
            $excel_ok = isset($rs['excel_ok']) ? $rs['excel_ok'] : [];
            $list = array_merge($excel_err, $excel_ok);
            $excel_header = [
                Yii::t('app', 'user id'),
                Yii::t('app', 'account'),
                Yii::t('app', 'password'),
                Yii::t('app', 'user create time'),
                Yii::t('app', 'user expire time'),
                Yii::t('app', 'operator'),
            ];
            $excel_header[] = Yii::t('app', 'extra message');
            array_unshift($list, $excel_header);
            // 返回一个数组用来填充Excel供用户下载
            $trans->commit();
            return array('ok' => count($excel_ok), 'err' => count($excel_err), 'list' => $list);
        } catch (\Exception $e) {
            //回滚
            $trans->rollBack();
            var_dump($e->getMessage(), $_POST, $e->getLine(), $e->getFile());exit;
            return false;
        }
    }

    /**
     * 随机生成用户名
     * @param int $str_i_len
     * @param string $randVal
     * @return string
     */
    private function get_rand_user_name($str_i_len = 0, $randVal = '')
    {
        $user_name = $zero_add = $suffix = "";
        if ($str_i_len < $this->num_len) {
            $suffix = sprintf("%0" . ($this->num_len) . "d", $randVal);
        } else {
            $suffix = strval($randVal);
        }
        if ($this->pre != "") {
            $user_name = $this->pre . $suffix;
        } else {
            $user_name = $suffix;
        }
        return $user_name . $this->suffix;
    }

    /**
     * 顺序生成用户名
     * @param int $str_i_len
     * @param int $i
     * @return string
     */
    private function get_user_name($str_i_len = 0, $i = 0)
    {
        $user_name = $zero_add = $suffix = "";
        if ($str_i_len < $this->num_len) {
            $suffix = sprintf("%0" . ($this->num_len) . "d", strval($i + $this->user_start));
        } else {
            $suffix = strval($i + $this->user_start);
        }
        if ($this->pre != "") {
            $user_name = $this->pre . $suffix;
        } else {
            $user_name = $suffix;
        }
        return $user_name . $this->suffix;
    }

    /**
     * 获取一条随机密码
     * @return string
     */
    private function get_raw_password()
    {
        $pwd = '';
        switch ($this->pw_type) {
            case 1:
                $pwd = rand((int)str_repeat('1', $this->passwd_len), (int)str_repeat('9', $this->passwd_len));
                break;
            case 2:
                $pwd = strtoupper(substr(md5(base64_encode(pack('N6', mt_rand(), mt_rand(),
                    mt_rand(), mt_rand(), mt_rand(), uniqid()))), 0, $this->passwd_len));
                break;
        }
        return $pwd;
    }

}