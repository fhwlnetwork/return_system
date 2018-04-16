<?php

namespace center\modules\auth\models;

use Yii;
use common\models\User;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "{{%srun_jiegou}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $pid
 * @property string $path
 * @property string $tid
 * @property integer $level
 * @property integer $status
 */
class RegionGroup extends \yii\db\ActiveRecord
{
    private static $_model;

    const TYPE_0 = '0';
    const TYPE_1 = '1';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%region_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'pid'], 'required'],
            [['pid', 'level', 'id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['tid'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'org_id'),
            'name' => Yii::t('app', 'org_name'),
            'level' => Yii::t('app', 'org_level'),
            'status' => Yii::t('app', 'org_status'),
            'path' => Yii::t('app', 'org_path'),
            'pid' => Yii::t('app', 'org_pid'),
            'tid' => Yii::t('app', 'org_tid'),
        ];
    }

    /**
     * 返回结构表中的某一个字段的值.
     * @param $id 表主键 ID 值
     * @param $item 表中的字段
     * @return mixed
     */
    public static function getJieGouItem($id, $item)
    {
        $model = static::findOne($id);

        if ($model) {
            return $model->$item;
        } else {
            return null;
        }

    }

    /**
     * 返回指定ID 的所有父级.
     * @param $id
     * @return string
     */
    public static function getOwnParent($id)
    {
        $path = static::getJieGouItem($id, 'path');

        if ($path === '0') {
            return '/';
        } elseif ($path) {
            $pathArray = explode('-', $path);
            unset($pathArray[0]);
            unset($pathArray[1]);

            $ownParent = '';
            foreach ($pathArray as $val) {
                $name = static::getJieGouItem($val, 'name');
                if (!empty($name)) {
                    $ownParent .= $name . '/';
                } else {
                    $ownParent .= '/';
                }
            }

            $ownData = static::getId('id', $id);
            $ownParent = $ownParent . $ownData['name'];
        } else {
            $ownParent = '';
        }

        return $ownParent;
    }

    /**
     * 返回当前登陆用户可以管理的节点.
     * @return string
     */
    public static function ajax()
    {
        $model = self::model();

        // 超级管理员直接全部输出
        if (User::isSuper()) {
            $array = $model->find()->asArray()->all();
            return json_encode($array);
        }

        if (Yii::$app->user->identity->mgr_org) {
            $orgArray = explode(',', Yii::$app->user->identity->mgr_org);
            $array = static::getAllChildData($orgArray, $model);
            return json_encode($array);
        }
    }

    public static function getAllChildData($orgArray, $model)
    {
        foreach ($orgArray as $val) {
            $nodeData = $model->find()->where(['id' => $val])->asArray()->one();
            $newPath = $nodeData['path'] . '-' . $val;
            $data[] = $model->find()->where('path like "' . $newPath . '%"')->orWhere(['id' => $val])->asArray()->all();
        }

        foreach ($data as $val) {
            if (is_array($val)) {
                foreach ($val as $value) {
                    $array[] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * 返回指定 PID 值的数组数据.
     * @param $pid 父ID.
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getChildData($pid)
    {
        return static::find()->where(['pid' => $pid])->andWhere(['status' => '1'])->asArray()->all();
    }

    /**
     * 重新设置节点
     * 超级管理员删除所有，非超级管理员删除所有自己管理的节点
     * @param $newNodeData
     * @return bool
     */
    public function setOrg($newNodeData)
    {
        //print_r($newNodeData);exit;
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (User::isSuper() == 'root') {
                self::deleteAll();
            } else {
                $org = User::getMgrOrg(Yii::$app->user->id);
                if (!empty($org)) {
                    $orgArray = explode(',', $org);

                    foreach ($orgArray as $val) {
                        $obj = RegionGroup::findOne($val);
                        RegionGroup::deleteAll('path like "' . $obj->path . '-' . $val . '%"');
                        $obj->delete();
                    }
                }
            }
            //参数为空直接返回.
            if (empty($newNodeData)) {
                return false;
            }

            $item = []; // 存储提交数据的 ID TID 值.
            foreach ($newNodeData as $val) {
                $model = new RegionGroup();

                if ($val->isNew == 0) { // 原始数据
                    $item[$val->tId] = $val->id; // 组合 key=>value 形式数据. Tid 是唯一的.

                    //根
                    if ($val->id && ($val->id == 1)) {
                        $model->id = $val->id;
                        $model->pid = 0;
                        $model->path = 0;
                    } else {
                        if (empty($val->parentTId)) {
                            $parentDate = self::getId('pid', 0); // 查询是否存在 父级
                        } else {
                            $parentDate = self::getId('id', $item[$val->parentTId]); // 查询是否存在 父级
                        }
                        $model->pid = $parentDate['id'];
                        $model->id = $val->id;
                        $model->path = $parentDate['path'] . '-' . $parentDate['id'];
                    }

                    $model->name = preg_replace('/\s(?=\s)/', '', $val->name);
                    $model->tid = $val->tId;
                    $model->save();

                } elseif ($val->isNew == 1) { // 新增数据
                    $parentDate = self::getId('id', $item[$val->parentTId]); // 查询是否存在 父级
                    $model->pid = $parentDate['id']; // 父级的 ID 为 本级的 PID
                    $model->path = $parentDate['path'] . '-' . $parentDate['id'];

                    $model->name = preg_replace('/\s(?=\s)/', '', $val->name);
                    $model->tid = $val->tId;
                    $model->save();
                    $item[$val->tId] = Yii::$app->db->getLastInsertID(); // 组合 key=>value 形式数据. Tid 是唯一的.
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        self::log(true);
        return true;
    }

    // 根本传过来的参数 返回 本节点以及子节点的 所有ID值.
    public static function getNodeId($array)
    {
        $source = [];

        if (!empty($array) && is_array($array)) {
            foreach ($array as $val) {
                $source[] = $val;
                $model = self::model();
                $path = $model->findOne($val)->path . '-' . $val;

                $data = $model->find()->andFilterWhere(['like', 'path', $path])->asArray()->all();
                if ($data) {
                    foreach ($data as $value) {
                        $source[] = $value['id'];
                    }
                }
            }
            $source = array_unique($source);
        }

        return $source;
    }

    /**
     * 返回非超级管理员可以管理的组织节点
     * @return array ['节点id', '节点id', '节点id', '节点id']
     */
    public static function getAllNode()
    {
        $nodes = [];
        $mgr_org = Yii::$app->user->identity->mgr_org;
        if (!empty($mgr_org)) {
            $nodeId = explode(',', $mgr_org);
            $nodes = self::getNodeId($nodeId);
        }
        return $nodes;
    }

    /**
     * 根据传参数返回值.
     * @param $field 表字段.
     * @param $val 字段值.
     * @return int
     */
    public static function getId($field, $val)
    {
        $data = static::find()->where([$field => $val])->one();

        if ($data) {
            return $data['attributes'];
        } else {
            return 0;
        }
    }

    public static function getAllIdNameVal()
    {
        $source = [];
        $model = self::model()->find()->asArray()->all();

        if ($model) {
            foreach ($model as $val) {
                $source[$val['id']] = $val['name'];
            }
        }

        return $source;
    }

    /**
     * 返回管理员选择的所有节点名称.
     * 返回管理员所选择的所有节点名称，以字符串连接形式返回。
     * 在编辑管理员处使用.
     * @param $item 节点的ID 字符串.
     * @return string
     */
    public static function getNodeName($item)
    {
        $NodeId = explode(',', $item);
        $name = '';

        foreach ($NodeId as $val) {
            $name .= self::findOne($val)->name . ' ,';
        }

        return rtrim($name, ',');
    }

    public function getName($id)
    {
        if (empty($id)) {
            return null;
        }

        $data = self::findOne($id);

        if ($data) {
            return $data->name;
        }
        return null;
    }

    /**
     *
     */
    public function getGroupNameArr($idArr)
    {
        $arr = [];
        if (is_array($idArr)) {
            foreach ($idArr as $id) {
                $arr[$id] = $this->getName($id);
            }
        }
        return $arr;
    }

    /**
     * 单例本对象MODEL.
     * @return SrunJiegou
     */
    protected static function model()
    {
        if (!isset(self::$_model)) {
            return self::$_model = new RegionGroup();
        } else {
            return self::$_model;
        }
    }

    //日志操作

    /**
     * 写操作日志, 增改.
     * @param bool $insert
     * @param array $changedAttributes
     */
    public static function log()
    {
        $dirtyArr = Yii::$app->user->identity->username . Yii::t('app', 'auth_srunjiegou_font1');

        if (!empty($dirtyArr)) {
            $logData = [
                'operator' => Yii::$app->user->identity->username,
                'target' => Yii::t('app', 'auth_srunjiegou_font2'),
                'action' => 'edit',
                'action_type' => 'Setting org',
                'content' => $dirtyArr,
                'class' => __CLASS__,
                'type' => 1,
            ];
            LogWriter::write($logData);
        }
    }

    /**
     * 获取当前管理员可以管理的用户组名称列表，key是用户组id
     * @return array
     */
    public static function canMgrGroupNameList(){
        $canMgrgroups = [];
        $groups = json_decode(self::ajax());
        if ($groups) {
            foreach ($groups as $val) {
                $canMgrgroups[$val->id] = $val->name;
            }
        }
        return $canMgrgroups;
    }
}
