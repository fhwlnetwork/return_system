<?php

namespace center\modules\student\models;

use center\models\Pagination;
use center\modules\auth\models\SrunJiegou;
use common\extend\Excel;
use common\models\User;
use Yii;

/**
 * This is the model class for table "stu_works_now".
 *
 * @property string $id
 * @property string $stu_id
 * @property string $stu_name
 * @property string $company_name
 * @property double $salary
 * @property string $major_id
 * @property string $major_name
 * @property string $stime
 * @property string $stop_time
 * @property integer $is_end
 * @property string $ctime
 * @property string $utime
 */
class StuWorksNow extends \yii\db\ActiveRecord
{
    public $created_at;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stu_works_now';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stu_id', 'major_id', 'stime', 'stop_time', 'is_end', 'ctime', 'utime'], 'integer'],
            [['salary'], 'number'],
            [['stu_name', 'company_name', 'major_name', 'created_at'], 'string', 'max' => 64],
            [['Dwzzjgdm', 'Dwxzdm', 'Dwhydm', 'Dwszddm', 'Gzzwlbdm', 'Byqxdm'], 'string', 'max' => 255],
        ];
    }

    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];

        return yii\helpers\ArrayHelper::merge([
            'company_name' => [
                'label' => Yii::t('app', '公司名称')
            ],
            'ctime' => [
                'label' => Yii::t('app', '发布时间')
            ],
        ], $exField);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stu_name' => '用户名',
            'company_name' => '公司名称',
            'salary' => '薪水',
            'major_id' => '专业名称',
            'major_name' => '专业名称',
            'stime' => '开始时间',
            'stop_time' => '结束时间',
            'is_end' => '是否至今',
            'ctime' => 'Ctime',
            'utime' => 'Utime',
            'Byqxdm' => '毕业去向代码',
            'work_name' => '工作名称',
            'Dwzzjgdm' => '单位组织机构代码',
            'Dwxzdm' => '单位性质代码',
            'Dwhydm' => '单位行业代码',
            'Dwszddm' => '单位所在地代码',
            'Gzzwlbdm' => '工作职位类别代码',
        ];
    }

    public function getList($param)
    {
        $rs = [];
        try {
            $query = self::find();
            $pagesSize = 10;

            foreach ($param as $k => $v) {
                if (!empty($v) && $this->hasAttribute($k)) {
                    if ($k == 'company_name') {
                        $query->andWhere("$k like :title", [':title' => "%" . $v . "%"]);
                    } else {
                        $query->andWhere('ctime >= :start', [':start' => strtotime($v)]);
                    }
                }
            }
            if (User::isStudent()) {
                $query->andWhere(['stu_id' => Yii::$app->user->identity->getId()]);
            }
            $pages = new Pagination([
                'totalCount' => $query->count(),
                'pageSize' => $pagesSize
            ]);
            $list = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->asArray()
                ->all();
            $rs = ['code' => 1, 'data' => $list, 'pagination' => $pages];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取招聘信息异常'];
        }

        return $rs;
    }

    /**
     * 获取就业率
     * @param $param
     * @return array
     */
    public function getRates($param)
    {
        try {
            //获取学生人数
            $users = Manager::find()->alias('m')->leftJoin('auth_assignment a', 'm.id=a.user_id')->where(['item_name' => '学生'])->count();

            $this->load($param);
            //根据条件获取有工作人数
            $hasWork = self::find();
            if (!empty($this->created_at)) {
                $hasWork->andWhere('ctime >= :start', [':start' => strtotime($this->created_at)]);
            }
            $hasWork->orWhere('is_end = 1');
            $hasCount = $hasWork->count();
            $series = [
                ['name' => '就业者', 'value' => $hasCount],
                ['name' => '无业者', 'value' => $users - $hasCount],
            ];
            $legend = ['就业者', '无业者'];

            return [
                'code' => 200,
                'data' => [
                    'title' => '就业率统计',
                    'legend' => $legend,
                    'seriesData' => $series
                ]
            ];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取就业率统计异常'];
        }

        return $rs;
    }

    /**
     * 按班级统计
     * @param $param
     * @return array
     */
    public function getRatesByLevel($param)
    {
        try {
            //获取各个用户组学生人数
            $users = Manager::find()->alias('m')
                ->select('mgr_org, count(m.id) number')
                ->leftJoin('auth_assignment a', 'm.id=a.user_id')
                ->where(['item_name' => '学生'])
                ->groupBy('mgr_org')
                ->indexBy('mgr_org')
                ->asArray()
                ->all();
            $groups = SrunJiegou::canMgrGroupNameList();
            $query = self::find()
                ->select('count(id) number, group_id');
            $this->load($param);
            if (!empty($this->created_at)) {
                $query->andWhere('ctime >= :start', [':start' => strtotime($this->created_at)]);
            }
            $query->orWhere('is_end = 1');
            $has = $query->groupBy('group_id')->indexBy('group_id')->asArray()->all();
            $yAxisData = [];
            $xAxis = [];
            foreach ($groups as $k => $v) {
                if (isset($has[$k]) || isset($users[$k])) {
                    $xAxis[] = $v;
                    $has = isset($has[$k]) ? $has[$k]['number'] : 0;
                    $total = isset($users[$k]) ? $users[$k]['number'] : 0;
                    $unHas = max(0, $total - $has);
                    $yAxisData['has'][] = $has;
                    $yAxisData['un_has'][] = $unHas;
                }
            }
            $legend = ['就业人数', '无业人数'];
            $series = $this->getLineSeries($yAxisData, $legend);
            $rs = [
                'code' => 200,
                'data' => [
                    'title' => '就业率统计',
                    'legends' => json_encode($legend),
                    'series' => json_encode($series),
                    'xAxis' => json_encode($xAxis),
                ]
            ];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取就业率统计异常'];
        }

        return $rs;
    }

    /**
     * 按班级统计
     * @param $param
     * @return array
     */
    public function getRatesByMajor($param)
    {
        try {
            //获取各个用户组学生人数
            $users = Manager::find()->alias('m')
                ->select('major_id, count(m.id) number, major_name')
                ->leftJoin('auth_assignment a', 'm.id=a.user_id')
                ->where(['item_name' => '学生'])
                ->andWhere('major_id != 0')
                ->groupBy('major_id')
                ->indexBy('major_id')
                ->asArray()
                ->all();
            $query = self::find()
                ->select('count(id) number, major_id');
            $this->load($param);
            if (!empty($this->created_at)) {
                $query->andWhere('ctime >= :start', [':start' => strtotime($this->created_at)]);
            }
            $query->orWhere('is_end = 1');
            $has = $query->groupBy('major_id')->indexBy('major_id')->asArray()->all();
            $yAxisData = [];
            $xAxis = [];
            foreach ($users as $k => $v) {
                $has = isset($has[$k]) ? $has[$k]['number'] : 0;
                $total = isset($users[$k]) ? $users[$k]['number'] : 0;
                $unHas = max(0, $total - $has);
                $yAxisData['has'][] = $has;
                $yAxisData['un_has'][] = $unHas;
                $xAxis[] = $v['major_name'];
            }
            $legend = ['就业人数', '无业人数'];
            $series = $this->getLineSeries($yAxisData, $legend);
            $rs = [
                'code' => 200,
                'data' => [
                    'title' => '就业率统计',
                    'legends' => json_encode($legend),
                    'series' => json_encode($series),
                    'xAxis' => json_encode($xAxis),
                ]
            ];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取就业率统计异常'];
        }

        return $rs;
    }

    public function getRatesOut($param)
    {
        try {
            //根据条件获取有工作人数
            $hasWork = self::find()->select('count(id) number, is_same');
            //获取学生人数
            if (isset($param['group_id']) && !empty($param['group_id'])) {
                $group = SrunJiegou::getAllChildId($param['group_id']);
                $group = explode(',', $group);
                $hasWork->andWhere(['group_id' => $group]);
            }
            $this->load($param);

            if (!empty($this->created_at)) {
                $hasWork->andWhere('ctime >= :start', [':start' => strtotime($this->created_at)]);
            }
            $hasWork->orWhere('is_end = 1');
            $hasCount = $hasWork->groupBy('is_same')->indexBy('is_same')->asArray()->all();
            $is_same = isset($hasCount[0]) ? $hasCount[0]['number'] : 0;
            $is_other = isset($hasCount[1]) ? $hasCount[1]['number'] : 0;
            $series = [
                ['name' => '跨行者', 'value' => $is_other],
                ['name' => '同行者', 'value' => $is_same],
            ];
            $legend = ['跨行者', '同行者'];

            return [
                'code' => 200,
                'data' => [
                    'title' => '跨行率统计',
                    'legend' => $legend,
                    'seriesData' => $series
                ]
            ];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取就业率统计异常'];
        }
    }

    public function export($param)
    {
        $rs = [];
        try {
            $query = self::find();
            $pagesSize = 10;

            foreach ($param as $k => $v) {
                if (!empty($v) && $this->hasAttribute($k)) {
                    if ($k == 'company_name') {
                        $query->andWhere("$k like :title", [':title' => "%" . $v . "%"]);
                    } else {
                        $query->andWhere('ctime >= :start', [':start' => strtotime($v)]);
                    }
                }
            }
            $list = $query->asArray()->all();
            $group = SrunJiegou::canMgrGroupNameList();
            if ($list) {
                $excelData = [];
                $excelData[0] = ['学号', '班级', '工作名称', '专业名称', '公司名称','毕业去向代码', '单位组织机构代码','单位性质代码','单位行业代码','单位所在地代码','工作职位类别代码','薪水', '是否跨行', '开始时间', '结束时间'];
                foreach ($list as $k => $v) {
                    $group_name = isset($group[$v['group_id']]) ? $group[$v['group_id']] : $v['group_id'];
                    $is_same = $v['is_same'] == 1 ? '否' : '是';
                    $end = $v['stop_time'] == 0 ? '至今' : date('Y-m-d', $v['stop_time']);
                    $excelData[] = [
                        $v['stu_name'], $group_name, $v['work_name'], $v['major_name'], $v['company_name'], $v['Byqxdm'],$v['Dwzzjgdm'],$v['Dwxzdm'],$v['Dwhydm'],$v['Dwszddm'],$v['Gzzwlbdm'],$v['salary'], $is_same,
                        date('Y-m-d', $v['stime']), $end
                    ];
                }
                $title = '学生就业中心';
                Excel::header_file($excelData, $title . '.xls', $title);
                exit;
            } else {
                $rs = ['code' => 404, 'msg' => '没有要导出的数据'];
            }
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '学生数据导出异常'];
        }
        Yii::$app->getSession()->setFlash('error', $rs['msg']);

        return $rs;
    }


    /**
     * 打包数据
     * @param $data
     * @param $legend
     * @return array
     */
    public function getLineSeries($data, $legend)
    {
        $result = [];
        $i = 0;
        foreach ($data as $v) {
            $object = new \stdClass();
            $object->type = 'line';
            $object->name = $legend[$i];
            $object->data = $v;
            $object->symbol = true;
            $object->sampling = 'average';
            $object->symbol = 'none';
            $object->areaStyle = ['normal' => []];
            $result[] = $object;
            $i++;
        }


        return $result;
    }
}
