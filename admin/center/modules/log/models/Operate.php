<?php
/**
 * Created by PhpStorm.
 * User: ligang
 * Date: 2015/1/13
 * Time: 15:23
 */

namespace center\modules\log\models;

use center\extend\Tool;
use phpDocumentor\Reflection\Exception;
use yii;
use yii\db\ActiveRecord;

class Operate extends ActiveRecord
{
    //每页显示数量
    public $perPage = 10;
    //本次请求的session值
    public $sessionKey = '';

    /**
     * 表名称
     * @return string
     */
    public static function tableName()
    {
        return 'log_operate';
    }

    //允许搜索的字段
    private $_searchFiled = null;
    public function getSearchField()
    {
        if(!is_null($this->_searchFiled)){
            return $this->_searchFiled;
        }
        $this->_searchFiled = [
            'operator' => Yii::t('app', 'operator'),
            'target' => Yii::t('app', 'target'),
            'action' => Yii::t('app', 'action'),
            'action_type' => Yii::t('app', 'action type'),
            'content' => Yii::t('app', 'operate content'),
            'opt_ip' => Yii::t('app', 'operate ip'),
            'opt_time' => Yii::t('app', 'operate time'),
        ];
        return $this->_searchFiled;
    }
    public function setSearchField($data){
        $this->_searchFiled = $data;
    }

    public static function getAttributesList()
    {
        return [
            //动作类型
            'action' => [
                '' => Yii::t('app', 'action select'),
                'add' => Yii::t('app', 'action add'),
                'edit' => Yii::t('app', 'action edit'),
                'delete' => Yii::t('app', 'action delete'),
                'pay' => Yii::t('app', 'action pay'),
                'self-pay' => Yii::t('app', 'action self pay'),
                'refund' => Yii::t('app', 'action refund'),
                'checkout' => Yii::t('app', 'action checkout'),
                'drop' => Yii::t('app', 'action drop'),
                'export' => Yii::t('app', 'action export'),
                'cancelProduct' => Yii::t('app', 'action cancelProduct'),
                'changeProduct' => Yii::t('app', 'action changeProduct'),
                'audit' => Yii::t('app', 'action audit'),
            ]
        ];
    }

    /**
     * 搜索的输入框，键作为输入框name，label是标签，id作为输入框的id
     * @return array
     */
    public function getSearchInput()
    {
        return [
            'operator' => [
                'label' => Yii::t('app', 'operator')
            ],
            'target' => [
                'label' => Yii::t('app', 'target')
            ],
            'action' => [
                'label' => Yii::t('app', 'action'),
                'type' => 'dropList',
            ],
            'start_opt_time' => [
                'label' => Yii::t('app', 'start opt time'),
                'type' => 'date',
            ],
            'end_opt_time' => [
                'label' => Yii::t('app', 'end opt time'),
                'type' => 'date',
            ],
            'opt_ip' => [
                'label' => Yii::t('app', 'operate ip')
            ],
        ];
    }

    /**
     * 图标样式
     * @param $action_type string 操作类型
     * @return array
     */
    public function getActionIcoType($action_type)
    {
        $module = 'Default';
        if(!empty($action_type)){
            $arr = explode(' ', $action_type);
            $module = isset($arr[0]) ? $arr[0] : 'default';
        }
        $icoArr = [
            'User' => 'fa-user',
            'Strategy' => 'fa-sitemap',
            'Financial' => 'fa-usd',
            'Message' => 'fa-envelope-o',
            'Setting' => 'fa-cogs',
            'Default' => 'fa-pencil-square-o', //默认
        ];
        return isset($icoArr[$module]) ? $icoArr[$module] : $icoArr['Default'];
    }
    //上次搜索的最小的id
    private $_lastId = 0;
    public function getLastId()
    {
        return $this->_lastId;
    }

    /**
     * 处理list列表成指定的形式，主要是处理了时间和日志内容
     * @param $list
     * @return array
     */
    public function listShow($list)
    {
        if(empty($list)) return [];

        //绑定事件，如果显示的消息内容模板不存在，那么使用默认的模板 operate show default
        yii\base\Event::on(yii\i18n\MessageSource::className(), yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION, function($event){
            $event->translatedMessage = Yii::t('app', 'operate show default');
        });

        foreach ($list as $key => $value) {
            //$ext = Tool::showDateTime($value['opt_time']);
            //根据日志类型显示不同的日志内容；0：格式化日志，1：描述性日志
            //格式化日志
            if($value['type']==0){
                $ext['showContent'] = Yii::t('app', 'operate show '.$value['action_type'], [
                    'operator' => $value['operator'],
                    'action' => Yii::t('app', 'operate action '.$value['action']),
                    'action_type' => Yii::t('app', 'operate type '.$value['action_type']),
                    'target' => $value['target']
                ]);
                //var_dump($ext, $value);exit;
            }
            //描述性日志
            else if($value['type']==1){
                $ext['showContent'] = $value['content'];
            }
            //其他类型不显示
            else{
                $ext['showContent'] = '';
            }

            $list[$key] = yii\helpers\ArrayHelper::merge($ext, $value);
            //最后一个值
            $this->_lastId = $value['id'];
        }

        //解除事件
        yii\base\Event::off(yii\i18n\MessageSource::className(), yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION);

        //如果总数小于每页值，那么代表数据取完了
        if (count($list)<$this->perPage) $this->_lastId = 0;

        return $list;
    }

    /**
     * 将日志列表组合成html形式
     * @param $list
     * @return string
     * @throws yii\base\InvalidConfigException
     */
    public function showHtml($list)
    {
        $show = '';
        //需要区别开不同页面的不同调用
        $dates = [];//已经取出的日志的日期数组
        $dateSession = 'log-operate-dates-'.md5(Yii::$app->request->pathInfo).'-'.md5($this->sessionKey);
        //如果是ajax请求，那么需要先从session中取出日期数组
        if(Yii::$app->request->isAjax){
            $dates = Yii::$app->session->get($dateSession) ? Yii::$app->session->get($dateSession) : [];
        }
        foreach ($list as $value){
            //处理时间格式
            $ext = Tool::showDateTime($value['opt_time']);
            $value = array_merge($value, $ext);

            //小圆图标颜色，添加绿色，编辑黄色，删除红色，其他为蓝色
            if ($value['action'] == 'add') {
                $smallLabelColor = 'bg-success';
            } else if ($value['action'] == 'edit') {
                $smallLabelColor = 'bg-warning';
            } else if ($value['action'] == 'delete') {
                $smallLabelColor = 'bg-danger';
            } else {
                $smallLabelColor = 'bg-primary';
            }

            //是否显示大标签 日期标签：2015-01-20 周二
            if (!in_array($value['day'], $dates)){
                $dates[] = $value['day'];
                Yii::$app->session->set($dateSession, $dates);
                //大日期标签颜色，今天红色，昨天黄色，其他日期都是蓝色
                if ($value['day'] == 'Today') {
                    $bigLabelColor = 'btn-danger';
                } elseif ($value['day'] == 'Yesterday') {
                    $bigLabelColor = 'btn-warning';
                } else {
                    $bigLabelColor = 'btn-primary';
                }

                $show .= '
                        <article class="tl-item">
                            <div class="tl-body">
                                <div class="tl-entry">
                                    <div class="tl-caption">
                                        <a href="javascript:;"  class="btn '.$bigLabelColor.' btn-block">'
                                        .Yii::t('app', $value['day']) . ' ' . ($value['day'] != 'Today' ? $value['week'] : '')
                                        .'</a>
                                    </div>
                                </div>
                            </div>
                        </article>';
            }
            //正常的日志内容
            $show .= '
                <article class="tl-item">
                    <div class="tl-body">
                        <div class="tl-entry">
                            <div class="tl-icon round-icon sm icon-bg '.$smallLabelColor.'">
                                <i class="fa '.$this->getActionIcoType($value['action_type']).'"></i>
                            </div>
                            <div class="tl-content">
                                <div class="row">
                                    <div class="col-sm-2"><span style="color: #999999;" title="'.date('H:i:s', $value['opt_time']).'">'
                                        .$value['time'].'</span>
                                    </div>
                                    <div class="col-sm-10">'
                                        .$value['showContent'];
            //附加显示：详细时间和IP地址
            $showExt = ' ('.date('H:i:s', $value['opt_time']) . ' IP:' .$value['opt_ip'].')';
            //是否显示：查看详情
            if ( $value['type'] == 0 && !empty($value['content']) ){
                //生成详情的表格
                $contentArr = yii\helpers\Json::decode($value['content']);
                if(is_array($contentArr)){
                    $content  = '<table class="table table-bordered table-hover"><thead><tr>';
                    $content .= '<th>'.Yii::t('app', 'property').'</th>';
                    //如果是编辑模式下，那么需要显示旧值
                    $content .= $value['action'] == 'edit' ? '<th>'.Yii::t('app', 'old value').'</th>' : '';
                    $content .= '<th>'.Yii::t('app', 'new value').'</th>';
                    $content .= '</tr></thead>';
                    $content .= '<tbody>';
                    // 解析内容
                    //创建一个对象来获取label的数据
                    $objects = [];//保存已经实例化的对象
                    $labels = [];//字段的标签数组
                    $fieldLabels = [];//已修改的字段数组
                    $attributesList = [];//下拉列表类数组
                    if(!empty($value['class'])){
                        //创建对象
                        if(in_array($value['class'], $objects)){
                            $object = $objects[$value['class']];
                        }else{
                            try{
                                $object = Yii::createObject(['class'=>$value['class']]);
                                $objects[$value['class']] = $object;

                                //获取字段的标签值
                                if(method_exists($object, 'attributeLabels')){
                                    $labels = $object->attributeLabels();
                                }
                                //获取字段的列表值
                                if(method_exists($object, 'getAttributesList')){
                                    $attributesList = $object->getAttributesList();
                                }
                            }catch (Exception $e){

                            }
                        }
                    }
                    foreach ($contentArr as $k => $v) {
                        $content .= '<tr>';
                        $content .= '<td>'.(isset($labels[$k]) ? $labels[$k] : $k).'</td>';
                        if($value['action']=='edit'){
                            $fieldLabels[] = (isset($labels[$k]) ? $labels[$k] : $k);
                        }
                        //值为数组的形式
                        if($value['action'] == 'edit' && is_array($v)){
                            $content .= '<td>'.$this->labelFiled($attributesList, $k, $v[0]).'</td>';//原值
                            $content .= '<td>'.$this->labelFiled($attributesList, $k, $v[1]).'</td>';//新值
                        }
                        //单个值的形式
                        else{
                            if($k == 'user_create_time' || $k == 'user_update_time' || preg_match('/time/', $k)){
                                //用户创建时间及更新时间由时间戳改为标准时间
                                if (is_numeric($v)) {
                                    $content .= '<td>'.$this->labelFiled($attributesList, $k, date('Y-m-d H:i:s',$v)).'</td>';
                                } else {
                                    $content .= '<td>'.$this->labelFiled($attributesList, $k, $v).'</td>';
                                }

                            }else{
                                $content .= '<td>'.$this->labelFiled($attributesList, $k, $v).'</td>';
                            }
                        }
                        $content .= '</tr>';
                    }
                    $content .= '</tbody></table>';

                    $show .= $value['action']=='edit' ? ' ('.implode($fieldLabels, ', ').')' : '';
                    $show .= $showExt;
                    $show .= '<a href="#" class="btn btn-default btn-xs" style="margin-left: 20px;" onclick="showDetail('. $value['id'] . ') "> '
                        .Yii::t('app', 'show detail').'</a>
                          <p></p>
                          <div id="detail'.$value['id'].'" style="display:none">'.$content.'</div>';
                }
            }else{
                $show .= $showExt;
            }

            $show .= '            </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            ';
        }
        //print_r($_SESSION);
        return $show;
    }

    /**
     * 把日志中的字段值进行转换，根据类中定义的getAttributesList()方法
     * @param $attributesList array 列表数组
     * @param $field string 字段名
     * @param $value string 字段值
     * @return string
     */
    public function labelFiled($attributesList, $field, $value)
    {
        $str = [];
        if(is_array($value)){
            foreach($value as $v){
                $str[] = $this->labelFiled($attributesList, $field, $v);
            }
        }else{
            $str[] = isset($attributesList[$field][$value]) ? $attributesList[$field][$value] : $value;
        }
        return $str ? implode($str, ', ') : '';
    }



}