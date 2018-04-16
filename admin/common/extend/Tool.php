<?php
/**
 * 公共的工具类
 * User: ligang
 * Date: 2015/5/11
 * Time: 13:28
 */

namespace common\extend;


class Tool
{
    public static function postData($url, $data)
    {
        $header = array( //'Content-Type: application/x-www-form-urlencoded',
        );
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置header
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch); //运行curl
        curl_close($ch);
        return $res; //输出结果
    }

    /**
     * 用file_get_content来请求接口
     * @param $url
     * @param $params
     * @param string $method
     * @return string
     */
    public static function postApi($url, $params, $method='POST'){
        $header = array(
            'Content-Type: application/x-www-form-urlencoded',
        );

        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置header
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //关闭 ssl 模块验证
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //在发起连接前等待的时间，如果设置为0，则无限等待。
        curl_setopt($ch, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出

        switch ($method) {
            case "GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "PUT" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
        }

        $res = curl_exec($ch); //运行curl
        curl_close($ch);
        return $res; //输出结果
    }

    /**
     * 获取查询数据.
     * @param $params
     * @param $query
     * @return bool
     */
    public static function getCount($params, $query)
    {
        if (empty($params) || empty($query)) {
            return false;
        }

        foreach ($query as $value) {
            foreach ($params as $val) {
                if (array_key_exists($val, $value)) {
                    $resArr[$val][] = $value[$val];
                }
            }
        }

        return $resArr;
    }

    /**
     * 根据开始时间 结束时间 单位 步长 对指定时间进行切割.
     * @param $start_At 开始时间
     * @param $stop_At 结束时间
     * @param $unit 单位
     * @param $step 步长
     * @return mixed array 返回值
     */
    public function substrTime($start_At, $stop_At, $unit, $step)
    {
        $units = $this->getUnitDate($unit);

        $count = ceil(($stop_At - $start_At) / ($step * $units)); //返回执行的次数

        for ($i = 0; $i <= $count; $i++) {
            $time_step[] = $start_At + $i * $step * $units;
        }

        return $time_step;
    }

    /**
     * 根据时间单位返回对应具体时间, 单位是秒
     * @param $unit
     * @return int
     */
    public function getUnitDate($unit)
    {
        if ($unit === 'minutes') {
            $units = 60;
        } elseif ($unit === 'hours') {
            $units = 60 * 60;
        } elseif ($unit === 'days') {
            $units = 60 * 60 * 24;
        } elseif ($unit === 'months') {
            $units = 60 * 60 * 24 * 30;
        } elseif ($unit === 'years') {
            $units = 60 * 60 * 24 * 30 * 12;
        }

        return $units;
    }

    /**
     * 根据时间单位 返回对应的秒数
     * @param $unit 时间单位
     * @return int 返回值
     */
    public static function getTimeDate($unit)
    {
        switch ($unit) {
            case 'minutes':
                return 60;
                break;
            case 'hours':
                return 60 * 60;
                break;
            case 'days':
                return 60 * 60 * 24;
                break;
            case 'months':
                return 60 * 60 * 24 * 30;
                break;
            case 'years':
                return 60 * 60 * 24 * 30 * 12;
                break;
            default:
                return 60;
        }
    }

    /**
     * 格式化时间, 主要面向对象为报表的 X 时间轴.
     * @param $units 时间单位
     * @param $xAxis x 轴数组
     * @return string 返回格式化后的时间字符串
     */
    public static function formatTime($units, $xAxis)
    {
        foreach ($xAxis as $val) {
            if ($units == 'minutes' || $units == 'hours') {
                $xAxisData[] = "'" . date('H:i', $val) . "'";
            } elseif ($units == 'days') {
                $xAxisData[] = "'" . date('m/d', $val) . "'";
            } elseif ($units == 'months') {
                $xAxisData[] = "'" . date('Y/m', $val) . "'";
            } else {
                $xAxisData[] = "'" . date('Y', $val) . "'";
            }
        }

        $xAxisString = implode(',', $xAxisData);
        return $xAxisString;
    }

    /**
     * 解析ip子网掩码如1.1.1.0/29， 生成ip数组
     * @param $ipaddr
     * @return array
     */
    public static function ipMaskParams($ipaddr){
        $ipAddrs = [];
        if(preg_match("/^([\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3})\/([\d]{1,2})$/", $ipaddr, $arr))
        {
            $ip = $arr[1];
            $mask = $arr[2];
            if($mask>=1 && $mask<=32){
                $maskBinStr =str_repeat("1", $mask ) . str_repeat("0", 32-$mask );      //net mask binary string
                $inverseMaskBinStr = str_repeat("0", $mask ) . str_repeat("1",  32-$mask ); //inverse mask

                $ipLong = ip2long( $ip );
                $ipMaskLong = bindec( $maskBinStr );
                $inverseIpMaskLong = bindec( $inverseMaskBinStr );
                $netWork = $ipLong & $ipMaskLong;

                $start = $netWork+1;//去掉网络号 ,ignore network ID(eg: 192.168.1.0)
                $end = ($netWork | $inverseIpMaskLong) -1 ; //去掉广播地址 ignore brocast IP(eg: 192.168.1.255)

                $ipAddrs = array();
                for ($num = $start; $num <= $end; $num++) {
                    $ipAddrs[] = long2ip($num);
                }
            }
        }
        return $ipAddrs;
    }

    /**
     * 将掩码位数生成子网掩码
     * @param $mask
     * @return string
     */
    public static function byteToMask($mask){
        $ip_mask = '';
        if($mask>=1 && $mask<=8){
            $ip_mask = (256-(1<<(8-$mask))).'.0.0.0';
        }elseif($mask>8 && $mask<=16){
            $ip_mask = '255.'.(256-(1<<(16-$mask))).'.0.0';
        }elseif($mask>16 && $mask<=24){
            $ip_mask = '255.255.'.(256-(1<<(24-$mask))).'.0';
        }elseif($mask>24 && $mask<=32){
            $ip_mask = '255.255.255.'.(256-(1<<(32-$mask)));
        }
        return $ip_mask;
    }

    /**
     * 生成随机数
     * @param int $len
     * @param string $format
     * @return string
     */
    public static function randpw($len=8,$format='ALL'){
        $is_abc = $is_numer = 0;
        $password = $tmp ='';
        switch($format){
            case 'ALL':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'CHAR':
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'NUMBER':
                $chars='0123456789';
                break;
            default :
                $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                break;
        }
        mt_srand((double)microtime()*1000000*getmypid());
        while(strlen($password)<$len){
            $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
            if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
                $is_numer = 1;
            }
            if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
                $is_abc = 1;
            }
            $password.= $tmp;
        }
        if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
            $password = self::randpw($len,$format);
        }
        return $password;
    }


    /**
     * 判断两个日期是否是在一个自然月
     * @param $start_time
     * @param $end_time
     * @return bool|false|string
     */
    public static function isNaturalMonth($start_time, $end_time){
        if(!empty($start_time) && !empty($end_time)){
            $start_month = date('Ym', strtotime($start_time));
            $end_month = date('Ym', strtotime($end_time));
            if($start_month !== $end_month){
                return false;
            }else{
                return $start_month;
            }
        }
        return false;
    }


    /**
     * 选择日志表表名
     * @param $start_time 时间格式：2017-06-02 or 2017-06-02 10:00:00
     * @param $end_time 时间格式：2017-06-02 or 2017-06-02 10:00:00
     * @param string $table
     * @return bool|string
     */
    public static function getPartitionTable($start_time, $end_time, $table = 'srun_detail'){
        $last_month_first_day = date('Y-m-01', strtotime('-1 month'));

        if(!empty($start_time)){//开始时间不为空
            if(!empty($end_time)){//结束时间不为空
                if($start_time >= $last_month_first_day && $end_time >= $last_month_first_day){
                    return $table;
                }
                $monthTable = self::isNaturalMonth($start_time, $end_time);
                if($monthTable){
                    return $table.'_'.$monthTable;
                }
            }else{//结束时间为空
                if($start_time >= $last_month_first_day){
                    return $table;
                }
            }
        }else{//开始时间为空
            if(empty($end_time)){//结束时间不为空
                return $table;
            }
        }

        return false;
    }
}