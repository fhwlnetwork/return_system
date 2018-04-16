<?php
/**
 * Created by PhpStorm.
 * User: cyc
 * Date: 15-7-24
 * Time: 上午10:48
 */

namespace center\modules\report\models;
use center\modules\report\models\OnlineReportClassName;
use yii;

class Report extends \yii\db\ActiveRecord
{

    public static function computingTime($time)
    {
		$unit = 'minutes';
		$step = 5;
		switch($time){
			case 'Yesterday':
				$start_At = date('Y-m-d H:i',strtotime(date('Y-m-d 00:00'))-(3600*24));
				$stop_At = date('Y-m-d H:i',strtotime(date('Y-m-d 23:59'))-(3600*24));
			break;					
			case 'week':
				$start_At = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
				$stop_At = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
			break;			
 			case 'lastweek':
				$start_At = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y"))); 
				$stop_At = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y"))); 			
			break;			
			default:
				$start_At = date('Y-m-d H:i',strtotime(date('Y-m-d 00:00')));
				$stop_At = date('Y-m-d H:i',strtotime(date('Y-m-d 23:59')));
			break;
		}  

		return $times=['unit'=>$unit,'step'=>$step,'start_At'=>$start_At,'stop_At'=>$stop_At];
    }	

}