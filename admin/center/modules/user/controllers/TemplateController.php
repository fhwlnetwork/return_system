<?php
/**
 * 开户模板控制器
 */
namespace center\modules\user\controllers;
use center\modules\strategy\models\Strategy;
use yii;
use center\controllers\ValidateController;
use center\modules\user\models\Template;
use yii\data\Pagination;
use common\models\User;
use yii\web\NotFoundHttpException;
use center\modules\strategy\models\Product;
use center\modules\setting\models\ExtendsField;
use center\modules\user\models\Base;
use common\models\Redis;
use center\modules\strategy\models\Package;
use center\modules\financial\models\PayType;


class TemplateController extends ValidateController
{
    /**
     * 模板列表
     * 
     * @return Ambigous <string, string>
     */
    public function actionIndex()
    {
        //请求的参数
        $params = Yii::$app->getRequest()->queryParams;

        //加载开户模板
        $temModel = new Template();

        //获取可使用的开户模板列表
        $allTemList = $temModel->getValidList();

//        //判断超管
//        if (User::isSuper()){
//            // 获取记录数，偏移量及记录数
//            $total = count($allTemList['all']);
//        }else{
//            unset($allTemList['all']);
//            $total = count($allTemList['self']);
//        }
        $total = count($allTemList['all']);

        $pagesSize = 10; // 每页条数
        $pages = new Pagination(['totalCount' => $total, 'pageSize' => $pagesSize]);
        $offset = $pages->offset;

        $list = $temModel->getList($offset, $pagesSize);

        return $this->render('index',[
            'list' => $list,
            'model' => $temModel,
            'pagination'=> $pages,
        ]);
    }
    
    /**
     * 修改模板
     * 
     * @param int $id
     * @throws NotFoundHttpException
     * @throws yii\web\ForbiddenHttpException
     * @return Ambigous <string, string>
     */
    public function actionEdit($id)
    {
        $params = Yii::$app->request->post();
        $id = intval($id);
        //加载开户模板
        $model = new Template();
        $record = $model->getOne($id);
        if (!$record) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        $model->setAttributes($record, false);
        $model->setAttributes($record['content'], false);
        $model->scenario = 'update';
        if ($model->load($params) && $model->validate()) {
            $content = array(
                'user_allow_chgpass' => $params['Template']['user_allow_chgpass'],
                'user_available' => $params['Template']['user_available'],
                'user_expire_time' => strtotime($params['Template']['user_expire_time']),
                'group_id' => $params['Template']['group_id'],
                'cert_type' => $params['Template']['cert_type'],
                'user_type' => $params['Template']['user_type'],
                'type' => $params['Template']['type'],
            );

            if ($params['Template']['products_id']){
                foreach ($params['Template']['products_id'] as $key => $value){
                    //按顺序保存（拉动后的产品顺序）
                    $content['products_id'][$key] = $key;
                }
            }
            $model->type = $params['Template']['type'];
            $model->content = $content;
            $model->setAttributes($content, false);
            $model->user_expire_time = $params['Template']['user_expire_time'];
            $model->products_id = $content['products_id'];
            $res = $model->save($id, '');
            if($res){
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
                return $this->redirect(array('edit','id'=>$id));
            }
        }

        //判断组织结构和产品是否可用
        //判断组织结构
        if (!User::canManage('org', $model->group_id)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 1'));
        }
        //判断产品
        if ($model->products_id) {
            foreach ($model->products_id as $pid) {
                if (!User::canManage('product', $pid)) {
                    throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 2'));
                }
            }
        }

        //获取产品列表
        $productList = (new Product())->getNameOfList();
        /*
         *修改页面如果通过拖拉的方式更改产品排序，（必须勾选2个以上）保存后产品顺序将发生改变
         * */
        //判断 $record['content']['products_id']是否为空且2个以上
        if(!empty($record['content']['products_id']) && count($record['content']['products_id']) > 1){
            foreach($productList as $k=>$v){
                $get = array_search($k,$record['content']['products_id']);
                if($get){
                    $get_arr[$k] = $v;
                    unset($productList[$k]);
                }
            }

            $get_arrs = array_combine(array_keys($record['content']['products_id']),$get_arr);

            foreach($get_arrs as $k=>$v){
                $get_arrs[$k] = $get_arr[$k];
            }
            $productList = $get_arrs + $productList;
        }

        return $this->render('edit',[
            'model' => $model,
            'action' => 'edit',
            'productList' => $productList,
        ]);
    }

    /*
     *添加模板
     *
     * */
    public function actionAddTemp()
    {
        $model = new Base();
        $params = Yii::$app->request->post();
        //场景
        $model->scenario = 'addtem';
        if ($model->load(Yii::$app->request->post()) && $model->validate() == false) {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //判断用户组和产品不能为空
            $hashKeyPre = 'hash:user:template:';
            $idKey = 'key:user:template:id';
            $listKey = 'list:user:template';
            $id = Redis::executeCommand('INCR', $idKey);
            $content = array(
                'user_allow_chgpass'=>$params['Base']['user_allow_chgpass'],
                'user_available'=>$params['Base']['user_available'],
                'user_expire_time'=>$params['Base']['user_expire_time'],
                'group_id'=>$params['Base']['group_id'],
                'cert_type'=>$params['Base']['cert_type'],
                'user_type'=>$params['Base']['user_type'],
                'products_id'=>$params['Base']['products_id'],
            );
            $data = [
                'id' => $id,
                'type' =>$params['Template']['type'],
                'create' => Yii::$app->user->identity->username,
                'content' => json_encode($content),
                'name' =>$params['Base']['temName'],
            ];
            $strategy = new Strategy();
            $isExists = $strategy->isExists($id);
            $data = Redis::arrayToHash($data);
           try{
               //生成hash值
               Redis::executeCommand('HMSET', $hashKeyPre.$id, $data);
               if(!$isExists){
                   Redis::executeCommand('LREM', $listKey, [0, $id]);
                   Redis::executeCommand('LPUSH', $listKey, [$id]);
               }
               Redis::executeCommand('LPUSH', 'list:user:template',[$id]);
               Yii::$app->getSession()->setFlash('success', Yii::t('app', 'operate success.'));
               return $this->redirect(['index']);
           }catch(\Exception $e){
               Yii::$app->getSession()->setFlash('error', Yii::t('app', 'operate failed.'));
           }
        }
        //获取产品列表
        $productList = (new Product())->getNameOfList();
        //获取套餐列表
        $packageList = (new Package())->getList();
        //默认过期时间
        $model->user_expire_time == 0 && $model->user_expire_time = '';

        return $this->render('add', [
            'model' => $model,
            'productList' => $productList,
            'packageList' => $packageList,
        ]);
    }



    /**
     * 删除模板
     * 
     * @param int $id
     * @throws NotFoundHttpException
     * @throws yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $id = intval($id);
        
        //加载开户模板
        $model = new Template();
        
        $record = $model->getOne($id);
        if (!$record) {
            throw new NotFoundHttpException(Yii::t('app', 'No results found.'));
        }
        
        //判断组织结构和产品是否可用
        //判断组织结构
        if (!User::canManage('org', $model->group_id)) {
            throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 3'));
        }
        
        //判断产品
        if ($model->products_id) {
            foreach ($model->products_id as $pid) {
                if (!User::canManage('product', $pid)) {
                    throw new yii\web\ForbiddenHttpException(Yii::t('app', 'message 401 4'));
                }
            }
        }
        $model->deleteOne($id);
        
        return $this->redirect('index');
    }
}

?>