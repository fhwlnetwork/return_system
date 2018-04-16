<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/10/20
 * Time: 15:23
 */

namespace center\extend;


use yii\log\FileTarget;
use common\models\Redis;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Request;
use yii\log\Logger;
use yii;
use yii\helpers\FileHelper;

class Log extends FileTarget
{
    const LIST_KEY = 'list:exception:8080';
    const HASH_EXCEPTION_KEY = 'exception:count:8080:%s';
   
    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }

        $prefix = $this->getMessagePrefix($message);
        $listKey = self::LIST_KEY;
        $lists = Redis::executeCommand('lrange', $listKey, [0,-1], 'redis_cache');
        if (!in_array($category, $lists)) {
            Redis::executeCommand('rpush', $listKey, [$category], 'redis_cache');
        }
        $hashKey = sprintf(self::HASH_EXCEPTION_KEY, $category);
        $flag = Redis::executeCommand('exists', $hashKey, [], 'redis_cache');
        if ($flag) {
            Redis::executeCommand('hincrby', $hashKey, ['count', 1], 'redis_cache');
        } else {
            Redis::executeCommand('hset', $hashKey, ['count', 1], 'redis_cache');
        }
        $array = [
           'category' => $category,
            'time_point' => $timestamp,
            'type' => 8080
        ];
        Yii::$app->db->createCommand()->insert('error_category_count', $array)->execute();


        return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text"
        . (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
    }

}