<?php

namespace common\models;

use center\modules\user\models\Base;
use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "clound_srun_login_log".
 *
 * @property string $id
 * @property string $product_key
 * @property string $error_count
 * @property string $error_count1
 * @property string $error_count2
 * @property string $error_count3
 * @property string $error_count4
 * @property integer $statistics_time
 */
class CloundSrunLoginLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'clound_srun_login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_key', 'statistics_time'], 'required'],
            [['error_count', 'error_count1', 'error_count2', 'error_count3', 'error_count4', 'statistics_time'], 'integer'],
            [['product_key'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_key' => 'Product Key',
            'error_count' => 'Error Count',
            'error_count1' => 'Error Count1',
            'error_count2' => 'Error Count2',
            'error_count3' => 'Error Count3',
            'error_count4' => 'Error Count4',
            'statistics_time' => 'Statistics Time',
        ];
    }

    public function getAllData($params)
    {
        $newParams = [];
        $newParams[':sta'] = isset($params['start_time']) ? strtotime($params['start_time']) : time() - 30*60;
        $newParams[':end'] = isset($params['end_time']) ? strtotime($params['end_time']) : time();
        $newParams[':prod'] = isset($params['products_key']) ? $params['products_key'] : '';

        $where = " 1=1";
        if ($newParams[':end'] < $newParams[':sta']) {
            return ['code' => 401, 'error' => Yii::t('app', 'end time error')];
        }
        if ($newParams[':end'] - $newParams[':sta'] > 86400 * 31) {//超过一个月
            return ['code' => 402, 'error' => Yii::t('app', 'time error1')];
        }

        if (!empty($newParams[':sta'])) {
            $where .= ' AND statistics_time >= :sta';
        }
        if (!empty($newParams[':end'])) {
            $where .= ' AND statistics_time <= :end';
        }
        if (!empty($params['exact_tag'])) {
            if (!empty($newParams[':prod']) || preg_match('/^0$/', $newParams[':prod'])) {
                $where .= " AND product_key = :prod";
            }
        } else {
            if (!empty($newParams[':prod']) || preg_match('/^0$/', $newParams[':prod'])) {
                $where .= " AND product_key LIKE :prod";
                $newParams[':prod'] = '%' . $newParams[':prod'] . '%';
            }
        }
        if (empty($newParams[':prod'])) {
            unset($newParams[':prod']);
        }

        $query = $this->find();
        $query->andWhere($where, $newParams);
        $query->addGroupBy('product_key');
        $query->select("product_key,sum(error_count1) as error_count1,sum(error_count2) as error_count2,sum(error_count3) as error_count3,sum(error_count4) as error_count4,sum(error_count) as error_count");
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $count = $query->count();
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $count,
        ]);
        $data = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $user = Base::findOne(['user_name' => $data[$k]['product_key']]);
                $data[$k]['school_name'] = $user ? $user->user_real_name : $data[$k]['product_key'];
            }
            return ['code' => 200, 'list' => $data, 'total'=>$count, 'msg'=>'ok'];
        } else {
            return ['code' => 401, 'error' => Yii::t('app', 'no record')];
        }
    }
}
