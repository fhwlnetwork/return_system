<?php
namespace common\models;

use yii;

class Redis
{
	/**
	 * 执行redis命令
	 * @param $command string 命令
	 * @param $key string key
	 * @param $data array 数据数组
	 * @param $redis string redis组件id
	 * @return mixed
	 */
	public static function executeCommand($command, $key, $data = [], $redis = 'redis_user')
	{
		array_unshift($data, $key);
		return Yii::$app->$redis->executeCommand($command, $data);
	}

	/**
	 * 将数组转换为hash需要的格式；比如将[key=>value] 数组转化成 [$key, $value] 形式
	 * @param $data
	 * @return array
	 */
	public static function arrayToHash($data)
	{
		$commandArr = [];
		if (is_array($data)) {
			foreach ($data as $k => $v){
				$commandArr[] = $k;
				$commandArr[] = $v;
			}
		}
		return $commandArr;
	}

	/**
	 * 将hash转换为数组格式；比如['id','2','name','10元包月']转换为['id'=>'2', 'name'=>'10元包月']
	 * @param $element array hash元素
	 * @return array
	 */
	public static function hashToArray($element)
	{
		$elementArray = [];
		foreach ($element as $k => $v) {
			if ($k > 0 && $k % 2 != 0) {
				$elementArray[$element[$k - 1]] = $v;
			}
		}
		return $elementArray;
	}

}