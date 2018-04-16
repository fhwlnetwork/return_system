<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/4/19
 * Time: 16:56
 */

namespace center\modules\log\models;

use yii\data\Pagination;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%bills}}".
 *
 * @property string $id
 * @property string $user_name
 * @property integer $group_id
 * @property string $user_real_name
 * @property string $action
 * @property integer $target_id
 * @property string $change_amount
 * @property string $before_amount
 * @property string $before_balance
 * @property string $after_amount
 * @property string $remark
 * @property integer $operate_time
 * @property string $mgr_name
 * @property string $api_name
 * @property string $operate_user_name
 */
class Bills extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bills}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_name', 'user_real_name', 'action', 'change_amount', 'before_amount', 'after_amount', 'before_balance', 'operate_time', 'mgr_name'], 'required'],
            [['group_id', 'target_id', 'operate_time'], 'integer'],
            [['change_amount', 'before_amount', 'before_balance', 'after_amount'], 'number'],
            [['user_name', 'user_real_name', 'action', 'mgr_name', 'api_name', 'operate_user_name'], 'string', 'max' => 64],
            [['remark'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'id'),
            'user_name' => Yii::t('app', 'user_name'),
            'group_id' => Yii::t('app', 'group_id'),
            'user_real_name' => Yii::t('app', 'user_real_name'),
            'action' => Yii::t('app', 'action'),
            'target_id' => Yii::t('app', 'target _id'),
            'change_amount' => Yii::t('app', 'change_amount'),
            'before_amount' => Yii::t('app', 'before_amount'),
            'before_balance' => Yii::t('app', 'before_balance'),
            'after_amount' => Yii::t('app', 'after_amount'),
            'remark' => Yii::t('app', 'remark'),
            'operate_time' => Yii::t('app', 'operate_time'),
            'mgr_name' => Yii::t('app', 'mgr_name'),
            'api_name' => Yii::t('app', 'api_name'),
            'operate_user_name' => Yii::t('app', 'operate_user_name'),
        ];
    }

    // 分页查询
    public function getBills($get){
        $start_time = $get['start_time'];
        $end_time = $get['end_time'];
        $user_name = $get['user_name'];
        $user_real_name = $get['user_real_name'];
        $remark = $get['remark'];
        $mgr_name = $get['mgr_name'];
        $api_name = $get['api_name'];
        $operate_user_name = $get['operate_user_name'];
        $action = $get['action'];

        $export = $get['export'];

        $query = self::find();
        if($user_name) $query->andWhere(['user_name' => $user_name]);
        if($user_real_name) $query->andWhere(['user_real_name' => $user_real_name]);
        if($mgr_name) $query->andWhere(['mgr_name' => $mgr_name]);
        if($api_name) $query->andWhere(['api_name' => $api_name]);
        if($operate_user_name) $query->andWhere(['operate_user_name' => $operate_user_name]);
        if($action) $query->andWhere(['action' => $action]);
        if($remark) $query->andWhere(['like','remark',$remark]);
        if($start_time) $query->andWhere(['>=','operate_time',strtotime($start_time)]);
        if($end_time) $query->andWhere(['<=','operate_time',strtotime($end_time)]);

        if($export == 'csv'){
            $rs = $query->orderBy('id desc')->asArray()->all();
            export_csv($rs);
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();

        $col = Yii::$app->db->createCommand("select column_name,column_comment from information_schema.columns where table_schema ='srun4k' and table_name = 'bills'")->queryAll();

        $re['models'] = $models;
        $re['pages'] = $pages;
        $re['col'] = $col;
        $re['get'] = $get;

        return $re;
    }
}