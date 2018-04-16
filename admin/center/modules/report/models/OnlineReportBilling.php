<?php

namespace center\modules\report\models;


use Yii;
use yii\db\Query;
use center\extend\Tool;
use center\modules\strategy\models\Billing;
use center\modules\report\models\base\BaseModel;

/**
 * This is the model class for table "online_report_billing".
 *
 * @property integer $report_id
 * @property integer $time_point
 * @property integer $billing_id
 * @property integer $count
 * @property integer $bytes_in
 * @property integer $bytes_out
 * @property integer $time_long
 */
class OnlineReportBilling extends BaseModel
{
   public $base = 'billing_id';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_report_billing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_At', 'stop_At'], 'required'],
            [['start_At', 'stop_At', 'unit', 'type'], 'string'],
            ['step', 'integer', 'min' => 1],
            //[['time_point', 'billing_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'required'],
            [['time_point', 'billing_id', 'count', 'bytes_in', 'bytes_out', 'time_long'], 'integer']
        ];
    }
}
