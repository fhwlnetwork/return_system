<?php
/**
 * Created by PhpStorm.
 * User: DM
 * Date: 17/4/19
 * Time: 14:03
 */

namespace center\modules\report\controllers;

use center\modules\report\models\Financial;
use Hisune\EchartsPHP\Config;
use Yii;
use yii\helpers\BaseJson;
use Hisune\EchartsPHP\ECharts;

class FinancialMController extends FinancialController
{
    public function actionProduct()
    {
        $model = new Financial();
        $post = Yii::$app->request->post();
        //默认提交按天统计和当天的日期
        $post['Financial']['statistical_cycle'] = !isset($post['Financial']['statistical_cycle']) ? 'day' : $post['Financial']['statistical_cycle'];

        $post['Financial']['start_time_day'] = $post['Financial']['statistical_cycle'] == 'day' && empty($post['Financial']['start_time_day']) ? date('Y-m-d') : $post['Financial']['start_time_day'];

        $params = $post['Financial'];

        //产品数据
        $products = $model->getProNames();

        //默认session选择的产品
        if (Yii::$app->session->get('searchProductField')) {
            $session_products = array_keys(Yii::$app->session->get('searchProductField'));
        } else {
            $session_products = [];
        }

        //如果搜索的产品没有勾选, 则查询session里的产品，如果session里没有则不查询.
        $products_report = [];
        if (isset($params['show_products'])) {
            foreach ($params['show_products'] as $val) {
                $products_report[$val] = $products[$val];
            }
        } else {
            foreach ($session_products as $val) {
                $products_report[$val] = $products[$val];
            }
        }

        if ($model->load($post) && $model->validate()) {
            //将查询的数据保存在session中
            Yii::$app->session->set('searchProductField', $products_report);
        }

        if(Yii::$app->request->isPost){
            if($model->statistical_cycle == 'day'){
                // 饼图
//                $echarts = $this->getIncomeByDay($params);
                // 柱状图
//                $echarts = $this->getIncomeByDay_bar($params);
                // 饼图
                $echarts = $this->getIncomeByDay_pie($params);
            }elseif ($model->statistical_cycle == 'year'){
                $echarts = $this->getIncomeByYear($params);
            }elseif ($model->statistical_cycle == 'week'){
                $echarts = $this->getIncomeByWeek($params);
            }else{
                $echarts = $this->defaultIncome();
            }
        }else{
            $echarts = $this->defaultIncome();
        }

        return $this->render('/financial/product-m', [
            'params' => $params,
            'model' => $model,
            'showField' => $products,
            'searchField' => $session_products,
            'echarts' => BaseJson::encode($echarts),
        ]);
    }

    // 获取默认统计方式 饼图
    private function defaultIncome(){
        // 默认全部 按天统计 今天 前5个产品
        // 要查询的产品列表
        $model = new Financial();
        $products = $model->getProNames();

        if($products){
            $i = 0;
            foreach ($products as $k => $v){
                if($i == 5) break;
                $i++;
                $values = $model->getIncomeByDefault($k);
                $option['series']['data'][] = ['name'=>$k . ':' . $v,'value'=> (bool)$values['pay_num'] ? $values['pay_num'] : 0];
                $option['legend']['data'][] = $k . ':' . $v;
            }
        }
        $option['legend']['orient'][] = 'vertical';
        $option['legend']['left'][] = 'left';

        $option['series']['name'] = Yii::t('app','count_day');
        $option['series']['type'] = 'pie';
        $option['series']['radius'] = '55%';

        $option['title']['text'] = Yii::t('app','count_day');
        $option['title']['x'] = 'center';
        $option['tooltip']['axisPointer']['label']['show'] = true;

        $option['toolbox']['show'] = true;
        $option['toolbox']['feature']['mark']['show'] = true;
        $option['toolbox']['feature']['dataView']['show'] = true;
        $option['toolbox']['feature']['dataView']['readOnly'] = false;
        $option['toolbox']['feature']['magicType']['show'] = true;
//        $option['toolbox']['feature']['magicType']['type'] = ['line','bar'];
        $option['toolbox']['feature']['restore']['show'] = true;
        $option['toolbox']['feature']['saveAsImage']['show'] = true;
        $option['toolbox']['show'] = true;
        $option['toolbox']['show'] = true;

        return $option;
    }

    // 按天统计 饼图 default
    private function getIncomeByDay($params){
        $model = new Financial();
        $products = $model->getProNames();

        $product_ids = $params['show_products'];
        if($product_ids){
            foreach ($product_ids as $k => $v){
                $values = $model->getIncomeByDefault($v,strtotime($params['start_time_day']));
                $option['series']['data'][] = ['name'=>$products[$v],'value'=> (bool)$values['pay_num'] ? $values['pay_num'] : 0];
                $option['legend']['data'][] = $products[$v];
            }
        }
        $option['series']['name'] = '按天统计';
        $option['series']['type'] = 'pie';
        $option['series']['radius'] = '55%';

        $option['title']['text'] = '按天统计';
        $option['tooltip']['axisPointer']['label']['show'] = true;

        return $option;
    }

    // 按天统计 柱状图
    private function getIncomeByDay_bar($params){
        $model = new Financial();
        $products = $model->getProNames();

        $product_ids = $params['show_products'];
        if($product_ids){
            $p = [];
            foreach ($product_ids as $k => $v){
                $p[$v] = $products[$v];
            }
            $rs = $model->getIncomeByDay($params, $p);
            $option['legend']['data'] = ['产品收入'];
            $option['legend']['itemGap'] = 5;
            $option['xAxis']['data'] = $rs['xaxis_data'];
            $option['yAxis']['type'] = 'value';
            $option['series']['data'] = $rs['series_data'];
            $option['series']['name'] = '产品收入';
            $option['series']['type'] = 'bar';

            $option['title']['text'] = $rs['title_text'];
            $option['tooltip']['trigger'] = 'axis';
            $option['tooltip']['axisPointer']['type'] = 'shadow';
            $option['tooltip']['axisPointer']['label']['show'] = true;
        }

        return isset($option) ? $option : '';
    }

    // 按天统计 饼图
    private function getIncomeByDay_pie($params){
        $model = new Financial();
        $products = $model->getProNames();

        $product_ids = $params['show_products'];
        if($product_ids){
            $p = [];
            foreach ($product_ids as $k => $v){
                $p[$v] = $products[$v];
            }
            $rs = $model->getIncomeByDay($params, $p);
            foreach ($rs['series_data'] as $kk => $vv) {
                $option['series']['data'][] = ['name'=>$rs['xaxis_data'][$kk],'value'=>$vv];
            }
            $option['legend']['data'] = $rs['xaxis_data'];
            $option['series']['name'] = Yii::t('app','product_income');
            $option['series']['type'] = 'pie';
            $option['series']['radius'] = '55%';

            $option['legend']['orient'][] = 'vertical';
            $option['legend']['left'][] = 'left';
            $option['title']['x'] = 'center';

            $option['title']['text'] = $rs['title_text'];
            $option['tooltip']['axisPointer']['type'] = 'shadow';
            $option['tooltip']['axisPointer']['label']['show'] = true;

            $option['toolbox']['show'] = true;
            $option['toolbox']['feature']['mark']['show'] = true;
            $option['toolbox']['feature']['dataView']['show'] = true;
            $option['toolbox']['feature']['dataView']['readOnly'] = false;
            $option['toolbox']['feature']['magicType']['show'] = true;
//            $option['toolbox']['feature']['magicType']['type'] = ['line','bar'];
            $option['toolbox']['feature']['restore']['show'] = true;
            $option['toolbox']['feature']['saveAsImage']['show'] = true;
            $option['toolbox']['show'] = true;
            $option['toolbox']['show'] = true;
        }

        return isset($option) ? $option : '';
    }

    // 按周统计 柱状图
    private function getIncomeByWeek($params){
        $model = new Financial();
        $products = $model->getProNames();

        $product_ids = $params['show_products'];
        if($product_ids){
            $p = [];
            foreach ($product_ids as $k => $v){
                $p[$v] = $products[$v];
            }
            $rs = $model->getIncomeByWeek($params, $p);
            $option['legend']['data'] = $rs['legend_data'];
            $option['legend']['itemGap'] = 5;
            $option['xAxis']['data'] = $rs['xaxis_data'];
            $option['yAxis']['type'] = 'value';
            $option['series'] = $rs['series'];

            $option['title']['left'] = '9%';

            $option['title']['text'] = Yii::t('app','count_week');
            $option['tooltip']['trigger'] = 'axis';
            $option['tooltip']['axisPointer']['type'] = 'shadow';
            $option['tooltip']['axisPointer']['label']['show'] = true;

            $option['toolbox']['show'] = true;
            $option['toolbox']['feature']['mark']['show'] = true;
            $option['toolbox']['feature']['dataView']['show'] = true;
            $option['toolbox']['feature']['dataView']['readOnly'] = false;
            $option['toolbox']['feature']['magicType']['show'] = true;
            $option['toolbox']['feature']['magicType']['type'] = ['line','bar'];
            $option['toolbox']['feature']['restore']['show'] = true;
            $option['toolbox']['feature']['saveAsImage']['show'] = true;
            $option['toolbox']['show'] = true;
            $option['toolbox']['show'] = true;
        }

        return isset($option) ? $option : '';
    }

    // 按年统计 柱状图
    private function getIncomeByYear($params){
        $model = new Financial();
        $products = $model->getProNames();

        $product_ids = $params['show_products'];
        if($product_ids){
            $p = [];
            foreach ($product_ids as $k => $v){
                $p[$v] = $products[$v];
            }
            $rs = $model->getIncomeByYear($params, $p);
            $option['legend']['data'] = $rs['legend_data'];
            $option['legend']['itemGap'] = 5;
            $option['xAxis']['data'] = $rs['xaxis_data'];
            $option['yAxis']['type'] = 'value';
            $option['series'] = $rs['series'];

            $option['title']['left'] = '9%';

            $option['title']['text'] = Yii::t('app','count_year');
            $option['tooltip']['trigger'] = 'axis';
            $option['tooltip']['axisPointer']['type'] = 'shadow';
            $option['tooltip']['axisPointer']['label']['show'] = true;

            $option['toolbox']['show'] = true;
            $option['toolbox']['feature']['mark']['show'] = true;
            $option['toolbox']['feature']['dataView']['show'] = true;
            $option['toolbox']['feature']['dataView']['readOnly'] = false;
            $option['toolbox']['feature']['magicType']['show'] = true;
            $option['toolbox']['feature']['magicType']['type'] = ['line','bar'];
            $option['toolbox']['feature']['restore']['show'] = true;
            $option['toolbox']['feature']['saveAsImage']['show'] = true;
            $option['toolbox']['show'] = true;
            $option['toolbox']['show'] = true;
        }

        return isset($option) ? $option : '';
    }

    // echarts-php
    public function actionEcharts(){
        Config::addExtraScript('macarons.js', 'http://echarts.baidu.com/asset/theme/');
        $chart = new ECharts();
        $chart->tooltip->show = true;
        $chart->legend->data[] = '销量';
        $chart->xAxis[] = array(
            'type' => 'category',
            'data' => array("衬衫","羊毛衫","雪纺衫","裤子","高跟鞋","袜子")
        );
        $chart->yAxis[] = array(
            'type' => 'value'
        );
        $chart->series[] = array(
            'name' => '销量',
            'type' => 'bar',
            'data' => array(5, 20, 40, 10, 10, 20)
        );
        echo $chart->render('simple-custom-id',['style' => 'width:600px;height:500px'],'macarons');
    }
}