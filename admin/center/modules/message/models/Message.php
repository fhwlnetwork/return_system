<?php

namespace center\modules\message\models;

use center\models\Pagination;
use Yii;

/**
 * This is the model class for table "message".
 *
 * @property string $id
 * @property string $article_id
 * @property string $article_title
 * @property string $uid
 * @property string $prev_id
 * @property integer $status
 * @property string $message
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->ctime = $this->utime = 1;
        } else {
            $this->utime = time();
            $this->mid = Yii::$app->user->identity->getId();
            $this->operator = Yii::$app->user->identity->username;
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'status'], 'integer'],
            [['message'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'status' => '留言状态',
            'message' => '留言内容',
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
            'username' => [
                'label' => Yii::t('app', '用户名')
            ],
            'title' => [
                'label' => Yii::t('app', '标题')
            ],
            'status' => [
                'label' => Yii::t('app', '状态'),
                'list' => [
                    '' => '请选择',
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

    public function getAttributesList()
    {
        return [
            'status' => [
                0 => '待审核',
                1 => '审核通过',
                2 => '未通过'
            ]
        ];
    }

    /**
     * @param $param
     * @return array
     */
    public function getList($param)
    {
        try {
            $query = self::find();
            $pagesSize = 10;
            foreach ($param as $k => $v) {
                if (!empty($v) || preg_match('/^0$/', $v) && $this->hasAttribute($k)) {
                    if ($k == 'title') {
                        $query->andWhere("$k like :title", [':title' => "%" . $v . "%"]);
                    } else {
                        $query->andWhere('ctime >= :start', [':start' => strtotime($v)]);
                    }
                }
            }
            $pages = new Pagination([
                'totalCount' => $query->count(),
                'pageSize' => $pagesSize
            ]);
            $list = $query->offset($pages->offset)
                ->orderBy('id desc')
                ->limit($pages->limit)
                ->asArray()
                ->all();
            $rs = ['code' => 1, 'data' => $list, 'pagination' => $pages];
        } catch (\Exception $e) {
            $rs = ['code' => 500, 'msg' => '获取异常'];
        }

        return $rs;
    }
}