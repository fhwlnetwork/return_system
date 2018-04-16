<?php

namespace common\models;

use center\models\SignupForm;
use center\modules\auth\models\AuthAssignment;
use center\modules\auth\models\AuthItem;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manager}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
            [['role', 'status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => Yii::t('app', 'Manager Name'),
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => '添加时间',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * 返回主键 ID 值.
     * @param $key 字段名称.
     * @param $val 字段值.
     * @return mixed
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getUserId($key, $val)
    {
        if (($model = self::find()->where([$key => $val])) !== null) {
            return $model->id;
        } else {
            throw new NotFoundHttpException('not found......');
        }
    }

    /**
     * 返回用户表数据的 username 字段值.
     * @param $id 主键ID.
     * @return string
     */
    public static function getUserName($id)
    {
        if ($model = self::findOne($id)) {
            return self::findOne($id)->username;
        } else {
            return 'NULL';
        }
    }

    /**
     * 根据指定的ID 获取相应的字段值.
     * @param $id
     * @param $field
     * @return mixed
     */
    public static function getItemValue($id, $field)
    {
        if ($id == '' || $field == '') {
            return false;
        } else {
            $data = self::find()->where(['id' => $id])->all();
            if($data) {
                return $data[0]['attributes'][$field];
            }
            return null;
        }
    }

    public function batchSave($users)
    {
        $rs = $user_names = $excel_err = $excel_ok = [];
        $flag = true;
        // var_dump(1111,$users);exit;
        if (count($users) > 100) {
            //查询出所有用户name
            $flag = false;
            $userBases = self::find()->select('username')->indexBy('user_name')->asArray()->all();
            $user_names = array_keys($userBases);
        }
        $this->scenario = 'add';
        $nameArr = [];
        $i = 0;
        foreach ($users as $name => $val) {
            if ($flag) {
                if (self::find()->where(['username' => $name])->count() > 0) {
                    $excel_err[] = array($name, Yii::t('app', 'batch add help2'));
                    continue;
                }

            } else {
                if (in_array($name, $user_names)) {
                    $excel_err[] = array($name, Yii::t('app', 'batch add help2'));
                    continue;
                }

            }
            //开始处理单个用户
            $this->oldAttributes = null;
            $model = new SignupForm();
            $AuthItem = new AuthItem();
            $model->password = $val['user_pass_value'];
            $model->username = $name;
            $model->mgr_org = $val['group_id'];
            $model->roles = '3';
            $model->passwords = $val['user_pass_value'];
            $model->email = '';
            $model->begin_time = $val['begin_time'] ? $val['begin_time'] : 0;
            $model->stop_time = $val['stop_time'] ? $val['stop_time'] : 0;
            $model->major_id = $val['major_id'] ? $val['major_id'] : 0;
            if ($user = $model->signup()) {
                //管理员添加成功
                $model->mgr_product = ($model->mgr_product) ? implode(',', $model->mgr_product) : ''; // 可管理的产品
                $model->log(null, $model->attributes, $insert = true); // 写 manager 表操作日志

                $AuthAssignment = new AuthAssignment(); //将数据添加到 auth_assignment 表
                $AuthAssignment->item_name = '学生'; //添加学生
                $AuthAssignment->user_id = $user->id;
                $AuthAssignment->save();

                $ok = [
                    $user->id,
                    $this->username,
                    $val['user_pass_value'],
                    date('Y-m-d H:i:s'),
                    $user->expire_time != 0 ? date('Y-m-d', $user->expire_time) : Yii::t('app', 'user expire time2'),
                    Yii::$app->user->identity->username,
                    '添加成功'
                ];
                $excel_ok[] = $ok;
            } else {
                //管理员添加失败
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'add failure'));
                $ok = [
                    '',
                    $name,
                    $val['user_pass_value'],
                    date('Y-m-d H:i:s'),
                    $this->expire_time != 0 ? date('Y-m-d', $this->expire_time) : Yii::t('app', 'user expire time2'),
                    Yii::$app->user->identity->username,
                    '失败',
                ];
                $excel_err[] = $ok;
            }
        }

        return ['excel_ok' => $excel_ok, 'excel_err' => $excel_err];
    }
}
