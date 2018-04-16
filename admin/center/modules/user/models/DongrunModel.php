<?php
/**
 * Created by PhpStorm.
 * User: wjh
 * Date: 2016/9/29
 * Time: 11:08
 */

namespace center\modules\user\models;


use yii\base\Model;
use yii;
use common\models\Redis;

/**
 * dongrun数据处理模型
 * Class DongrunModel
 * @package center\modules\user\models
 */
class DongrunModel extends  Model
{
    public $password_type;
    public $sso_add_fields;
    public $user_add_fields;
    public $sso_edit_fields;
    public $user_edit_fields;
    public $mustAddExecFields;  //增加用户处理字段
    public $mustEditExecFields; //编辑用户处理字段
    public $allow_delete; //是否允许删除中间表
    public $user_type; //用户类型
    public $state; //动作类型字段
    protected  $group = [];
    protected  $map_arr1 = [];
    protected  $map_arr = [];
    protected  $configs = [];
    public $group_arr;
    protected  $tableName; //表名
    const HASH_DONGRUN_KEY = 'hash:dongrun:test';   //dongrun  hash
    //执行命令行前先执行这个
    public function init()
    {
        $conf = "/srun3/etc/dongruan.conf";
        if(!is_file($conf)) {
            $this->tableName = 'sso_user_pass';
        } else {
            $this->configs = parse_ini_file($conf);
            $this->tableName = $this->configs['db_table'];
        }


        //-----------类型映射--------------
        $arr1 = array();
        if(is_file("/srun3/etc/dongruan_map.conf"))
        {
            $arr1 = file("/srun3/etc/dongruan_map.conf");
        }
        if(count($arr1)>0)
        {
            foreach($arr1 as $v)
            {
                $v = preg_replace("/[\n|\r]+/", "", $v);
                $arr2 = explode(",", $v);
                $this->map_arr[$arr2[0]] = $arr2[1];
                $this->group_arr[$arr2[0]] = $arr2[2];
                $this->map_arr1[$arr2[1]] = $arr2[0];
            }
        }
    }
    public function attributeLabels()
    {
        return [
            'sso_add_fields' => Yii::t('app', 'dongrun help1'),
            'user_add_fields' => Yii::t('app', 'dongrun help2'),
            'sso_edit_fields' => Yii::t('app', 'dongrun help1'),
            'user_edit_fields' => Yii::t('app', 'dongrun help2'),
            'mustAddExecFields' => Yii::t('app', 'dongrun must handle'),
            'mustEditExecFields' => Yii::t('app', 'dongrun must handle'),
            'state' => Yii::t('app', 'dongrun action type'),
            'allow_delete' => Yii::t('app', 'allow delete'),
            'user_type' => Yii::t('app', 'dongrun user type'),
            'password_type' => Yii::t('app', 'password type')
        ];
    }

    public function getAttributesList()
    {
        //获取扩展字段的列表字段


        return yii\helpers\ArrayHelper::merge([], [

            //密码类型
            'password_type' => [
                '' => Yii::t('app', 'password type'),
                '1' => '明文密码',
                '2' => '32位密码',
                '3' => '16位密码',
          ],
        ]);
    }

    public function rules()
    {
        return [
            [['sso_add_fields', 'user_add_fields', 'user_type', 'password_type'], 'required', 'on'=>['add']],
            [['sso_add_fields'], 'CheckIsInTables', 'on'=>['add']],
            [['sso_edit_fields', 'user_edit_fields', 'state'], 'required', 'on'=>['edit']],
            [['state', 'user_type'], 'CheckIsExist'],
            [['allow_delete',],'integer'],
            [['allow_delete',],'string'],

        ];
    }

    /**
     * 检测中间表动作类型是否在字段中
     * @param $attributes
     * @param $params
     */
    public function checkIsExist($attributes, $params)
    {
        $fields = $this->getTableField();
        if (!in_array($this->$attributes, $fields)) {
            if ($attributes == 'state') {
                $this->addError($attributes, Yii::t('app', 'dongrun help5'));
            } else {
                $this->addError($attributes, Yii::t('app', 'dongrun help9'));
            }

        }
    }

    public function CheckIsInTables($attributes, $params)
    {
        $fields = $this->getTableField();

        $param = explode(',', $this->$attributes);
        if (strstr($attributes, 'sso_add_fields')) {
            $count = count(explode(',', $this->user_add_fields));
        } else {
            $count = count(explode(',', $this->user_edit_fields));
        }
        $out = array_diff($param, $fields);

        if ($out){
            //表明所勾选字段有些不在这个里头
            $this->addError($attributes, Yii::t('app', 'dongrun help7', ['table'=>$this->tableName]));
        }
        if (count($param) != $count) {
            $this->addError($attributes, Yii::t('app', 'dongrun help10'));
        }


    }

    /**
     * 获取表字段
     * @param string $tableName
     * @return array
     */
    public function getTableField($tableName = '')
    {
        $tableName = (!empty($tableName)) ? $tableName : $this->tableName;
        $db = Yii::$app->db;
        $data = $db->createCommand('DESC '."`$tableName`")->queryAll();
        $fields = [];
        foreach ($data as $k => $v) {
            $fields[] = $v['Field'];
        }


        return $fields;

    }


}