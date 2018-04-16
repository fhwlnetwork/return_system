<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/3/20
 * Time: 13:50
 */

namespace center\modules\user\models;

use yii;
use center\modules\strategy\models\Strategy;
use center\modules\setting\models\ExtendsField;
use center\modules\strategy\models\Condition;
use center\modules\financial\models\PayType;
use center\modules\strategy\models\Product;
use common\models\User;

class Template extends Strategy
{
    //hash的key前缀
    public $hashKeyPre = 'hash:user:template:';
    //list的key
    public $listKey = 'list:user:template';
    //自增id的key
    public $idKey = 'key:user:template:id';
    //模板id
    public $id;
    //模板名称
    public $name;
    //模板创建者
    public $create;
    //模板内容
    public $content;
    //模板类型
    public $type;
    //是否允许修改密码
    public $user_allow_chgpass;
    //用户状态
    public $user_available;
    //过期时间
    public $user_expire_time;
    //用户组id
    public $group_id;
    //证件类型
    public $cert_type;
    //用户类型
    public $user_type;
    //产品id
    public $products_id;
    //证件号码
    public $cert_num;


    public function rules()
    {
        //获取扩展字段的必填项
        $mustField = [];
        foreach (ExtendsField::getAllData() as $one) {
            if ($one['is_must'] == 1) {
                $mustField[] = $one['field_name'];
            }
        }
    //var_dump(ExtendsField::getAllData());die;
        return [
            ['name', 'string', 'length' => [1, 64]],
            ['name', 'string', 'length' => [1, 64],'on'=>'update'],
            ['name', 'required', 'on'=>'update'],
            ['products_id', 'productsIdMust'],
            ['products_id', 'productsIdMust','on'=>'update'],
            ['products_id', 'required','on'=>'update'],
            [['user_expire_time'], 'default', 'value' => 0],
            [['user_expire_time'], 'default', 'value' => 0,'on'=>'update'],
            [$mustField, 'required'],
        ];
    }

    public function scenarios()
    {
        return [
            'update' => ['name','products_id'],
        ];
    }

    function productsIdMust()
    {
        $productModel = new Product();
        $product = $productModel->getNameOfList();
        $params = Yii::$app->request->post()['Template'];
        if ($params['products_id'] && !in_array(array_keys($params['products_id']), array_keys($product))){
            foreach ($params['products_id'] as $key => $value){
                if (!in_array($key, array_keys($product))){
                    $this->addError('products_id', Yii::t('app', 'user base help23'));
                    Yii::$app->getSession()->setFlash('error', Yii::t('app', 'user base help23'));
                }
            }
        }
    }

    public function save($id, $data)
    {
        if ($id == '')
            $this->id = $this->getId();
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'create' => $this->create,
            'type' => $this->type,
            'content' => json_encode($this->content),
        ];
        return parent::save($this->id, $data);
    }
    
    /**
     * 获取分页列表
     * @param int $offset
     * @param int $pagesSize
     * @return array
     */
    public function getList($offset = 0, $pagesSize = 0)
    {
        $allList = $this->getValidList();
        if (empty($allList)) {
            return [];
        }
        //如果是查询全部，那么直接返回
        if ($offset == 0 && $pagesSize == 0) {
            return $allList;
        }
        //判断超管
        if (User::isSuper()){
            // 获取记录数，偏移量及记录数
            $allList = $allList['all'];
        }else{
            $allList = $allList['all'];
            //$allList = $allList['self'];
        }
        //切割数组，返回对应的数据
        $list = array_slice($allList, $offset, $pagesSize, true);
        return $list;
    }

    /**
     * 获取有效的列表
     * @param int $type 类型 默认0：全部，1：自己创建的，2：其他人的
     * @return array
     */
    public function getValidList()
    {
        //获取所有记录
        $list = parent::getList();
        $self = $comm = [];
        if($list){
            foreach ($list as $key => $one) {
                //如果是本人创建的
                if ( isset($one['create']) && $one['create'] == Yii::$app->user->identity->username ) {
                    $self[$key] = $one;
                }
                //否则如果是公用的(如果是超级管理员，可以看到所有的，包括未公开的模板)
                $userModel = new User();
                if (!User::isSuper()) {
                    //获取有此人创建的管理员
                    $canMgrope = $userModel->getChildIdAll();
                    if (isset($one['type']) && ($one['type'] == 1 || in_array($one['create'],$canMgrope))){
                        $comm[$key] = $one;
                    }
                } else {
                    if (isset($one['type'])){
                        $comm[$key] = $one;
                    }
                }
            }
        }
        return [
            'all' => $self + $comm,
            'self' => $self,
            'comm' => $comm,
        ];
    }

    /**
     * 获取计费策略列表，只要id和名称
     * @return array
     */
    public function getNameOfList($list)
    {
        $newList = [];
        if (isset($list['all'])) {
            foreach ($list['all'] as $k => $v) {
                $newList[$k] = $v['name'];
            }
        }
        return $newList;
    }
    public function deleteOne($id)
    {
        //写日志结束
        $this->executeCommand('DEL', $this->hashKeyPre.$id);
        $this->executeCommand('LREM', $this->listKey, [0, $id]);
        return true;
    }
    public function getOne($id)
    {
        $one = parent::getOne($id);
        if(isset($one['content'])){
            $content = yii\helpers\Json::decode($one['content']);
            if(isset($content['user_expire_time']) && $content['user_expire_time']>0 ){
                $content['user_expire_time'] = date('Y-m-d', $content['user_expire_time']);
            }
            $one['content'] = $content;
        }
        return $one;
    }
    
    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput(){
        return array(
            'create'=>[
                'label' => Yii::t('app', 'mgr name create')
                ],
                );
    }
    public function attributeLabels()
    {
        //扩展属性
        foreach (ExtendsField::getAllData() as $one) {
            $labels[$one['field_name']] = $one['field_desc'];
        }
        
        $labels['name'] = Yii::t('app','action_name');
        $labels['user_allow_chgpass'] = Yii::t('app', 'user allow chgpass');
        $labels['user_expire_time'] = Yii::t('app', 'user expire time');
        $labels['user_available'] = Yii::t('app', 'user available');
        $labels['user_allow_chgpass'] = Yii::t('app', 'user allow chgpass');
        return $labels;
        
    }
    public function getAttributesList()
    {
        //获取扩展字段的列表字段
        $exField = ExtendsField::getList();
        
        if ($exField['cert_type'] && $exField['user_type']){
            array_unshift($exField['cert_type'], Yii::t('app', 'Please Select'));
            array_unshift($exField['user_type'], Yii::t('app', 'Please Select'));
        }
        
        return yii\helpers\ArrayHelper::merge($exField, [
            'user_available' => [
                '0' => Yii::t('app', 'user available0'),
                '1' => Yii::t('app', 'user available1'),
                '2' => Yii::t('app', 'user available3'),
                '3' => Yii::t('app', 'user available4'),
                '4' => Yii::t('app', 'user available5'),
            ],
            'user_allow_chgpass' => [
                '1' => Yii::t('app', 'allow'),
                '0' => Yii::t('app', 'deny'),
            ],
        ]);
    }

    /**
     * 获取模板里面产品
     * @param $id
     * @param $productList
     * @return array
     */
    public function productsByTempSort($id, $productList){
        $data = $this->getOne($id);
        if(empty($data) || empty($data['content']['products_id']) || count($data['content']['products_id']) < 1){

            return $productList;
        }
        $res = [];
        $tempProducts = $data['content']['products_id'];
        if($productList){
            $ids = array_keys($productList);
            foreach ($tempProducts as $id){
                if(in_array($id, $ids)){
                    $res[$id] = $productList[$id];
                }
            }
        }
        return $res;
    }
}