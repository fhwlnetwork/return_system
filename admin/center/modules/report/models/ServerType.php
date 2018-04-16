<?php

namespace center\modules\report\models;

use Yii;
use center\extend\Tool;
use center\modules\setting\models\Server;
use yii\db\Query;

/**
 * This is the model class for table "online_report_os_name".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property string $os_name
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class ServerType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting_server';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'Portal Server' => [
                'httpd_process_data' => Yii::t('app', 'httpd_process_data'),
                'system_load' => Yii::t('app', 'system_load'),
                'portal_server' => Yii::t('app', 'portal_server'),
            ],
            'Radiusd' => [
                'disk_io_status' => Yii::t('app', 'disk_io_status'),
                'system_status' => Yii::t('app', 'system status'),
                'radisud' => Yii::t('app', 'radisud'),
                'hard_disk_data' => Yii::t('app', 'hard_disk_data'),
            ],
            'AAA' => [
                'system_data' => Yii::t('app', 'system_data'),
                'data_acquisition' => Yii::t('app', 'data_acquisition'),
                'hard_disk_data' => Yii::t('app', 'hard_disk_data'),
                'redis_status' => Yii::t('app', 'redis_status'),
                'system_status' => Yii::t('app', 'system status'),
            ],
            'Msg' => [],
            'Mysql' => [
                'disk_io_status' => Yii::t('app', 'disk_io_status'),
                'system_status' => Yii::t('app', 'system status'),
                'mysqld' => Yii::t('app', 'mysqld'),
            ],
            'Redis' => [
                'disk_io_status' => Yii::t('app', 'disk_io_status'),
                'system_status' => Yii::t('app', 'system status'),
                'redis_status' => Yii::t('app', 'redis_status'),
            ],
            'Redis从' => [
                'disk_io_status' => Yii::t('app', 'disk_io_status'),
                'system_status' => Yii::t('app', 'system status'),
                'redis_status' => Yii::t('app', 'redis_status'),
            ],
            'Interface' => [
                'disk_io_status' => Yii::t('app', 'disk_io_status'),
                'system_status' => Yii::t('app', 'system status'),
                'redis_status' => Yii::t('app', 'redis_status')
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function findServerType()
    {
        $Server = new Server;
        $type = $Server->getAttributesType();
        $serverType = $type['devicename'];
        $dataArray = array();
        if (!empty($serverType)) {
            foreach ($serverType as $key => $value) {
                $server = $this->serverData($key);
                !empty($server) ? $dataArray[$value] = $server : '';
            }
        }

        return $dataArray;
    }

    public function serverData($type)
    {
        $query = new Query();
        $query->from('setting_server');
        $query->select(['id', 'ip', 'devicename', 'type']);
        $query->andWhere(['=', 'devicename', $type]);
        $data = $query->All();
        $array = array();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (strpos($value['type'], ',')) {
                    $type = explode(',', $value['type']);
                    if (is_array($type)) {
                        foreach ($type as $m) {
                            $value['type'] = $m;
                            $arrayStatus = $this->getStatus($m, $value);
                            $array[$value['type']][] = $value;

                        }
                    }
                }  else {
                    $array[$value['type']][] = $value;
                }
            }
        }


        return $array;
    }
    public function getStatus($type, $value)
    {

    }

    public function FindDebugData($data, $proc, $action)
    {
        $time = '';
        $ip = '';
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($value["proc"] == $proc && $value['action'] == $action) {
                    $time = $value['time'];
                    $ip = $value['my_ip'];
                }
            }
        }

        $bgcolor = $this->DataColor($time);
        return $source = [
            'ip' => $ip,
            'time' => $time,
            'bgcolor' => $bgcolor,
        ];
    }

    public function DataColor($time)
    {
        if ($time >= 80) {
            $color = 'bg-custom_chart';
        } else if ($time >= 50) {
            $color = 'bg-warning';
        } else if ($time == '') {
            $color = 'bg-gray';
        } else {
            $color = 'bg-primary';
        }
        return $color;
    }

    /**
     * 获取资源
     * @param $key
     * @param $ip
     * @return array
     */
    public function getSource($key, $ip)
    {
        $report = new DashboardReports();
        switch($key){
            case 'httpd_process_data':
                $source = $report->getProcess($ip);
                break;
            case 'system_load':
                $source = $report->getProcess($ip, 'loads');
                break;
            case 'portal_server':
                $source = $report->getPortalServer($ip);
                break;
            case 'disk_io_status':
                $source = $report->DiskIoCounters($ip);
                break;
            case 'system_status':
                $source = $report->getProcess($ip, 'mem');
                break;
            case 'radisud':
                //获取radiusd
                $source = $report->getRadiusdTime($ip);
                break;
            case 'mysqld':
                $source = $report->ProcessIoCounters('mysqld',$ip);
                break;
            case 'system_data':
                $source = $report->getProcess($ip, 'cpu');
                break;
            case 'data_acquisition':
                $source = $report->getAuthDetail($ip);
                break;
            case 'hard_disk_data':
                $source = $report->PartitionStatus($ip);
                break;
            case 'redis_status':
                $source = $report->ProcessIoCounters('redis-server',$ip);
                break;
            default:
                $source = array();
        }

        return $source;
    }

}
