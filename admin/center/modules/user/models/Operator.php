<?php
/**
 * Created by PhpStorm.
 * User: cyc
 * Date: 15-7-29
 * Time: 下午4:37
 */

namespace center\modules\user\models;

use center\modules\financial\models\WaitCheck;
use center\modules\log\models\LogWriter;
use center\modules\strategy\models\Product;
use common\extend\Tool;
use common\models\Redis;
use common\models\User;
use yii\base\Model;
use center\modules\auth\models\SrunJiegou;
use yii;

class Operator extends yii\db\ActiveRecord
{
    const STATUS_S = 0; //产品状态 正常
    const STATUS_F = 1; //产品状态 禁用
    public $userModel = null;
    public $user_name;
    public $product_name;
    public $mobile_phone;
    public $mobile_password;
    public $user_available = 0;
    public $checkout_date;
    public $products;
    public $product_id;
    public $file;
    public $excelData;
    public $editFields = [];//批量修改 可以选择的字段
    public $selectFields = [];//批量修改选择的字段
    public $conditionField = 'cert_num';//条件字段

    public function init(){
        $this->editFields = [
            'cert_num' => Yii::t('app', 'ID NO'),
            'user_available' => Yii::t('app', 'user available'),
            'group_id' => Yii::t('app', 'batch excel user group id'),
            'phone' => Yii::t('app', 'attr variable7'),
            'bind_product_id' => Yii::t('app', 'bind products id'),
            'unbind_product_id' => Yii::t('app', 'unbind products id'),
        ];
    }

    public static function tableName()
    {
        return 'user_products';
    }

    public function rules()
    {
        return [
            [['user_name', 'product_name'], 'required', 'on' => ['bind']],
            [['mobile_phone', 'mobile_password'], 'string', 'on' => ['bind']],
            ['user_available', 'integer', 'on' => ['bind']],
            [['product_id', 'file'], 'required', 'on' => ['import']],
            [['selectFields', 'file'], 'required', 'on' => ['batch-edit']],
            ['file', 'file', 'on' => ['import', 'batch-edit']],
            [['product_id'], 'required', 'on' => ['import', 'export']],
            ['product_id', 'integer', 'on' => ['import', 'export']],
        ];
    }

    public function scenarios()
    {
        return [
            'bind' => ['user_name', 'product_name'],
            'import' => ['product_id'],
            'export' => ['product_id'],
            'export' => ['selectFields','file'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_name' => Yii::t('app', 'account'),
            'product_name' => Yii::t('app', 'product'),
            'mobile_phone' => Yii::t('app', 'carrier operator mobile_phone'),
            'mobile_password' => Yii::t('app', 'carrier operator mobile_password'),
            'user_available' => Yii::t('app', 'Status'),
            'checkout_date' => Yii::t('app', 'checkout date'),
            'product_id' => Yii::t('app', 'product'),
            'selectField' => Yii::t('app', 'batch excel select'),
        ];
    }

    public function getAttributesList()
    {
        return [
            'user_available' => [
                self::STATUS_S => Yii::t('app', 'user available0'),
                self::STATUS_F => Yii::t('app', 'user available1'),
            ]
        ];
    }

    private $_isInterfaceMgr = null;
    private $_productList = [];
    /**
     * 获取产品列表
     *
     * @param $product_ids array
     *
     * @return array
     */
    public function setProductList($product_ids = [])
    {
        //如果是接口管理员
        if ($this->_isInterfaceMgr) {
            if (empty($product_ids)) {
                $this->_productList = [];
            } else {
                $this->_productList = (new Product())->getNameArr($product_ids);
            }
        } //如果不是接口
        else {
            $this->_productList = (new Product())->getNameOfList();
        }
        return $this->_productList;
    }

    /**
     * 获取用户产品实例列表
     *
     * @param $uid
     * @param $products_id
     *
     * @return array
     */
    public function getProObjList($uid, $products_id)
    {

        // 查询订购的产品实例
        $list = [];
        if ($uid) {
            if (is_array($products_id)) {
                foreach ($products_id as $id) {
                    $proObj = $this->getOneProObj($uid, $id);
                    if ($proObj) {
                        $list[] = $proObj;
                    }
                }
            }
        }
        return $list;
    }

    public function getOneProObj($uid, $pid)
    {
        $proObj = [];
        $userModel = new Base();
        $productModel = new Product();
        $proList = (new Product())->getNameOfList();
        //判断产品是否可以管理，如果不能管理，跳过
        if (!array_key_exists($pid, $proList)) {
            return false;
        }
        $one = self::find()->where(['user_id' => $uid, 'products_id' => $pid])->asArray()->one();
        $proObj = $userModel->getOneProductObj($uid, $pid);
        if ($proObj) {
            $proObj['mobile_phone_show'] = isset($one['mobile_phone']) && !empty($one['mobile_phone']) ? $one['mobile_phone'] : $proObj['mobile_phone_show'];
            //获取此用户此产品的结算日期
            $waitCheckModel = WaitCheck::findOne(['user_id' => $uid, 'products_id' => $pid]);
            if ($waitCheckModel) {
                $proObj['checkout_date'] = date('Y-m-d',
                    $waitCheckModel->checkout_date);
            }
            $proObj['product_name'] = $productModel->getOneName($pid);
        }
        return $proObj;
    }

    /**
     *修改产品实例
     *
     * @param $user_name
     * @param $product_id
     * @param $mobile          手机号（运行商帐号）
     * @param $mobile_password 手机密码
     * @param $available 0-正常 1-禁用
     *
     * @return mixed
     */
    public function updateProObj($user_name, $product_id, $mobile, $mobile_password, $available)
    {
        if (empty($user_name) || empty($product_id)) {
            return false;
        }
        $data = [
            'action' => 9,
            'serial_code' => time() . rand(111111, 999999),
            'time' => time(),
            'user_name' => $user_name,
            'products_id' => $product_id,
            'mobile_phone' => $mobile,
            'mobile_phone_show' => str_replace(substr($mobile, 0, 7), '*******', $mobile),
            'mobile_password' => $mobile_password,
            'user_available' => $available,
            'proc' => 'admin',
        ];
        $json = json_encode($data);
        $res = Redis::executeCommand('RPUSH', "list:interface", [$json]);
        if($res){
            $product_name = (new Product())->getOne($product_id)['products_name'];
            //写日志开始
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => $user_name,
                'action' => 'add',
                'action_type' => 'User Operator',
                'content' => Yii::t('app','carrier operator bind log',['operator'=>Yii::$app->user->identity->username,'user_name'=>$user_name,'product_name'=>$product_name,'mobile'=>$mobile,'mobile_password'=>str_replace(substr($mobile_password, 0, 3), '***', $mobile_password)]),
                'class' => __CLASS__,
                'type' => 1
            ];
            LogWriter::write($logData);
            //写日志结束
        }
        return $res;
    }

    /**
     * 保存提交内容
     * @param $data
     * @return bool
     */
    public function saves($data)
    {
        if (empty($data['user_name']) || empty($data['products_id']) || empty($data['uid'])) {
            return false;
        }
        $resUpdate = $this->updateProObj($data['user_name'], $data['products_id'], trim($data['mobile_phone']), trim($data['mobile_password']), $data['user_available']);
        if (!$resUpdate) {
            return false;
        }
        //修改结算日期
        if (!empty($data['checkout_date'])) {
            $waitModel = new WaitCheck();
            $waitModel->updateAll(['checkout_date' => strtotime($data['checkout_date'])], ['user_id' => $data['uid'], 'products_id' => $data['products_id']]);
        }
        return true;
    }

    public function excelDemo()
    {
        $excelList = [
            0 => [
                '0' => Yii::t('app', 'account'),
                '1' => Yii::t('app', 'carrier operator mobile_phone'),
                '2' => Yii::t('app', 'carrier operator mobile_password'),
            ],
            1 => [
                '0' => '20150901',
                '1' => '13812345678',
                '2' => '415263',
            ],
            2 => [
                '0' => '20150908',
                '1' => '15912345678',
                '2' => '147258',
            ]
        ];
        return $excelList;
    }

    /**
     * 批量绑定运营商账号、密码
     * @param $data
     * @param $product_id
     * @return array
     */
    public function batchImport($data, $product_id)
    {
        $i = 0;
        $array_res = [
            '0' => [
                Yii::t('app', 'account'),
                Yii::t('app', 'batch excel result'),
                Yii::t('app', 'batch import help3'),
            ]
        ];
        if (!empty($data)) {
            foreach ($data as $v) {
                $res = $this->updateProObj($v[0], $product_id, $v[1], $v[2], 0);
                if($res){
                    $i++;
                    $array_res[] = [$v[0],Yii::t('app','success')];
                }else{
                    $array_res[] = [$v[0],Yii::t('app','failed')];
                }
            }
            //写日志开始
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => Yii::t('app','batch'),
                'action' => 'add',
                'action_type' => 'User Operator',
                'content' => Yii::$app->user->identity->username.Yii::t('app','batch import help1',['success_num'=>$i,'failed_num'=>count($data)-$i]),
                'class' => __CLASS__,
                'type' => 1
            ];
            LogWriter::write($logData);
            //写日志结束
        }
        $array_res[1][2] .= Yii::t('app','batch import help2',['success_num'=>$i,'failed_num'=>count($data)-$i]);
        return ['data' => $array_res, 'success_num' => $i, 'failed_num' => count($data)-$i];
    }

    /**
     * 批量导出用户的运营商账号
     * @param $data
     * @return array
     */
    public function batchExport($data){
        $res = [
            '0' =>[
                Yii::t('app','serial number'),
                Yii::t('app','account'),
                Yii::t('app','product'),
                Yii::t('app','products balance'),
                Yii::t('app','carrier operator mobile_phone'),
                Yii::t('app','user status'),
            ]
        ];
        if(!empty($data)){
            $product_name = (new Product())->getOne($data[0]['products_id'])['products_name'];
            $i = 0;
            foreach($data as $v){
                $res[] = [
                    $i+1,
                    $v['user_name'],
                    $product_name,
                    $v['user_balance'],
                    $v['mobile_phone'],
                    !is_null($v['user_available'])?$this->getAttributesList()['user_available'][$v['user_available']]:'',
                ];
            }
        }
        return $res;
    }

    /**
     * 批量导出用户
     * @param $data
     * @return array
     */
    public function batchExportUser($data){
        $res = [
            '0' => $data[0]
        ];
        if(!empty($data)){
            unset($data[0]);
            $i = 0;
            foreach($data as $v){
                $res[] = [$v];
            }
        }
        return $res;
    }

    /**
     * 修改运营商下载模板
     * @param array $params
     * @return array
     */
    public function excelEditDemo($params = [])
    {
        $excelList = [];
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                if (strstr($v, 'bind')) {
                    $excelList[0][$k] = Yii::t('app', 'bind products id');
                } else if(strstr($v, 'group')){
                    $excelList[0][$k] = Yii::t('app', 'batch excel user group id');
                } else if(strstr($v, 'user_available')) {
                    $excelList[0][$k] = Yii::t('app', 'user available').'('.Yii::t('app', 'batch edit help7').')';
                } else if (strstr($v, 'unbind')) {
                    $excelList[0][$k] = Yii::t('app', 'unbind products id');
                } else if (strstr($v, 'phone')) {
                    $excelList[0][$k] = Yii::t('app', 'attr variable7');
                } else {
                    $excelList[0][$k] = Yii::t('app', $v);
                }

            }
        }


        return $excelList;
    }

    public function batchEdit($data){
        //根据条件来更新 用户的信息 包括产品，用户状态

        $array_res = [
            '0' => [
                $this->editFields[$this->conditionField],
                Yii::t('app', 'batch excel result'),
            ]
        ];
        $i = 0;
        if($data){
            //可以管理的产品
            $products = $this->setProductList();
            $mgrName = Yii::$app->user->identity->username;

            $isSuper = User::isSuper();
            if($isSuper === false){
                //根据可管理的产品查询 可管理的用户id
                $uids = $ids = [];
                //根据可管理的用户组查询 可管理的用户id
                $canMgrOrg = SrunJiegou::getAllNode();
                $users = (new Base())->find()->select('user_id')->andWhere(['in', 'group_id', $canMgrOrg])->asArray()->all();
                foreach ($users as $user) {
                    $uids[] = $user['user_id'];
                }

            }
            foreach($data as $one){
             //   var_dump($products);
                $fieldData = [];
                foreach($this->selectFields as $k => $field){
                    $fieldData[$field] = $one[$k] == null ? '' : trim($one[$k]) ;
                }

                if(empty($fieldData[$this->conditionField])){
                    $array_res[] = [ $this->conditionField, Yii::t('app','batch edit help2') ];
                    continue;
                }

                $conditionVal = $fieldData[$this->conditionField];
                // 查询数据库中用户的信息

                $count = Base::find()->where([$this->conditionField => $conditionVal])->count();
                if($count>1){
                    $users = Base::find()->where([$this->conditionField => $conditionVal])->asArray()->all();
                    foreach($users as $k => $one){
                        //由于证件号不是唯一的，所以得在有管理权限的产品和用户组的用户中才搜索用户
                        if($isSuper === false){
                            if(!in_array($one['user_id'], $uids) || !in_array($one['group_id'], $canMgrOrg)){
                                unset($users[$k]);
                            }
                        }
                    }
                    if(count($users)>1){
                        $array_res[] = [ $conditionVal, Yii::t('app', 'user operate error')];
                        continue;
                    }
                    if(count($users) == 0){
                        $array_res[] = [ $conditionVal, Yii::t('app','batch excel help15') ];
                        continue;
                    }
                    $userModel = Base::findOne(['user_name' => array_values($users)[0]['user_name']]);
                }
                if($count == 1){
                    $userModel = Base::findOne([$this->conditionField => $conditionVal]);
                    if($isSuper === false){
                        if(!in_array($userModel->user_id, $uids) || !in_array($userModel->group_id, $canMgrOrg)){
                            $array_res[] = [ $conditionVal, Yii::t('app', 'message 401 3')];
                            continue;
                        } else {
                            if (isset($fieldData['group_id'])) {
                                if(!in_array($fieldData['group_id'], $canMgrOrg)){
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'message 401 3')];
                                    continue;
                                }
                            }
                        }
                    }
                }
                if($count == 0){
                    $array_res[] = [ $conditionVal, Yii::t('app','batch excel help15') ];
                    continue;
                }
                if(empty($userModel)){
                    $array_res[] = [ $conditionVal, Yii::t('app','batch excel help15') ];
                    continue;
                }

                //判断组织结构和产品是否可用
                //判断组织结构
                /*if(!User::canManage('org', $userModel->group_id)){
                    $array_res[] = [ $conditionVal, Yii::t('app', 'message 401 3').'('.Yii::t('app', 'user_group').')'];
                    continue ;
                }
                //判断产品
                if($userModel->products_id){
                    $pidErr = false;
                    foreach($userModel->products_id as $pid){
                        if(!User::canManage('product', $pid)){
                            $pidErr = true;
                            break;
                        }
                    }
                    if($pidErr){
                        $array_res[] = [ $conditionVal, Yii::t('app', 'message 401 3').'('.Yii::t('app', 'product').')'];
                        continue;
                    }
                }*/

                $chgStr = '';
                //修改用户状态
                if(isset($fieldData['user_available'])){
                    if(in_array($fieldData['user_available'], [0, 1])){
                        $availOld = $userModel->user_available;
                        if($fieldData['user_available'] == 1){//正常
                            $userModel->user_available = 0;
                        }else{//暂停
                            $userModel->user_available = 3;
                        }
                        $chgStr .=  'user_available:'. $availOld.'=>'.$userModel->user_available;
                    }else{
                        $array_res[] = [ $conditionVal, Yii::t('app', 'batch edit help5')];
                        continue;
                    }
                }

                //修改手机号
                if(isset($fieldData['phone']) && !empty($fieldData['phone'])){
                    $oldPhone = $userModel->phone;
                    $userModel->phone = $fieldData['phone'];
                    if (!empty($chgStr)) {
                        $chgStr .=  ';phone:'. $oldPhone.'=>'.$fieldData['phone'];
                    } else {
                        $chgStr .=  'phone:'. $oldPhone.'=>'.$fieldData['phone'];
                    }
                }

                //修改用户组
                if(isset($fieldData['group_id']) && !empty($fieldData['group_id'])){
                    $oldGroup = $userModel->group_id;
                    $userModel->group_id = $fieldData['group_id'];
                    if (!empty($chgStr)) {
                        $chgStr .=  ';group:'. $oldGroup.'=>'.$fieldData['group_id'];
                    } else {
                        $chgStr .=  'group:'. $oldGroup.'=>'.$fieldData['group_id'];
                    }
                }

                $res = $userModel->save(false);
                if(!empty($fieldData['user_available']) || !empty($fieldData['group_id']) || !empty($fieldData['phone'])){
                    if(!is_array($res) && $res){
                        $array_res[] = [ $conditionVal, Yii::t('app', 'user operate edit success', ['user'=>$userModel->user_name, 'detail'=>$chgStr])];
                    } else {
                        $array_res[] = [ $conditionVal,Yii::t('app', 'user operate edit error', ['user'=>$userModel->user_name])];
                        continue;
                    }

                }

                //绑定产品 不进行结算和 转余额
                if(isset($fieldData['bind_product_id']) && !empty($fieldData['bind_product_id'])){
                    $bind_product_ids = explode(',', $fieldData['bind_product_id']);
                    if($bind_product_ids){
                        $url =  Yii::$app->params['api_prefix'].Yii::$app->params['api_host']. Yii::$app->params['api_port'].'/api/v1/product/subscribe';
                        foreach($bind_product_ids as $id){
                            //通过接口进行绑定产品
                            $proName = $id.':'. (new Product())->getOneName($id);
                            //var_dump($post_data);exit;
                            //判断该管理员是否能管理该产品
                            if (!empty($products)) {
                                if (!isset($products[$id])) {
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product error', ['mgr'=>$mgrName, 'pro'=>$proName])];
                                    continue;
                                }
                            } else {
                                $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product error1', ['mgr'=>$mgrName, 'pro'=>$proName])];
                                continue;
                            }
                            $post_data = [
                                'access_token' => Yii::$app->params['mgr_access_token'],
                                'user_name' => $userModel->user_name,
                                'product' => $id,
                            ];
                            if(empty($post_data['access_token'])){
                                $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product failure', ['user'=>$userModel->user_name, 'pro'=>$proName,'msg' => 'api_token is empty' ])];
                                continue;
                            }

                            //var_dump($fieldData);
                            //echo $url;exit;
                            $res = Tool::postApi($url, http_build_query($post_data));
                            if($res){
                                $res = json_decode($res, true);
                                if($res['code'] == 0 && $res['message'] == 'ok'){

                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product success', ['user'=>$userModel->user_name, 'pro'=>$proName,])];
                                }else{
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product failure',['user'=>$userModel->user_name, 'pro'=>$proName,'msg' => $res['message'] ])];
                                    continue;
                                }
                            }
                        }
                    }
                }

                //解绑产品
                if(isset($fieldData['unbind_product_id']) && !empty($fieldData['unbind_product_id'])){
                    $unbind_product_ids = explode(',', $fieldData['unbind_product_id']);
                    if($unbind_product_ids){
                        $url = Yii::$app->params['api_prefix'].Yii::$app->params['api_host']. Yii::$app->params['api_port'].'/api/v1/product/cancel';
                        foreach($unbind_product_ids as $id){
                            //通过接口进行取消产品
                            $proName = $id.':'. (new Product())->getOneName($id);
                            if (!empty($products)) {
                                if (!isset($products[$id])) {
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product error', ['mgr'=>$mgrName, 'pro'=>$proName])];
                                    continue;
                                }
                            } else {
                                $array_res[] = [ $conditionVal, Yii::t('app', 'user bind product error1', ['mgr'=>$mgrName, 'pro'=>$proName])];
                                continue;
                            }
                            $post_data = [
                                'access_token' => Yii::$app->params['mgr_access_token'],
                                'user_name' => $userModel->user_name,
                                'products_id' => $id,
                            ];
                            if(empty($post_data['access_token'])){
                                $array_res[] = [ $conditionVal, Yii::t('app', 'user unbind product failure',['user'=>$userModel->user_name, 'pro'=>$proName,'msg' => 'api_token is empty' ])];
                                continue;
                            }
                            $res = Tool::postApi($url, http_build_query($post_data));
                            if($res){
                                $res = json_decode($res, true);
                                if($res['code'] == 0 && $res['message'] == 'ok'){
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user unbind product success', ['user'=>$userModel->user_name, 'pro'=>$proName])];
                                }else{
                                    $array_res[] = [ $conditionVal, Yii::t('app', 'user unbind product failure', ['user'=>$userModel->user_name, 'pro'=> $proName, 'msg'=>$res['message']])];
                                    continue;
                                }
                            }
                        }
                    }
                }

            }
        }

        return ['data' => $array_res, 'success_num' => count($data)-$i, 'failed_num' => $i];
    }
} 