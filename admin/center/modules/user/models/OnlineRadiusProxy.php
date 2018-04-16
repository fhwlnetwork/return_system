<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/14
 * Time: 16:55
 */

namespace center\modules\user\models;

use common\models\Redis;
use Yii;
use yii\db\ActiveRecord;

class OnlineRadiusProxy extends ActiveRecord
{

    //默认要显示的字段
    public $defaultField = ['user_name', 'add_time', 'ip', 'user_mac', 'nas_ip', 'nas_port', 'vlan_id','bytes_in','bytes_out'];
	
	//记录数
	public $sum = 0;

    public static function tableName()
    {
        return 'online_radius_proxy';
    }
	
	public function getSum()
	{
		return $this->sum;
	}

    //拖拽显示并排序的字段
    private $_searchFiled = null;

    public function getSearchField()
    {
        if(!is_null($this->_searchFiled)){
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'session_id' => Yii::t('app', 'session id'),
            'user_name' => Yii::t('app', 'account'),
			'add_time' => Yii::t('app', 'add time'),
            'domain' => Yii::t('app', 'domain'),
            'uid' => Yii::t('app', 'user id'),
            'ip' => Yii::t('app', 'user ip'),
            'ip6' => Yii::t('app', 'user ip6'),
            'user_mac' => Yii::t('app', 'user mac'),
            'nas_ip' => Yii::t('app', 'nas ip'),
            'nas_ip1' => Yii::t('app', 'nas ip1'),
            'nas_port' => Yii::t('app', 'nas port'),
            'nas_port_id' => Yii::t('app', 'nas port id'),
            'station_id' => Yii::t('app', 'station id'),
            'filter_id' => Yii::t('app', 'filter id'),
            'pbhk' => Yii::t('app', 'pbhk'),
            'vlan_id1' => Yii::t('app', 'vlan id1'),
            'vlan_id2' => Yii::t('app', 'vlan id2'),
            'vlan_id' => Yii::t('app', 'vlan id'),
			'bytes_in'=> Yii::t('app', 'bytes in'),
			'bytes_out'=> Yii::t('app', 'bytes out'),
        ];
        return $this->_searchFiled;
    }

    public function setSearchField($data){
        $this->_searchFiled = $data;
    }

    //要搜索的字段
    //private $_searchInput = ['user_name', 'ip', 'user_mac', 'bytes_in', 'bytes_out', 'add_time'];

    public function getSearchInput()
    {
        return [
            'user_name' => [
                'label' => Yii::t('app', 'account')
            ],
            'ip' => [
                'label' => Yii::t('app', 'user ip')
            ],
        ];
    }
	
	//从REDIS中获取在线列表
	public function getProxyList($page=1, $offset=20)
	{
		$array = Redis::executeCommand('LLEN', 
									 'list:proxy', 
									 [], 
									 'redis_online');


		$this->sum = $array;
        if ($page < 1) {
            $page = 1;
        }
        if ($page > ceil($this->sum / $offset)) {
            $page = ceil($this->sum / $offset);
        }
		return Redis::executeCommand('LRANGE', 
									 'list:proxy', 
									 array((($page-1)*$offset),(($page-1)*$offset+$offset-1)), 
									 'redis_online');
	}
	
	public function getProxyListByUserName($user_name, $page =1, $size = 20)
	{
		$array = Redis::executeCommand('SMEMBERS', 
									 'set:proxy:user_name:'.$user_name, 
									 [], 
									 'redis_online');
		$this->sum = count($array);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > ceil($this->sum / $size)) {
            $page = ceil($this->sum / $size);
        }
        $data = array_slice($array, ($page-1)*$size, $size);

		return $data;
	}
	
	public function getProxyListByIp($ip)
	{
		$this->sum = 1;
		return Redis::executeCommand('GET', 
									 'key:proxy:ip'.$ip.":0", 
									 [], 
									 'redis_online');
	}
	
	

    /**
     * 根据rad_online_id和字段名获取此字段的值
     * @param $rad_online_id
     * @param $field
     * @return mixed
     */
    public function getValueInRedis($rad_online_id, $field = null)
    {
        if($field){
            return Redis::executeCommand('HGET', 'hash:proxy:'.$rad_online_id, [$field], 'redis_online');
        }else{
            $res = Redis::executeCommand('HGETALL', 'hash:proxy:'.$rad_online_id, [], 'redis_online');
            return Redis::hashToArray($res);
        }

    }

}