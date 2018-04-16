<?php
/**
 * 工具类
 */
namespace center\extend;

use yii;

class Tool
{
    const TRAFFIC_CARRY = 1024; //流量进位

    /**
     * 时间处理函数，有日期，时间，周
     * 日期分为 今天，昨天，前天，普通日期2015-01-20
     * 时间分为：在今天12小时内：xx秒前，xx分钟前，xx小时前，其他都显示时间格式：08:30:10
     * 周：直接显示周
     * @param $unixTime int 时间戳
     * @return string 时间
     */
    public static function showDateTime($unixTime)
    {
        return [
            'time' => self::showTime($unixTime),
            'day' => self::showDay($unixTime),
            'week' => self::showWeek($unixTime),
        ];
    }

    /**
     * 时间处理
     * 时间分为：在今天12小时内：xx秒前，xx分钟前，xx小时前，其他都显示时间格式：08:30:10
     * @param $unixTime
     * @return string
     */
    public static function showTime($unixTime)
    {
        $value = time() - $unixTime;
        //12小时内
        if ($value < 43200) {
            if ($value < 60) {
                $time = Yii::t('app', '{times} seconds age', ['times' => $value]); //$value.'秒前';
            } elseif ($value < 3600) {
                $min = floor($value / 60);
                $time = Yii::t('app', '{times} minutes ago', ['times' => $min]); //$min.'分钟前';
            } else {
                $h = floor($value / 3600);
                $time = Yii::t('app', '{times} hours ago', ['times' => $h]); //$h.'小时前';
            }
        } else {
            $time = date('H:i:s', $unixTime);
        }
        return $time;
    }

    /**
     * 格式化日期
     * @param $unixTime
     * @return bool|string
     */
    public static function showDay($unixTime)
    {
        $day = date('Y-m-d', $unixTime);
        $today = date('Y-m-d', time());
        //今天
        if ($day == $today) {
            return 'Today';
        } //昨天
        else if ($day == date('Y-m-d', strtotime('-1 day'))) {
            return 'Yesterday';
        }
        //前天
        /*else if($day == date('Y-m-d', strtotime('-1 day'))){
            return '前天';
        }*/
        //其他
        else {
            return $day;
        }

    }

    /**
     * 判断某一天是星期几
     * @param string $unixTime
     * @return string
     */
    public static function showWeek($unixTime = '')
    {
        $unixTime = is_numeric($unixTime) ? $unixTime : time();
        //$weekArray = array('日','一','二','三','四','五','六');
        return Yii::t('app', date('D', $unixTime));
    }

    /**
     * 按照当前页和页数 切割数组
     * @param $array
     * @param $currentPage
     * @param $pageSize
     * @return array
     */
    public static function cuttingArray($array, $currentPage, $pageSize)
    {
        $data = [];
        if (is_array($array)) {
            $total_num = count($array);
            $start = ($currentPage - 1) * $pageSize;
            if ($total_num < $currentPage * $pageSize) {
                $end = $total_num;
            } else {
                $end = $currentPage * $pageSize;
            }
            for ($i = $start; $i < $end; $i++) {
                array_push($data, $array[$i]);
            }
        }
        return $data;
    }

    /**
     * 判断一个多维数组中键或值是否存在某个值
     * @param $value
     * @param $array
     * @return bool
     */
    public static function array_key_value_exists($value, $array)
    {
        foreach ($array as $key => $item) {
            if ($key === $value) {
                return true;
            }
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if (in_array($value, $item) || array_key_exists($value, $item)) {
                return true;
            } else if (static::array_key_value_exists($value, $item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 格式化流量
     * @param $bytes int 字节
     * @return string
     */
    public static function bytes_format($bytes)
    {

        if ($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY) >= 1)
            return number_format($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY), 2) . "G";
        else if ($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY) >= 1)
            return number_format($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY), 2) . "M";
        else if (($bytes / 1000) >= 1)
            return number_format($bytes / self::TRAFFIC_CARRY, 2) . "KB";
        else
            return $bytes . "B";
    }

    /**
     * 格式化时间
     * @param $second int 秒
     * @return string
     */
    public static function seconds_format($second)
    {
        $h = floor($second / 3600);
        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);
        $out = "";
        if ($h > 0)
            $out = number_format($h, 0) . Yii::t('app', 'hours') . $m . Yii::t('app', 'minutes') . $s . Yii::t('app', 'seconds');
        else if ($m > 0)
            $out = $m . Yii::t('app', 'minutes') . $s . Yii::t('app', 'seconds');
        else
            $out = $s . Yii::t('app', 'seconds');
        return $out;
    }

    /**
     * 格式化时间
     * @param $second int 秒
     * @return string
     */
    public static function dates_format($second)
    {
        $d = floor($second / 3600/24);
        $h = floor($second % 24/ 3600);

        $m = floor(($second % 3600) / 60);
        $s = floor(($second % 3600) % 60);
        $out = "";
        if ($d > 0)
            $out = number_format($d, 0) . Yii::t('app', 'days') .number_format($h, 0) . Yii::t('app', 'hours') . $m . Yii::t('app', 'minutes') . $s . Yii::t('app', 'seconds');
        else if ($h > 0)
            $out = number_format($h, 0) . Yii::t('app', 'hours') . $m . Yii::t('app', 'minutes') . $s . Yii::t('app', 'seconds');
        else if ($m > 0)
            $out = $m . Yii::t('app', 'minutes') . $s . Yii::t('app', 'seconds');
        else
            $out = $s . Yii::t('app', 'seconds');
        return $out;
    }
    /**
     * 格式化金额
     * @param $money
     * @return string
     */
    public static function money_format($money)
    {
        return number_format($money, 2);
    }

    /**
     * 金额转换成中文大写
     * @param $data
     * @return bool|string
     */
    public static function num2zh($data) // 金额转换成中文大写
    {
        $capnum = array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖");
        $capdigit = array("", "拾", "佰", "仟");
        $subdata = explode(".", $data);
        $yuan = $subdata[0];
        $j = 0;
        $nonzero = 0;
        for ($i = 0; $i < strlen($subdata[0]); $i++) {
            if (0 == $i) { //确定个位
                if (isset($subdata[1])) {
                    $cncap = (substr($subdata[0], -1, 1) != 0) ? "元" : "元";
                } else {
                    $cncap = "元";
                }
            }
            if (4 == $i) {
                $j = 0;
                $nonzero = 0;
                $cncap = "万" . $cncap;
            } //确定万位
            if (8 == $i) {
                $j = 0;
                $nonzero = 0;
                $cncap = "亿" . $cncap;
            } //确定亿位
            $numb = substr($yuan, -1, 1); //截取尾数
            $cncap = ($numb) ? $capnum[$numb] . $capdigit[$j] . $cncap : (($nonzero) ? "零" . $cncap : $cncap);
            $nonzero = ($numb) ? 1 : $nonzero;
            $yuan = substr($yuan, 0, strlen($yuan) - 1); //截去尾数
            $j++;
        }
        $chiao = $cent = "";
        $zhen = "整";
        if (intval($subdata[1]) > 0) {
            $chiao = (substr($subdata[1], 0, 1)) ? $capnum[substr($subdata[1], 0, 1)] . "角" : "零";
            $cent = (substr($subdata[1], 1, 1)) ? $capnum[substr($subdata[1], 1, 1)] . "分" : "零分";
            $zhen = "";
        } else {
            $chiao = "";
            $cent = "";
            $zhen = "整";
        }
        $cncap .= $chiao . $cent . $zhen;
        $cncap = preg_replace("/(零)+/", "\\1", $cncap); //合并连续"零"
        return $cncap;
    }

    /**
     * 截取中文字符
     * @param $string
     * @param $length
     * @param string $etc
     * @return string
     */
    public static function truncate_utf8_string($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++) {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')) {
                if ($length < 1.0) {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            } else {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen) {
            $result .= $etc;
        }
        return $result;
    }

    /**
     * 将MAC转为标准格式
     * @param $user_mac
     * @return string
     */
    public static function format_mac($user_mac)
    {
        $mac = preg_replace("/[\.|:|\-]/", "", $user_mac);
        $mac1 = array();
        $n = 0;
        for ($i = 0; $i < strlen($mac); $i += 2) {
            $mac1[$n] = $mac[$i] . $mac[$i + 1];
            $n++;
        }
        return implode(":", $mac1);
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
     * 按字母顺序排序
     * @param $params
     * @return array
     */
    public static function sortAlphabetically($params)
    {
        $sort = [];
        for ($i = 65; $i < 91; $i++) {
            $sort[] = strtoupper(chr($i));
        }

        if (!$params) {
            return [];
        }

        foreach ($params as $value) {
            $subStr = substr($value[0], 0, 1);
            if ($subStr == '<' || $subStr == '{') {
                $subStr = substr($value[0], 1, 1);
            } else {
                $subStr = substr($value[0], 0, 1);
            }

            //返回key值
            $keys = array_keys($sort, strtoupper($subStr));

            if ($keys) {
                $variables[$keys[0]][] = $value;
            } else {
                $variables[][] = $value;
            }
        }

        //自然顺序排序
        ksort($variables);

        //排序后的变量数组
        $variablesNew = [];
        //二维数组转一维数组
        foreach ($variables as $value) {
            if (is_array($value)) {
                foreach ($value as $values) {
                    $variablesNew[] = $values;
                }
            } else {
                $variablesNew[] = $value;
            }
        }
        return $variablesNew;
    }

    /**
     * 格式化流量，统一为指定的单位
     * @param $bytes int 字节
     * @param string $unit 指定的单位 G/M/KB
     * @return string
     */
    public static function bytes_format_unit($bytes, $unit = 'M')
    {
        if($unit == 'G'){
            return sprintf("%.2f", floatval($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY * self::TRAFFIC_CARRY))) . "G";
        }elseif($unit == 'M'){
            return sprintf("%.2f", floatval($bytes / (self::TRAFFIC_CARRY * self::TRAFFIC_CARRY))) . "M";
        }elseif($unit == 'KB'){
            return sprintf("%.2f", floatval($bytes / self::TRAFFIC_CARRY)) . "KB";
        }else{
            return $bytes . "B";
        }
    }

    /**
     * 策略条件中过滤特殊字符
     * @param $str
     * @return mixed
     */
    public static function filterSpeCharacter($str){
        return preg_replace('/[\\r|\\n|@|#|$|%|^|&|\*|\\\]+/', '', $str);
    }

}