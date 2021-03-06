<?php

namespace center\modules\student\models;

use center\models\Pagination;
use common\models\User;
use Yii;

/**
 * This is the model class for table "stu_pub_center".
 *
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string $pic
 * @property integer $status
 * @property string $remark
 * @property string $ctime
 * @property string $utime
 */
class StuPubCenter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stu_pub_center';
    }
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->stu_id = Yii::$app->user->identity->getId();
            $this->stu_name = Yii::$app->user->identity->username;
            $this->ctime = $this->utime = time();
        } else {
            $this->utime = time();
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'desc'], 'string'],
            [['status', 'ctime', 'utime'], 'integer'],
            [['title', 'desc', 'content'], 'required'],
            [['pic'], 'file', 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png',],
            [['title', 'remark'], 'string', 'max' => 64],
            [['pic'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'desc' => '描述',
            'content' => '内容',
            'pic' => '图片',
            'status' => '状态',
            'remark' => '备注',
            'ctime' => 'Ctime',
            'utime' => 'Utime',
        ];
    }

    public function getAttributesList()
    {
        //1校内新闻2大赛新闻3行业新闻
        return [
            'status' => [
                0 => '待审核',
                1 => '审核通过',
                2 => '未通过'
            ]
        ];
    }

    /**
     * 要搜索的字段
     * @return array
     */
    public function getSearchInput()
    {
        //扩展字段加入搜索
        $exField = [];

        return yii\helpers\ArrayHelper::merge([
            'title' => [
                'label' => Yii::t('app', '标题')
            ],
            'status' => [
                'label' => Yii::t('app', '发布状态'),
                'list' => [
                    '' => '全部',
                    0 => '待审核',
                    1 => '审核通过',
                    2 => '未通过'
                ]
            ],
            'ctime' => [
                'label' => Yii::t('app', '发布时间')
            ],

        ], $exField);
    }

    /**
     * 获取所有招聘信息
     * @param $param
     * @return array
     */
    public function getList($param)
    {
        $rs = [];
        try {
            $query = self::find();
            $pagesSize = 10;

            foreach ($param as $k => $v) {
                if (!empty($v) && $this->hasAttribute($k)) {
                    if ($k == 'company_name') {
                        $query->andWhere("$k like :title", [':title' => "%".$v."%"]);
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
}
