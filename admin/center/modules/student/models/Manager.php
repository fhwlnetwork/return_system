<?php

namespace center\modules\student\models;

use center\extend\Tool;
use center\models\Pagination;
use center\modules\auth\models\SrunJiegou;
use common\models\User;
use Yii;

/**
 * This is the model class for table "manager".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $mobile_phone
 * @property integer $role
 * @property integer $status
 * @property string $mgr_org
 * @property string $mgr_region
 * @property string $mgr_product
 * @property string $mgr_admin
 * @property string $mgr_portal
 * @property string $tid
 * @property string $path
 * @property integer $pid
 * @property integer $mgr_admin_type
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $ip_area
 * @property integer $max_open_num
 * @property integer $expire_time
 */
class Manager extends \yii\db\ActiveRecord
{
    public $showField = ['username', 'mgr_org', 'major_name', 'begin_time', 'stop_time'];
    //搜索字段
    private $_searchField = null;

    public function getSearchField()
    {
        if (!is_null($this->_searchField)) {
            return $this->_searchField;
        }
        $this->_searchField = yii\helpers\ArrayHelper::merge([
            'user_id' => Yii::t('app', 'user id'),
            'username' => Yii::t('app', 'account'),
            'mgr_org' => Yii::t('app', 'group id'),
            'major_name' => Yii::t('app', '专业名称'),
            'begin_time' => '入校时间',
            'stop_time' => '毕业时间',
        ], []);

        return $this->_searchField;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email', 'mobile_phone', 'mgr_product', 'mgr_admin', 'mgr_portal', 'tid', 'created_at', 'updated_at'], 'required'],
            [['role', 'status', 'pid', 'mgr_admin_type', 'created_at', 'updated_at', 'max_open_num', 'expire_time'], 'integer'],
            [['mgr_org', 'mgr_region', 'mgr_product', 'mgr_admin'], 'string'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'mgr_portal', 'path', 'ip_area'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['mobile_phone'], 'string', 'max' => 20],
            [['tid'], 'string', 'max' => 30],
            [['username'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'mobile_phone' => 'Mobile Phone',
            'role' => 'Role',
            'status' => 'Status',
            'mgr_org' => 'Mgr Org',
            'mgr_region' => 'Mgr Region',
            'mgr_product' => 'Mgr Product',
            'mgr_admin' => 'Mgr Admin',
            'mgr_portal' => 'Mgr Portal',
            'tid' => 'Tid',
            'path' => 'Path',
            'pid' => 'Pid',
            'mgr_admin_type' => 'Mgr Admin Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'ip_area' => 'Ip Area',
            'max_open_num' => 'Max Open Num',
            'expire_time' => 'Expire Time',
        ];
    }

    public function getAttributesList()
    {
        return [];
    }

    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];

        return yii\helpers\ArrayHelper::merge([
            'username' => [
                'label' => Yii::t('app', 'account')
            ],
        ], $exField);
    }

    /**
     * 获取学生
     * @param $param
     * @return array
     */
    public function getList($param)
    {
        try {
            $query = self::find()->alias('m')->leftJoin('auth_assignment a', 'm.id=a.user_id')->where(['item_name' => '学生']);
            $pagesSize = 10;
            if (!User::isSuper() && (!isset($param['group_id']) || empty($param['group_id']))) {
                $canGroup = SrunJiegou::canMgrGroupNameList();
                $query->andWhere(['m.mgr_org' => array_keys($canGroup)]);
            }
            foreach ($param as $k => $v) {
                if (!empty($v) && $this->hasAttribute($k)) {
                    if ($k == 'username') {
                        $query->andWhere("$k like :title", [':title' => "%" . $v . "%"]);
                    } else if ($k == 'group_id') {
                        $group_id = explode(',', $param['group_id']);
                        $ids = SrunJiegou::getNodeId($group_id);
                        $query->andWhere(['g.mgr_org' => $ids]);
                    } else {
                        $query->andWhere('ctime >= :start', [':start' => strtotime($v)]);
                    }
                }
            }
            $pages = new Pagination([
                'totalCount' => $query->count(),
                'pageSize' => $pagesSize
            ]);
            if (isset($params['orderBy']) && array_key_exists($param['orderBy'], $this->searchField)) {
                $query->orderBy([$param['orderBy'] => $param['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
            } else {
                $query->orderBy(['m.id' => SORT_DESC]);
            }
            $list = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->asArray()
                ->all();
            $rs = ['code' => 1, 'data' => $list, 'pagination' => $pages];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取学生异常' . $e->getMessage()];
        }

        return $rs;
    }

    /**
     * 获取学生工作记录
     * @return array
     */
    public function getWorkHistory($id)
    {
        $rs = [];
        try {
            $rs = StuWorks::find()->where(['stu_id' => $id])->asArray()->all();
            $rs = $this->showWork($rs);
            $rs = ['code' => 1, 'msg' => 'ok', 'data' => $rs];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取工作异常' . $e->getMessage()];
        }
        //var_dump($rs);exit;

        return $rs;

    }

    /**
     * 组装工作记录
     * @param $list
     * @return string
     */
    public function showWork($list)
    {
        $show = '';
        //需要区别开不同页面的不同调用
        foreach ($list as $value) {
            //处理时间格式
            $ext = Tool::showDateTime($value['ctime']);
            $value = array_merge($value, $ext);

            $bigLabelColor = 'btn-primary';
            $smallLabelColor = 'bg-danger';
            $end = $value['is_end'] == 1 ? '至今' : date('Y-m-d', $value['stop_time']);
            $value['showContent'] = sprintf('%s~%s在%s上班', date('Y-m-d', $value['stime']),$end,  $value['company_name']);
            $show .= '
                        <article class="tl-item">
                            <div class="tl-body">
                                <div class="tl-entry">
                                    <div class="tl-caption">
                                        <a href="javascript:;"  class="btn ' . $bigLabelColor . ' btn-block">'
                . $value['showContent']
                . '</a>
                                    </div>
                                </div>
                            </div>
                        </article>';
            //正常的日志内容
            $show .= '
                <article class="tl-item">
                    <div class="tl-body">
                        <div class="tl-entry">
                            <div class="tl-icon round-icon sm icon-bg ' . $smallLabelColor . '">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="tl-content">
                                <div class="row">
                                    <div class="col-sm-2"><span style="color: #999999;" title="' . date('H:i:s', $value['ctime']) . '">'
                . $value['time'] . '</span>
                                    </div>
                                    <div class="col-sm-10">'
                . $value['showContent'];
            //附加显示：详细时间和IP地址
            $showExt = ' (' . date('H:i:s', $value['ctime']) . ')';
            //是否显示：查看详情

            //生成详情的表格
            $value['action'] = 'add';
            $contentArr = $value;
            if (is_array($contentArr)) {
                $content = '<table class="table table-bordered table-hover"><thead><tr>';
                $content .= '<th>' . Yii::t('app', 'property') . '</th>';
                //如果是编辑模式下，那么需要显示旧值
                $content .= $value['action'] == 'edit' ? '<th>' . Yii::t('app', 'old value') . '</th>' : '';
                $content .= '<th>' . Yii::t('app', 'new value') . '</th>';
                $content .= '</tr></thead>';
                $content .= '<tbody>';
                // 解析内容
                //创建一个对象来获取label的数据
                $objects = [];//保存已经实例化的对象
                $labels = [];//字段的标签数组
                $fieldLabels = [];//已修改的字段数组
                $attributesList = [];//下拉列表类数组
                $object = Yii::createObject(['class' => 'center\modules\student\models\StuWorks']);
                $objects['center\modules\student\models\StuWorks'] = $object;

                //获取字段的标签值
                if (method_exists($object, 'attributeLabels')) {
                    $labels = $object->attributeLabels();
                }
                //获取字段的列表值
                if (method_exists($object, 'getAttributesList')) {
                    $attributesList = $object->getAttributesList();
                }
                $contentArr['stop_time'] = $end;

                foreach ($contentArr as $k => $v) {
                    if (in_array($k, ['id', 'major_id','stu_name','stu_id', 'ctime', 'utime', 'day', 'week', 'showContent', 'is_end', 'time', 'action'])) continue;
                    $content .= '<tr>';
                    $content .= '<td>' . (isset($labels[$k]) ? $labels[$k] : $k) . '</td>';
                    if ($value['action'] == 'edit') {
                        $fieldLabels[] = (isset($labels[$k]) ? $labels[$k] : $k);
                    }
                    //值为数组的形式
                    if ($value['action'] == 'edit' && is_array($v)) {
                        $content .= '<td>' . $this->labelFiled($attributesList, $k, $v[0]) . '</td>';//原值
                        $content .= '<td>' . $this->labelFiled($attributesList, $k, $v[1]) . '</td>';//新值
                    } //单个值的形式
                    else {
                        if ($k == 'user_create_time' || $k == 'user_update_time' || preg_match('/time/', $k)) {
                            //用户创建时间及更新时间由时间戳改为标准时间
                            if (is_numeric($v)) {
                                $content .= '<td>' . $this->labelFiled($attributesList, $k, date('Y-m-d', $v)) . '</td>';
                            } else {
                                $content .= '<td>' . $this->labelFiled($attributesList, $k, $v) . '</td>';
                            }

                        } else {
                            $content .= '<td>' . $this->labelFiled($attributesList, $k, $v) . '</td>';
                        }
                    }
                    $content .= '</tr>';
                }
                $content .= '</tbody></table>';

                $show .= $value['action'] == 'edit' ? ' (' . implode($fieldLabels, ', ') . ')' : '';
                $show .= $showExt;
                $show .= '<a href="#" class="btn btn-default btn-xs" style="margin-left: 20px;" onclick="showDetail(' . $value['id'] . ') "> '
                    . Yii::t('app', 'show detail') . '</a>
                          <p></p>
                          <div id="detail' . $value['id'] . '" style="display:none">' . $content . '</div>';
            }

            $show .= '            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            ';
        }
        //print_r($_SESSION);
        return $show;
    }

    /**
     * 获取学生发布记录
     * @return array
     */
    public function getStuPub($id)
    {
        $rs = [];
        try {
            $rs = StuPubCenter::find()->where(['stu_id' => $id])->asArray()->all();
            $rs = ['code' => 1, 'msg' => 'ok', 'data' => $rs];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取工作异常' . $e->getMessage()];
        }

        return $rs;

    }

    /**
     * 把日志中的字段值进行转换，根据类中定义的getAttributesList()方法
     * @param $attributesList array 列表数组
     * @param $field string 字段名
     * @param $value string 字段值
     * @return string
     */
    public function labelFiled($attributesList, $field, $value)
    {
        $str = [];
        if (is_array($value)) {
            foreach ($value as $v) {
                $str[] = $this->labelFiled($attributesList, $field, $v);
            }
        } else {
            $str[] = isset($attributesList[$field][$value]) ? $attributesList[$field][$value] : $value;
        }
        return $str ? implode($str, ', ') : '';
    }

}
