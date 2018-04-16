<?phpnamespace center\modules\report\models\detail;use Yii;/** * This is the model class for table "srun_detail_hour". * * @property string $id * @property integer $date * @property integer $hour * @property string $user_name * @property string $bytes_in * @property string $bytes_out * @property string $bytes_in6 * @property string $bytes_out6 * @property integer $products_id * @property integer $billing_id * @property integer $control_id * @property double $user_balance * @property string $total_bytes * @property integer $time_long * @property integer $user_login_count * @property integer $user_group_id */class SrunDetailHour extends \yii\db\ActiveRecord{    /**     * @inheritdoc     */    public static function tableName()    {        return 'srun_detail_hour';    }    /**     * @inheritdoc     */    public function rules()    {        return [            [['date', 'hour', 'bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'products_id', 'billing_id', 'control_id', 'total_bytes', 'time_long', 'user_login_count', 'user_group_id'], 'integer'],            [['bytes_in', 'bytes_out', 'bytes_in6', 'bytes_out6', 'products_id', 'billing_id', 'control_id', 'total_bytes', 'time_long', 'user_login_count'], 'required'],            [['user_balance'], 'number'],            [['user_name'], 'string', 'max' => 64],        ];    }    /**     * @inheritdoc     */    public function attributeLabels()    {        return [            'id' => 'ID',            'date' => 'Date',            'hour' => 'Hour',            'user_name' => 'User Name',            'bytes_in' => 'Bytes In',            'bytes_out' => 'Bytes Out',            'bytes_in6' => 'Bytes In6',            'bytes_out6' => 'Bytes Out6',            'products_id' => 'Products ID',            'billing_id' => 'Billing ID',            'control_id' => 'Control ID',            'user_balance' => 'User Balance',            'total_bytes' => 'Total Bytes',            'time_long' => 'Time Long',            'user_login_count' => 'User Login Count',            'user_group_id' => 'User Group ID',        ];    }}