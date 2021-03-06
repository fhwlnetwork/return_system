<?php

namespace center\modules\report\models;

use Yii;

/**
 * This is the model class for table "online_product_user".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $product_name
 * @property integer $user_id
 * @property string $user_name
 * @property string $product_status
 * @property string $cert_type
 * @property string $cert_num
 * @property string $date
 * @property string $mobile_phone
 */
class OnlineProductUser extends \yii\db\ActiveRecord
{

    public $start_At; //开始时间
    public $stop_At; //截止时间
    public $showField; //相关的产品信息

    public function getShowField(){
        return $this->showField;
    }

    public function setShowField($showField){
        $this->showField = $showField;
    }

    public function getStopAt(){
        return $this->stop_At;
    }

    public function setStopAt($stop_At){
        $this->stop_At = $stop_At;
    }

    public function getStartAt(){
        return $this->start_At;
    }

    public function setStartAt($start_At){
        $this->start_At = $start_At;
    }

    /**
     * 设置场景
     *
     * */
    public function scenarios()
    {
        $scenarios =  parent::scenarios(); // TODO: Change the autogenerated stub
        $scenarios['user-interval'] = ['start_At','stop_At','showField'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_product_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id','date', 'product_name', 'user_id','mobile_phone', 'product_status'], 'required'],
            [['product_id', 'user_id'], 'integer'],
            [['product_name', 'user_name', 'cert_type', 'cert_num'], 'string', 'max' => 64],
            [['product_status'], 'string', 'max' => 2],
            [['start_At','stop_At','showField'],'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'product_status' => 'Product Status',
            'cert_type' => 'Cert Type',
            'cert_num' => 'Cert Num',
            'mobile_phone' => 'Mobile Phone',
        ];
    }


    /**
     * 获取一段时间的内的产品的总人数
     *
     * */
    public function getIntervalUserNumber($excel=false){
        $startTime = strtotime($this->start_At);
        $stopTime = strtotime($this->stop_At);
        $query = new \yii\db\Query();
        $query->select(['product_name','product_id','count(distinct user_id) as user_amount']);
        $query->from(self::tableName());
        $query->where(['>=','date',$startTime]);
        $query->andWhere(['<=','date',$stopTime]);
        $query->andWhere(['product_status'=>1]);
        $query->andFilterWhere(['in','product_id',$this->showField]);
        $query->groupBy(['product_id']);
        $query->indexBy('product_id');
        $dataActive = $query->all();
        $query = new \yii\db\Query();
        $query->select(['product_name','product_id','count(distinct user_id) as user_amount']);
        $query->from(self::tableName());
        $query->where(['>=','date',$startTime]);
        $query->andWhere(['<=','date',$stopTime]);
        $query->andWhere(['product_status'=>0]);
        $query->andFilterWhere(['in','product_id',$this->showField]);
        $query->groupBy(['product_id']);
        $query->indexBy('product_id');
        $data = $query->all();
        if($dataActive){
            $legend = [\Yii::t('app','abnormal_amount'),\Yii::t('app','normal_amount')];
            $temp = [];
            $xAxis = [];
            $tableData = [];
            if($excel){
                foreach ($dataActive as $key => $value){
                    $unNormal = $data[$key]['user_amount']?$data[$key]['user_amount']:0;
                    $tableData[] = [
                         $value['product_name'],
                         $value['user_amount'],
                         $unNormal];
                }
                $tableHeader = [Yii::t('app','product_name'),Yii::t('app','normal_amount'),Yii::t('app','abnormal_amount')];
                array_unshift($tableData,$tableHeader);
                $result['data'] = $tableData;
                $result['title'] = Yii::t('app','Product User Amount');
                $result['file'] = $result['title'].'.xls';
                return $result;
            }
            foreach ($dataActive as $key => $value){
                $unNormal = $data[$key]['user_amount']?$data[$key]['user_amount']:0;
                $temp['abnormal_amount'][] = $unNormal;
                $temp['normal_amount'][] = $value['user_amount'];
                $xAxis[] = $value['product_name'];
                $tableData[] = [
                    'product_name' => $value['product_name'],
                    'normal_amount' => $value['user_amount'],
                    'abnormal_amount' => $unNormal];
            }
            $obj = new \stdClass();
            $obj->name = \Yii::t('app','normal_amount');
            $obj->type = 'bar';
            $obj->data = $temp['normal_amount'];
            $unable = new \stdClass();
            $unable->name = \Yii::t('app','abnormal_amount');
            $unable->type = 'bar';
            $unable->data = $temp['abnormal_amount'];
            //返回统计图数据
            $result['xAxis'] = $xAxis;
            $result['series'] = [$unable,$obj];
            $result['legend'] = $legend;
            $result['table'] = $tableData;
            return $result;
            //返回统计表数据

        }else{
            return [];
        }
    }


    /**
     * 获取数据包装结果
     *
     * */
    public function getSeries($type,$stack = false,$data){
        $result = [];
        //循环构造结果集数据
        foreach ($data as $key => $value){
            $object = new \stdClass();
            $object->type = $type;
            $object->name = \Yii::t('app',$key);
            $object->data = $value;
            $result[] = $object;
        }
        return $result;
    }


    /**
     * 获取对应的详细数据
     * */
    public function getProductUserExcel($productId){
        $date = strtotime($this->date);
        $query = new \yii\db\Query();
        $query->select(['product_name','product_id','user_id','user_name','product_status','cert_type','cert_num','mobile_phone']);
        $query->from(self::tableName());
        $query->where(['date'=>$date]);
        $query->andWhere(['product_id'=>$productId]);
        $data = $query->all();
        if($data){
            $result = [];
            foreach ($data as $key => $value){
                $temp = array_values($value);
                $result[] = $temp;
                unset($temp);
            }
            return $result;
        }
        return false;
    }
}
