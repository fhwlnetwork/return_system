<?php
namespace center\modules\report\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use center\modules\report\models\ServerType;
use center\modules\report\models\DashboardReports;

class Efficiency extends Model
{
	

    public function CountAttribute()
    {
        return [
            'start_response_time' => 'start_response_time',
            'update_response_time' => 'update_response_time',
            'stop_response_time' => 'stop_response_time',
            'auth_response_time' => 'auth_response_time',
        ];
    }	
	
	public static function Checktype($type,$ip){	
		$model = new Efficiency();
		$reports = new DashboardReports();
		$ServerType = new ServerType();
		$server = $ServerType->attributeLabels();
		$errorData = array();
		$i = 0;
        if(isset($server["{$type}"]) && is_array($server["{$type}"]) && $server["{$type}"]){
            foreach($server["{$type}"] as $key=>$value){
                $dataStatus = $reports::dataStatus($key,$ip);
                if(($dataStatus['color'] == 'bg-danger') || ($dataStatus['color'] == 'bg-warning')){
                    $errorData[] = $key;
                    $i++;
                }
            }
        }

		$result = $model::searchStatus($i,3,1);
		$result['error'] = implode(',',$errorData);
		return $result;
	}
	/*
	** 检测数据是否异常
	*/	
	public static function CheckStatusAuth($ip){
		$query = new Query();
		$nowTim = time();
		$endTim = time()-7200;		
		$query->from('efficiency_report');
		$query->select(['start_response_time','update_response_time','stop_response_time']);	
		$query->distinct();				
		$query->andWhere(['=', 'proc', 'rad_auth']);
		$query->andWhere(['=', 'my_ip', $ip]);
		$query->andWhere(['<=', 'time_point', $nowTim]);
		$query->andWhere(['>=', 'time_point', $endTim]);
		$data = $query->All();
		$result = array();
		if(!empty($data)){
			foreach($data as $key=>$value){
				$value = array_values($value);
				sort($value);
				$max = array_pop($value);
				$result[] = $max;
			}
		}
		sort($result);
		$end = array_pop($result);
		return $end;	
	}
	/*
	** 检测数据是否异常
	*/	
	public static function CheckStatusPortal($ip){
		$query = new Query();	
		$query->from('efficiency_report');
		$query->select(['auth_response_time']);				
		$query->andWhere(['=', 'proc', 'srun_portal_server']);
		$query->andWhere(['=', 'my_ip', $ip]);
		$query->orderBy('time_point desc');	
		$data = $query->One();
		$result = 0;
		if(!empty($data)){
				$result = $data['auth_response_time'];
		}
		return $result;
	}	
	
	/*
	** 检测数据是否异常
	*/	
	public static function CheckStatusRadiusd($ip){
		$query = new Query();
		$nowTim = time();
		$endTim = time()-7200;		
		$query->from('efficiency_report');
		$query->select(['auth_response_time']);
		$query->distinct();
		$query->andWhere(['=', 'proc', 'radiusd']);
		$query->andWhere(['=', 'my_ip', $ip]);
		//$query->andWhere(['<=', 'time_point', $nowTim]);
		//$query->andWhere(['>=', 'time_point', $endTim]);		
		$query->orderBy('time_point desc');
        $data = $query->one();
		$result = array();
		/*if(!empty($data)){
			foreach($data as $key=>$value){
				$result[] = $value['auth_response_time'];
			}
		}
		sort($result);
		$end = array_pop($result);*/
        $end = $data['auth_response_time'];
		return $end;
	}
	
	public static function searchStatus($data,$larger,$less){

		$result = [];
		if($data >= $larger){
			$result['color'] = 'bg-danger';
			$result['text'] = Yii::t('app', 'danger');	
			$result['icon'] = 'fa fa-times';
		}else if($data<$larger && $data >= $less){
			$result['color'] = 'bg-warning';
			$result['text'] = Yii::t('app', 'warning');		
			$result['icon'] = 'fa fa-exclamation';
		}else{
			$result['color'] = 'bg-success';
			$result['text'] = Yii::t('app', 'normal');
			$result['icon'] = 'fa fa-check';
		}
		return $result; 
	}
	/*
	** 设备IP
	*/
	public static function EfficiencyData(){
		
		$query = new Query();
		$nowTim = time();
		$endTim = time()-7200;		
		$query->from('efficiency_report');
		$query->select(['my_ip']);	
		$query->distinct();				
		$query->andWhere(['<=', 'time_point', $nowTim]);
		$query->andWhere(['>=', 'time_point', $endTim]);		
		$deviceip = $query->All();	

 		$model = new Efficiency();
		$resData = array();
		if(!empty($deviceip)){
			foreach($deviceip as $key=>$value){
				$resData[$value['my_ip']]= $model::DeviceData($value['my_ip']);
			}
		}
		return $resData;		
	}	

	/*
	** 根据设备ip取出数据
	*/
	public static function DeviceData($param){

		$result = array();
		$model = new Efficiency();	
		$result['maxData'] = $model::DeviceDataMax($param);
		return $result;
	}
	/*
	** 根据设备ip取出所有的数据
	*/	
	public static function DeviceDataAll($param){
		//查询要查询的进程
		$query = new Query();
		$nowTim = time()-7200;
		$query->from('efficiency_report');
		$query->select(['proc']);
		$query->andWhere(['>=', 'time_point', $nowTim]);		
		$query->andWhere(['=', 'my_ip', $param]);		
		$query->distinct();		
		$dataRes = $query->All();	
		$model = new Efficiency();
		$ResultData = array();
		if(!empty($dataRes)){
			foreach($dataRes as $key=>$value){
				$ResultData[]= $model::ProData($value['proc'],$param);
			}
		}
		return $ResultData;
	}	
	
	/*
	** 根据设备ip取出最大值
	*/	
	public static function ProData($pro,$ip){	
		$model = new Efficiency();
		$query = new Query();	
		$nowTim = time();
		$endTim = time()-7200;
		$query->from('efficiency_report');
		$query->select(['start_response_time','update_response_time','stop_response_time','auth_response_time','time_point']);
		$query->andWhere(['=', 'proc', $pro]);
		$query->andWhere(['=', 'my_ip', $ip]);
		$query->andWhere(['<=', 'time_point', $nowTim]);
		$query->andWhere(['>=', 'time_point', $endTim]);	
		$query->orderBy('time_point asc');
		$yAxisAllData = $query->all();		
		$StatusLables = $model->CountAttribute();

		$sendArray = array();
		$xAxis = array();
		$legend = array();	
		$dataString = array();		
		if(!empty($yAxisAllData)){
			foreach($yAxisAllData as $m=>$n){
					$xAxis[]= "'".date('H:i',$n['time_point'])."'";
					$sendArray['start_response_time'][] = sprintf("%.2f",$n['start_response_time']);
					$sendArray['update_response_time'][] = sprintf("%.2f",$n['update_response_time']);				
					$sendArray['stop_response_time'][] = sprintf("%.2f",$n['stop_response_time']);				
					$sendArray['auth_response_time'][] = sprintf("%.2f",$n['auth_response_time']);				
			}		

			foreach($sendArray as $m=>$n){
				$legend[]= "'".$StatusLables[$m]."'";
				$data = implode(',',$n);
				$dataString[]="{
						name: '".$StatusLables[$m]."',
						type: 'line',
						smooth:true,
						itemStyle: {normal: {areaStyle: {type: 'default'}}},					
						data: [".$data."]
						}";
			}
		}
		$xAxis = implode(',',$xAxis);
		$legend = implode(',',$legend);
		$dataString = implode(',',$dataString);
        $source = [
            'xAxis' => $xAxis,
            'legend' => $legend,
            'dataString' => $dataString,
            'NetStatusname' => $ip.'：'.$pro,
        ];
		return $source;	
	}
	/*
	** 根据设备ip取出最大值
	*/	
	public static function DeviceDataMax($ip){
		$arr = array();
		$query = new Query();
		$query->from('efficiency_report');
		$query->select(['auth_response_time']);
		$query->andWhere(['=', 'proc', 'srun_portal_server']);
		$query->andWhere(['=', 'my_ip', $ip]);			
		$query->orderBy('time_point desc');		
		$data = $query->one();	
		
		$query2 = new Query();
		$query2->from('efficiency_report');
		$query2->select(['start_response_time','update_response_time','stop_response_time']);
		$query2->andWhere(['=', 'proc', 'rad_auth']);
		$query2->andWhere(['=', 'my_ip', $ip]);		
		$query2->orderBy('time_point desc');
		$data2 = $query2->one();	

		$query3 = new Query();
		$query3->from('efficiency_report');
		$query3->select(['start_response_time','update_response_time','stop_response_time','auth_response_time']);
		$query3->andWhere(['=', 'proc', 'radiusd']);	
		$query3->andWhere(['=', 'my_ip', $ip]);		
		$query3->orderBy('time_point desc');
		$data3 = $query3->one();
		
		!empty($data) ? $arr['auth_response_time'] = sprintf("%.2f",$data['auth_response_time']) : '';
		!empty($data2) ? $arr['start_response_time'] = sprintf("%.2f",$data2['start_response_time']) : '';
		!empty($data2) ? $arr['update_response_time'] = sprintf("%.2f",$data2['update_response_time']) : '';
		!empty($data2) ? $arr['stop_response_time'] = sprintf("%.2f",$data2['stop_response_time']) : '';
		!empty($data3) ? $arr['radiusd_start'] = sprintf("%.2f",$data3['start_response_time']) : '';
		!empty($data3) ? $arr['radiusd_update'] = sprintf("%.2f",$data3['update_response_time']) : '';
		!empty($data3) ? $arr['radiusd_stop'] = sprintf("%.2f",$data3['stop_response_time']) : '';
		!empty($data3) ? $arr['radiusd_auth'] = sprintf("%.2f",$data3['auth_response_time']) : '';

		return $arr;
	}	
}