<?php
/**
 * 用户交流控制器
 */
namespace center\modules\user\controllers;

use Yii;
use center\controllers\ValidateController;
use center\modules\user\models\UserCloundInteraction;
use yii\data\Pagination;
use center\modules\user\models\Base;
use common\extend\Tool;
use yii\web\UploadedFile;


class InteractionController extends ValidateController
{
    const  INTERACTION_PUSH_CLOUD_API = '/interaction/push'; //推送到云端的接口
    const  INTERACTION_LIST_CLOUD_API = '/interaction/list'; //获取云端投诉数据接口
    const  INTERACTION_DETAIL_CLOUD_API = '/interaction/detail';

    //是否通过验证
    public function getAuth()
    {
        $config = Yii::$app->params['srunCloudApi'];
        $prefix = $config['url'] . $config['version'][0];
        $url = $prefix . $config['version']['login'];

        $data = [
            'username' => Yii::$app->params['dbConfig']['products_key'],
            'password' => Yii::$app->params['dbConfig']['products_password'],
        ];
        if (empty($data['username']) || empty($data['password'])) {
            Yii::$app->getSession()->setFlash('error',Yii::t('app', 'message Invalid Param'));

            return false;
        }
        $data = [
            'username' => Yii::$app->params['dbConfig']['products_key'],
            'password' => md5(Yii::$app->params['dbConfig']['products_password']),
        ];

        $rs = Tool::postApi($url, http_build_query($data));
        $rs = json_decode($rs, true);
        //判断json解析是否出错
        $errorMessage = json_last_error() ? json_last_error_msg() : '';
        if ($errorMessage) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'json parser error') . $errorMessage);

            return false;
        }
        if (!empty($rs)) {
            return array_merge($rs, ['prefix' => $prefix]);
        } else {
            return false;
        }
    }

    /**
     * 查看用户投诉
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $model = new UserCloundInteraction();
        $attributes = $model->getAttributesList();
        $questionTypes = isset($attributes['questionTypes']) ? $attributes['questionTypes'] : [];
        $questionStates = isset($attributes['questionStates']) ? $attributes['questionStates'] : [];
        $list = $data = [];

        $rs = $this->getAuth();
        //获取云端投诉数据
        if (!empty($rs) && $rs['code'] == 0) {
            $params['access_token'] = $rs['data']['access_token'];
            $params['uid'] = $rs['data']['uid'];
            $keys = http_build_query($params);
            $keys = '?' . $keys;
            $url = $rs['prefix'] . self::INTERACTION_LIST_CLOUD_API . $keys;
            $json = Tool::postApi($url, '', 'get');

            $data = json_decode($json, true);
            //判断json解析是否出错
            $errorMessage = json_last_error() ? json_last_error_msg() : '';
            if ($errorMessage) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'json parser error') . $errorMessage);
            }

        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'service help1'));
        }
        //分页
        //一页多少条
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => 0,
        ]);
        if (!empty($data) && $data['code'] == 0) {
            $list = $data['data'];
            if (!empty($list)) {
                unset($list['count']);
                foreach ($list as &$v) {
                    unset($v['question_content']);
                    unset($v['question_answer']);
                    $v['question_type'] = isset($questionTypes[$v['question_type']]) ? $questionTypes[$v['question_type']] : '';
                    $v['question_state'] = isset($questionStates[$v['question_state']]) ? $questionStates[$v['question_state']] : '';
                }
            }
            $pagination = new Pagination([
                'defaultPageSize' => $offset,
                'totalCount' => $data['data']['count'],
            ]);
        }


        return $this->render('index', [
            'model' => $model,
            'list' => $list,
            'questionTypes' => $questionTypes,
            'questionStates' => $questionStates,
            'pagination' => $pagination
        ]);
    }

    /**
     * 查看问题内容
     * @param $id  投诉id
     * @return string
     */
    public function actionView($id)
    {
        $rs = $this->getAuth();
        $model = new UserCloundInteraction();
        $attributes = $model->getAttributesList();
        $questionTypes = isset($attributes['questionTypes']) ? $attributes['questionTypes'] : [];
        $questionStates = isset($attributes['questionStates']) ? $attributes['questionStates'] : [];
        $params['id'] = $id;
        $data = [];
        if (!empty($rs) && $rs['code'] == 0) {
            $url = $rs['prefix'] . self::INTERACTION_DETAIL_CLOUD_API;
            $params['access_token'] = $rs['data']['access_token'];
            $params['uid'] = $rs['data']['uid'];
            $response = Tool::postApi($url, http_build_query($params));
            $res = json_decode($response, true);
            //判断json解析是否出错
            $errorMessage = json_last_error() ? json_last_error_msg() : '';
            if ($errorMessage) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'json parser error') . $errorMessage);
            }

            if (!empty($res) && $res['code'] == 0) {
                //获取成功
                $data = $res['data'];
                foreach ($data as $k => $v) {
                    if ($k == 'question_pub_at') {
                        $v = date('Y-m-d H:i:s', $v);
                    }
                    $model->$k = $v;
                }
            } else {
                //获取失败
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'this record is not exist'));

                return $this->redirect('index');
            }
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'cloud auth failure'));

            return $this->redirect('index');
        }

        return $this->render('view', [
            'model' => $model,
            'action' => 'view',
            'questionTypes' => $questionTypes,
            'questionStates' => $questionStates,
        ]);
    }

    /**
     * 客户向云端推送投诉信息
     * @return \yii\web\Response
     */
    public function actionAdd()
    {
        $model = new UserCloundInteraction();
        $params = Yii::$app->request->post();
        $params = isset($params['UserCloundInteraction']) ? $params['UserCloundInteraction'] : [];
        //是否通过验证，获取token
        $rs = $this->getAuth();
        if (!empty($rs) && $rs['code'] == 0) {
            //通过验证
            $url = $rs['prefix'] . self::INTERACTION_PUSH_CLOUD_API;
            $params['access_token'] = $rs['data']['access_token'];
            $params['uid'] = $rs['data']['uid'];
            $response = Tool::postApi($url, http_build_query($params));
            $res = json_decode($response, true);
            //判断json解析是否出错
            $errorMessage = json_last_error() ? json_last_error_msg() : '';
            if ($errorMessage) {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'json parser error') . $errorMessage);
            }

            if ($res['code'] == 0) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'interaction push success'));
            } else {
                Yii::$app->getSession()->setFlash('error', $res['message']);
            }
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'cloud auth failure'));
        }
        return $this->redirect('index');
    }
    //166云端查看所有的
    public function actionIndexAll()
    {
        $params = Yii::$app->request->queryParams;
        $model = new UserCloundInteraction();
        $attributes = $model->getAttributesList();
        $questionTypes = isset($attributes['questionTypes']) ? $attributes['questionTypes'] : [];
        $questionStates = isset($attributes['questionStates']) ? $attributes['questionStates'] : [];
        $query = $model->find();

        if (!empty($params['products_key'])) {
            $value = $params['products_key'];
            $query->andWhere("products_key LIKE :prod", [":prod" => "%$value%"]);
        }
        if (!empty($params['question_pub_time'])) {
            $value = strtotime($params['question_pub_time']);
            $query->andWhere("question_pub_at > :pub", [":pub" => $value]);
        }
        if (!empty($params['question_type'])) {
            $value = $params['question_type'];
            $query->andWhere("question_type = :type", [":type" => $value]);
        }
        if (!empty($params['question_state']) || preg_match('/^0$/', $params['question_state'])) {
            $value = $params['question_state'];
            $query->andWhere("question_state = :state", [":state" => $value]);
        }
        //分页
        //一页多少条
        $offset = isset($params['offset']) && $params['offset'] > 0 ? $params['offset'] : 10;
        $pagination = new Pagination([
            'defaultPageSize' => $offset,
            'totalCount' => $query->count(),
        ]);
        $params['showField'] = $model->defaultField;
        $query->addSelect('id');
        foreach ($params['showField'] as $val) {
            if (array_key_exists($val, $model->searchField)) {
                //将搜索字段压入新数组
                $sortField[$val] = $model->searchField[$val];
                $validParam[] = $val; //把有效的数据再压入搜索中
                $query->addSelect($val);
            }
        }

        //排序
        if (isset($params['orderBy']) && array_key_exists($params['orderBy'], $model->searchField)) {
            $query->orderBy([$params['orderBy'] => $params['sort'] == 'desc' ? SORT_DESC : SORT_ASC]);
        } else {
            $query->orderBy(['question_pub_at' => SORT_DESC]);
        }
        $list = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        if (!empty($list)) {
            foreach ($list as &$v) {
                $v['question_type'] = isset($questionTypes[$v['question_type']]) ? $questionTypes[$v['question_type']] : '';
                $v['question_state'] = isset($questionStates[$v['question_state']]) ? $questionStates[$v['question_state']] : '';
            }
        }

        return $this->render('index-all', [
            'model' => $model,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params
        ]);
    }

    /**
     * 166管理员处理问题
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        $model = new UserCloundInteraction();
        $attributes = $model->getAttributesList();
        $questionTypes = isset($attributes['questionTypes']) ? $attributes['questionTypes'] : [];
        $questionStates = isset($attributes['questionStates']) ? $attributes['questionStates'] : [];
        $model = $model::findOne($id);
        $model->question_pub_at = date('Y-m-d H:i:s', $model->question_pub_at);



        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $data = $_POST['UserCloundInteraction'];
            $attributes = [
                //需要更新的字段
                'question_answer' => $data['question_answer'],
                'question_state' => $data['question_state'],
                'question_solution_time' => time()
            ];
            $count = UserCloundInteraction::updateAll($attributes, 'id=:id', array(':id' => $id));
            if ($count) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                return $this->redirect('view-all?id=' . $id);
            } else {
                Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
            }
        }


        return $this->render('_form',[
            'model' => $model,
            'questionStates' => $questionStates,
            'questionTypes' => $questionTypes,
            'action' => 'edit',
        ]);
    }

    /**
     * 166云端查看某条记录
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionViewAll($id)
    {
        $model = new UserCloundInteraction();
        $attributes = $model->getAttributesList();
        $questionTypes = isset($attributes['questionTypes']) ? $attributes['questionTypes'] : [];
        $questionStates = isset($attributes['questionStates']) ? $attributes['questionStates'] : [];
        $model = $model::findOne($id);
        $model->question_pub_at = date('Y-m-d H:i:s', $model->question_pub_at);


        return $this->render('_form',[
            'model' => $model,
            'questionStates' => $questionStates,
            'questionTypes' => $questionTypes,
            'action' => 'view-all',
        ]);

    }
}