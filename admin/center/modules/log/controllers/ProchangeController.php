<?php

namespace center\modules\log\controllers;



use Yii;
use common\models\User;
use yii\data\Pagination;
use center\controllers\ValidateController;
use center\modules\user\models\Base;
use center\modules\strategy\models\Product;
use center\modules\strategy\models\ProductsChange;
use center\modules\auth\models\SrunJiegou;

/**
 * 产品转移控制器
 * Class ProchangeController
 * @package center\modules\log\controllers
 */
class ProchangeController extends ValidateController
{
    public function actionIndex()
    {
        //产品
        $productModel = new Product();

        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;
        $model = new ProductsChange();
        $query = ProductsChange::find();
        $query->where(['is_del' => 0]);
        if (isset($params['user_name']) && !empty($params['user_name'])) {
            $query->andWhere(['user_name' => $params['user_name']]);
        }
        //如果非超级管理员，则需要去判断
        if (!User::isSuper()) {
            //
            $userBase = new Base();
            $productList = $userBase->can_product;
            $query->andWhere(['group_id' => array_keys($userBase->can_group)]);
            //判断产品
            //所有可以管理的产品
            $proKey = array_keys($productList);
            $query->andWhere(['products_id_from' => $proKey]);
            $query->andWhere(['products_id_to' => $proKey]);
        }
        $query->orderBy(['change_id' => SORT_DESC]);
        //分页
        $pagination = new Pagination([
            'defaultPageSize' => isset($params['perPage']) ? $params['perPage'] : 10,
            'totalCount' => $query->count(),
        ]);

        //列表
        $list = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $newList = $newLists = [];
        foreach ($list as $key => $value) {
            $list[$key]['products_id_from'] = empty($value['products_id_from']) ? '' : !empty($productList[$value['products_id_from']]) ? $productList[$value['products_id_from']] : '';
            $list[$key]['products_id_to'] = empty($value['products_id_to']) ? '' : !empty($productList[$value['products_id_to']]) ? $productList[$value['products_id_to']] : '';
            //用户详情页面显示的方式
            foreach ($value as $k => $v) {
                if ($k == 'operating_date') {
                    $v = date('Y-m-d H:i:s', $v);
                } elseif ($k == 'change_date') {
                    $v = $v ? date('Y-m-d', $v) : Yii::t('app', 'effect next cycle');
                } elseif ($k == 'change_status') {
                    $v = $model->getAttributesList()['change_status'][$v];
                } else {
                    $v = $list[$key][$k];
                }
                if (array_key_exists($k, $model->showField)) {
                    $newList[$key][$k] = $v;
                }
            }
            if ($k == 0) {
                $newLists[0] = array_keys($newList[$key]);
            }
            $newLists[$key + 1] = array_values($newList[$key]);
        }
        //如果是用户详情页面
        //处理ajax请求
        if (isset($params['showType']) && $params['showType'] == 'ajax') {
            if (!empty($newList)) {
                foreach ($newLists[0] as $k => $value) {
                    $newLists[0][$k] = $model->showField[$value];
                }
            }

            return json_encode($newLists);
        }
        return $this->render('index', [
            'model' => $model,
            'product' => $productList,
            'list' => $list,
            'pagination' => $pagination,
            'params' => $params,
        ]);
    }

    public function actionDelete($id)
    {
        $id = intval($id);
        $model = new ProductsChange();
        $data = ProductsChange::find()->where(['change_id'=>$id])->asArray()->one();
        if (!$data) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'No results found.'));
        }
        $res = ProductsChange::updateAll(['is_del'=>1], ['change_id'=>$id]);
        if ($res) {
            $model -> writeDelLog($data);
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
        } else {
            Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'operate failed.'));
        }
        return $this->redirect(['index']);
    }

    public function actionAjaxGetNextProduct(){
        $params = Yii::$app->request->post();
        if(!empty($params['user_id']) && !empty($params['product_id'])){
            $model = new ProductsChange();
            echo $model->getChangeProduct($params['user_id'], $params['product_id']);exit;
        }
        echo false;
    }

    public function actionAjaxDelNextProduct(){
        $params = Yii::$app->request->post();
        if(!empty($params['user_id']) && !empty($params['product_id'])){
            ProductsChange::updateAll(['is_del'=>1], ['change_status'=>0,'user_id'=>$params['user_id'],'products_id_from'=>$params['product_id']]);
            echo true;
        }
        echo false;
    }

}
