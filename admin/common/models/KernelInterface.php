<?php
/**
 * 往核心发送数据的接口
 * User: ligang
 * Date: 2015/1/16
 * Time: 14:08
 */
namespace common\models;

use center\modules\financial\models\Bills;
use center\modules\strategy\models\IpPool;
use center\modules\user\models\Base;
use common\extend\Tool;

class KernelInterface extends \yii\redis\Connection
{
    //允许的下线类型
    public static $dropTypes = [
        'portal' => 2, //portal 下线
        'radius' => 1, //以下为 radius 下线
        'dhcp' => 2,
        'proxy' => 5,
        'local' => 7, //本地下线
        'virtual' => 8, //虚拟下线，只写明细不下线 产品实例中除了扣费还会修改产品实例中的使用量
        'virtual_ded' => 10,//虚拟下线，只写明细不下线 产品实例中扣费
    ];

    /**
     * 用户下线，包括portal下线，radius的dm下线
     * @param $type string
     * @param $data array
     * @return string
     */
    public static function userDrop($type, $data = [])
    {
        if (!array_key_exists($type, self::$dropTypes)) {
            return false;
        }
        if ($type == 'portal') {
            return self::dropPortal($data);
        } else {
            return self::dropRadius($type, $data);
        }
    }

    /**
     * portal 下线
     * @param $data array
     * @return string
     */
    private static function dropPortal($data)
    {
        if (empty($data['user_ip'])) {
            return false;
        }
        $serial_code = time() . rand(111111, 999999); //唯一的流水号
        $arr = [
            'action' => 2,
            'user_ip' => $data['user_ip'],
            'serial_code' => $serial_code,
            'time' => time(),
        ];
        $json = json_encode($arr);
        $res = Redis::executeCommand('RPUSH', "list:auth", [$json]);
        return $res ? $serial_code : false;
    }

    /**
     * radius 下线
     * @param $type string
     * @param $data array
     * @return string
     */
    private static function dropRadius($type, $data)
    {
        if (empty($data['online_id'])) {
            return false;
        }
        //唯一的流水号
        $serial_code = time() . rand(111111, 999999);
        //从在线表中获取my_ip，这个地址表示由哪台AAA发DM，因为设备只接收与它对接的AAA的DM报文。
        $my_ip = Redis::executeCommand('HGET', 'hash:rad_online:' . $data["online_id"], ['my_ip'], 'redis_online');
        $res = false;
        if($my_ip){
            $arr = [
                'action' => self::$dropTypes[$type],
                'online_id' => $data['online_id'],
                'serial_code' => $serial_code,
                'time' => time(),
                'proc' => 'admin',
            ];
            $json = json_encode($arr);
            $res = Redis::executeCommand('RPUSH', 'list:rad_dm:' . $my_ip, [$json], 'redis_online');
        }
        return $res ? $serial_code : false;
    }

    /**
     * radius 获取在线的流量，时长，上线次数，且可以虚拟下线
     *
     * @param $user_name string
     * @param $is_vir_drop int
     * @return string
     */
    public static function onlineInfo($user_name, $is_vir_drop = 0)
    {
        $array = Redis::executeCommand('SMEMBERS', 'set:rad_online:user_name:' . $user_name, [], 'redis_online');
        $sum_bytes = 0; //当前在线的总流量
        $sum_seconds = 0;
        $sum_times = 0;
        foreach ($array as $rad_online_id) {
            if($rad_online_id){
                $res = Redis::executeCommand('HGETALL', 'hash:rad_online:' . $rad_online_id, [], 'redis_online');
                if ($res) {
                    $online_info = Redis::hashToArray($res);
                    //取在线流量
                    $sum_bytes += (($online_info["bytes_in"] - $online_info["bytes_in1"]) * $online_info["traffic_down_ratio"] / 100) +
                        (($online_info["bytes_out"] - $online_info["bytes_out1"]) * $online_info["traffic_up_ratio"] / 100);
                    //取在线时长
                    $time = time();
                    $sum_seconds += $time - $online_info["add_time"];
                    //登录次数
                    $sum_times++;
                    if ($is_vir_drop == 1) {
                        //虚拟下线
                        $arr = ['online_id' => $rad_online_id];
                        self::dropRadius('virtual_ded', $arr);
                    }
                }
            }
        }
        $data = [
            'sum_bytes' => $sum_bytes,
            'sum_seconds' => $sum_seconds,
            'sum_times' => $sum_times,
        ];
        return json_encode($data);
    }

    /**
     * 获取产品实例信息
     *
     * @param int $user_name
     * @param int $products_id
     * @return array
     */
    public static function productObj($user_name, $products_id)
    {
        $user_id = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        $one = Redis::executeCommand('HGETALL', 'hash:users:products:' . $user_id . ':' . $products_id);
        if ($one) {
            return Redis::hashToArray($one);
        }
        return [];
    }

    /**
     * 更改产品实例余额
     *
     * @param string $user_name
     * @param int $products_id
     * @param float $amount
     * @return unknow
     */
    public static function updateproductBal($user_name, $products_id, $amount)
    {
        if (!empty($user_name) && !empty($products_id)) {
            $data = [
                'action' => 3,
                'serial_code' => time() . rand(111111, 999999),
                'time' => time(),
                'proc' => 'admin',
                'user_name' => $user_name,
                'amount' => floatval($amount),
                'products_id' => $products_id,
            ];
            $json = json_encode($data);
            return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
        } else {
            return false;
        }
    }

    /**
     * 清空产品实例的流量、时长、上线次数、结算金额
     *
     * @param int $user_name
     * @param int $products_id
     * @return unknow
     */
    public static function cleanProObj($user_name, $products_id)
    {
        $user_id = Redis::executeCommand('get', 'key:users:user_name:' . $user_name);
        return Redis::executeCommand('HMSET', 'hash:users:products:' . $user_id . ':' . $products_id, ['sum_bytes', 0, 'sum_seconds', 0, 'sum_times', 0, 'checkout_amount', 0, 'saving_amount', 0, 'user_charge', 0]);
    }


    public static function productCheckout($user_name, $check_num, $product_id)
    {
        if (!empty($user_name) && !empty($product_id)) {
            $data = [
                'action' => 8,
                'serial_code' => time() . rand(111111, 999999),
                'time' => time(),
                'proc' => 'admin',
                'user_name' => $user_name,
                'amount' => -$check_num,
                'products_id' => $product_id,
            ];
            $json = json_encode($data);
            return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
        } else {
            return false;
        }
    }

    /**
     * 添加待结算队列 1-开户模块或产品绑定模块写入 2-缴费模块写入 3-认证模块写入 4-结算模块写入
     *
     * @param int $user_id
     * @param int $products_id
     * @param int $proc 模块
     * @return bool|mixed
     */
    public static function addCheckoutList($user_id, $products_id, $proc)
    {
        if (!empty($user_id) && !empty($products_id) && ($proc >= 1 && $proc <= 4)) {
            $data = [
                'user_id' => $user_id,
                'products_id' => $products_id,
                'proc' => $proc,
            ];
            $json = json_encode($data);
            return Redis::executeCommand('RPUSH', 'list:waiting_checkout', [$json]);
        } else {
            return false;
        }
    }

    /**
     * 写入已结算队列，待写入数据库结算清单表
     *
     * @param array $data
     * @return bool|mixed
     */
    public static function addCheckoutedList($data)
    {
        if (!empty($data['user_name']) && (!empty($data['products_id']) || $data['type'] == 2)) {
            $data_list = [
                'user_name' => $data['user_name'],
                'products_id' => $data['products_id'],
                'package_id' => isset($data['package_id']) ? $data['package_id'] : 0,
                'checkout_amount' => isset($data['checkout_amount']) ? $data['checkout_amount'] : 0,
                'rt_spend_num' => isset($data['rt_spend_num']) ? $data['rt_spend_num'] : 0,
                'user_balance' => $data['user_balance'],
                'sum_bytes' => isset($data['sum_bytes']) ? $data['sum_bytes'] : 0,
                'sum_seconds' => isset($data['sum_seconds']) ? $data['sum_seconds'] : 0,
                'sum_bytes6' => isset($data['sum_bytes6']) ? $data['sum_bytes6'] : 0,
                'sum_seconds6' => isset($data['sum_seconds6']) ? $data['sum_seconds6'] : 0,
                'sum_times' => isset($data['sum_times']) ? $data['sum_times'] : 0,
                'type' => isset($data['type']) ? $data['type'] : 0,
                'remark' => isset($data['remark']) ? $data['remark'] : '',
                'create_at' => isset($data['create_at']) ? $data['create_at'] : '',
                'group_id' => isset($data['group_id']) ? $data['group_id'] : 0,
            ];
            $json = json_encode($data_list);

            return Redis::executeCommand('RPUSH', 'list:checkout', [$json]);
        } else {
            return false;
        }
    }

    /**
     * 删除已结算队列
     *
     * @param array $data
     * @return mixed
     */
    public static function delCheckoutedList($data)
    {
        $json = json_encode($data);
        return Redis::executeCommand('LREM', 'list:checkout', [0, $json]);
    }

    /**
     * 查询mac认证地址是否重复
     * 
     * @param $address
     * @return bool
     */
    public static function checkMacAuth($address)
    {
        return Redis::executeCommand('get', 'key:users:mac_auth:' . $address);
    }
    
    /**
     * 操作绑定数据
     * @param $data
     * @return bool
     */
    public static function userBind($data)
    {
        $arr = [
            'action' => 6,
            'serial_code' => time() . rand(111111, 999999), //唯一的流水号
            'time' => time(),
            'proc' => 'admin',
            'user_name' => $data['user_name'],
            'type' => intval($data['type']),
            'value' => $data['value'],
            'operation' => $data['operation'], //1添加，2删除
        ];
        $json = json_encode($arr);
        $res = Redis::executeCommand('RPUSH', 'list:interface', [$json]);
        if($arr['operation'] == 1 && $arr['type'] == 2 && $res){//mac绑定 通知消息给用户
            $data = [
                '{ACCOUNT}' => $arr['user_name'],
                '{MAC_ADDR}' => $arr['value'],
            ];
            self::bindNotice($data);
        }

        //ip的操作
        if($data['type'] == 5){
            $uid = Redis::executeCommand('get', 'key:users:user_name:'.$data['user_name']);
            if($arr['operation'] == 1){
                //绑定ip后更新ip池的使用状态
                IpPool::updateAll(['status' => 2, 'uid' => $uid], ['ip' => $data['value']]);
            }else{
                //回收ip
                (new IpPool())->Recycling($data['value'], $uid);
            }
        }
        return $res;
    }

    /**
     * 设置是否为mac认证
     * @param $data
     * @return bool
     */
    public static function setMacAuth($data)
    {
        $arr = [
            'action' => 2,
            'serial_code' => time() . rand(111111, 999999), //唯一的流水号
            'time' => time(),
            'proc' => 'admin',
            'user_name' => $data['user_name'],
            'mac_auth' => $data['value'],
        ];
        //print_r($arr);exit;
        $json = json_encode($arr);
        return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
    }

    /**
     * 取消产品 , 暂时没有这个接口的逻辑，接口不可用
     * @param $user_name string 用户名
     * @param $product_id int 产品id
     * @return bool
     */
    public static function cancelProduct($user_name, $product_id)
    {
        //取消产品
        $array = [
            'action' => 7,
            "user_name" => $user_name,
            "serial_code" => time() . rand(111111, 999999), //唯一的流水号
            "time" => time(),
            'proc' => 'admin',
            "products_id" => $product_id, //取消的产品
            "products_id_new" => -1,
        ];
        $json = json_encode($array);
        return Redis::executeCommand('RPUSH', "list:interface", [$json]);
    }

    /**
     * 修改产品实例的接口，实现对产品实例数据的修改。
     * 如 mobile_phone  mobile_password user_available
     * @param $user_name 用户名
     * @param $product_id 产品id
     * @param $array_key_values 要修改的字段 key=>value ...
     * @return bool|mixed
     */
    public static function setProduct($user_name, $product_id, $array_key_values)
    {
    	if(!is_array($array_key_values))
    		return false;
    	//取消产品
    	$array = [
    			'action' => 9,
    			"user_name" => $user_name,
    			"serial_code" => time() . rand(111111, 999999), //唯一的流水号
    			"time" => time(),
    			'proc' => 'admin',
    			"products_id" => $product_id //取消的产品
    	];
    	foreach($array_key_values as $k=>$v)
    	{
    		$array[$k] = $v;
    	}

    	$json = json_encode($array);
    	return Redis::executeCommand('RPUSH', "list:interface", [$json]);
    }

    /**
     * 加载IP @todo 需要发到每个分布式ip的16384端口去，设计不合理
     * @param $type string 类型，直通IP：direct，免费IP：free，默认直通IP
     * @return bool
     */
    public static function loadIP($type = 'direct')
    {
        $types = [
            'direct' => 101,
            'free' => 102,
        ];
        $id = isset($types[$type]) ? $types[$type] : 101;
        if (\Yii::$app->params['distribute_ip']) {
            //向每一个SERVER IP写入数据
            foreach (\Yii::$app->params['distribute_ip'] as $ip) {
                if ($ip == '') {
                    continue;
                }
                $redis = \Yii::createObject([
                    'class' => '\yii\redis\Connection',
                    'hostname' => $ip,
                    'port' => 16384,
                    'password' => \Yii::$app->params['redisConfig']['redis_password'],
                    'database' => 0,
                ]);
                $redis->executeCommand('RPUSH', ['list:distribute:' . $ip, "{\"action\":" . $id . "}"]);
                //Redis::executeCommand('RPUSH', 'list:distribute:'.$ip, ["{\"action\":".$id."}"], 'redis_cache');
            }
            return true;
        }
        return false;
    }

    /**
     * 产品转移，发送到接口
     * @param $user_name
     * @param $products_id
     * @param $products_id_new
     * @return mixed
     */
    public static function prodcutChange($user_name, $products_id, $products_id_new){
        $data = [
            'action' => 7,
            'serial_code' => time() . rand(111111, 999999),
            'time' => time(),
            'proc' => 'admin',
            'user_name' => $user_name,
            'products_id' => $products_id,
            'products_id_new' => $products_id_new,
        ];
        $json = json_encode($data);
        return Redis::executeCommand('RPUSH', 'list:interface', [$json]);
    }

    /**
     * 把用户的在线记录插入update队列中，触发策略变更
     * @param $user_name
     * @return bool
     */
    public static function onlineUpdateList($user_name){
        $res = false;
        $array = Redis::executeCommand('SMEMBERS', 'set:rad_online:user_name:' . $user_name, [], 'redis_online');
        foreach ($array as $rad_online_id) {
            if(!$rad_online_id){
                continue;
            }
            $my_ip = Redis::executeCommand('HGET', 'hash:rad_online:' . $rad_online_id, ['my_ip'], 'redis_online');
            if(!empty($my_ip))
            {
                $redis = "redis_". ip2long($my_ip); //为每个AAA生成一个REDIS句柄$$redis，并保持长连接
                if(!$$redis) //如果柄是空的就去连接，如果不是空的，就不需要再次连接，以节省TCP资源
                {
                    //连接到AAA的REDIS
                    $$redis = \Yii::createObject([
                        'class' => 'yii\redis\Connection',
                        'hostname' => $my_ip,
                        'port' => 16384,
                        'password' => \Yii::$app->params['redisConfig']['redis_password'],
                        'database' => 0,
                    ]);
                }
                if($$redis){
                    $res = $$redis->executeCommand('RPUSH', ['list:rad_online:update', "1-".$rad_online_id]);
                }
            }
        }
        return $res;
    }

    /**
     * 绑定CDR号码
     * @param $user_name
     * @param $val
     * @return bool
     */
    public static function CDRBind($user_name, $val){
        $key = Base::INTERFACE_NAME_KEY;
        $key_list_pre = Base::INTERFACE_NAME_List_KEY;
        //绑定interface_name到key-value
        //如果包含'/'，解析成ip段
        $val = trim($val);
        if(strpos($val, '/') !== false){
            $ips = Tool::ipMaskParams($val);
            if($ips){
                foreach($ips as $ip){
                    Redis::executeCommand('SET', $key.$ip, [$user_name]);
                }
            }
        }else{
            Redis::executeCommand('SET', $key.$val, [$user_name]);
        }

        //绑定interface_name到list
        Redis::executeCommand('RPUSH', $key_list_pre.$user_name, [$val]);
        return true;
    }

    /**
     * 删除CDR绑定号码
     * @param $user_name
     * @param $val
     * @return bool
     */
    public static function delCDRBind($user_name, $val){
        $key = Base::INTERFACE_NAME_KEY;
        $key_list_pre = Base::INTERFACE_NAME_List_KEY;
        //绑定interface_name到key-value
        Redis::executeCommand('DEL', $key.$val, [$user_name]);
        //绑定interface_name到list
        Redis::executeCommand('LREM', $key_list_pre.$user_name, [0, $val]);
        return true;
    }

    /**
     * 添加绑定发送消息通知
     * @param $data
     * @param string $type
     * @return mixed
     */
    public static function bindNotice($data, $type = 'device_bind'){
        $base = [
            'event_source' => SRUN_MGR,
            'event_type' => $type,
            '{ACCOUNT}' => '',
            '{DEVICE_NUM}' => 0,
            '{LAST_NUM}' => 0,
            '{MAC_ADDR}' => '',
            '{BIND_TIME}' => date('Y-m-d H:i:s'),
        ];
        $data = array_merge($base, $data);
        $data = json_encode($data);
        return Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }

    /**
     * 清除用户绑定的信息（MAC认证，MAC绑定，NAS PORT绑定，VLAN ID绑定，IPV4绑定）
     * @param $user_name
     * @param $type
     * @return bool
     */
    public static function clearBindInfo($user_name, $type){
        if(!in_array($type, ['mac_auth', 'mac', 'nas_port_id', 'vlan_id', 'ip'])){
            return false;
        }
        if($type == 'mac_auth')
        {
            $array = Redis::executeCommand('LRANGE', 'list:users:mac_auth:'.$user_name, [0, -1]);
            foreach($array as $mac)
            {
                Redis::executeCommand('DEL', 'key:users:mac_auth:'.$mac);
            }
            Redis::executeCommand('DEL', 'list:users:mac_auth:'.$user_name);
        }
        else
        {
            Redis::executeCommand('DEL', 'list:users:'.$type.':'.$user_name);
        }
        return true;
    }

    /**
     * 结算成功后触发预约策略
     * @param $user_name
     * @param array $params
     * @return mixed
     */
    public static function buyPackageByCheckout($user_name, $params = []){
        $data = [
            'event_source' => SRUN_CHECKOUT,
            'event_type' => 'user_checkout_success',
            '{ACCOUNT}' => $user_name,
        ];
        if(isset($params['product_id'])){
            $data['{PRODUCT_ID}'] = $params['product_id'];
        }
        $data = json_encode($data);
        return Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }

    /**
     * 结算完成后触发预约事件：产品余额不足月费用电子钱包来缴纳
     * @param $user_name
     * @param $product_id
     * @param $user_balance
     * @param array $params
     * @return mixed
     */
    public static function balanceToProduct($user_name, $product_id, $user_balance, $params = []){
        if(isset($params['product_balance'])){
            $product_balance = $params['product_balance'];
        }else{
            $uid = isset($params['user_id']) && $params['user_id'] ? $params['user_id'] : Redis::executeCommand('get', 'key:users:user_name:'.$user_name);
            $product_balance = Redis::executeCommand('hget', 'hash:users:products:'.$uid.':'.$product_id, ['user_balance']);
        }
        $fee = isset($params['fee']) ? $params['fee'] : Redis::executeCommand('hget', 'hash:products:'.$product_id, ['checkout_amount']);
        $data = [
            'event_source' => SRUN_CHECKOUT,
            'event_type' => 'user_checkout_success',
            '{ACCOUNT}' => $user_name,
            '{PRODUCT_BALANCE}' => $product_balance,
            '{FEE}' => $fee,
            '{USER_BALANCE}' => $user_balance,
        ];
        $data = json_encode($data);
        return Redis::executeCommand('RPUSH', 'list:message:main:events', [$data], 'redis_online');
    }
}