<?php
namespace center\modules\user\models;

use center\modules\auth\models\SrunJiegou;
use center\modules\log\models\LogWriter;
use common\models\KernelInterface;
use common\models\Redis;
use common\models\User;
use yii;
use yii\helpers\Json;


class Building extends yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'tj_campus_building';
    }

    public function rules()
    {

        return [
            [['id', 'name', 'type', 'parent', 'create_at'], 'safe'],
        ];
    }



    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'id'),
            'name' => Yii::t('app', 'Address Name'),
            'type' => Yii::t('app', 'type'),
            'parent' => Yii::t('app', 'parent'),
            'create_at' => Yii::t('app', 'create_at'),
        ];
    }

    /**
     * 翻译用户地址
     * @param $address
     * @return array
     */
    public static function getAddress($address)
    {
		if(!empty($address)){
			$address  = explode('.',$address);
			$data = array_pop($address);
			if(is_array($address)){
				$string = '';
				foreach($address as $key=>$value){
					$AddressMessage = Building::find()->where(['id'=>$value])->asArray()->one();
					if(!empty($AddressMessage)){
						$string.=$AddressMessage['name'];
					}
				}
				if(!empty($data)){
					$string.=$data.Yii::t('app', 'strategy_Building_font1');
				}
				return $string;
			}
		}
		
        return $address;
    }



}