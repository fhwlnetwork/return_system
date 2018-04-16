<?php

namespace center\modules\setting\models;

use Yii;
use center\modules\log\models\LogWriter;

/**
 * This is the model class for table "portal".
 *
 * @property integer $id
 * @property string $pc_logo
 * @property string $pc_top_banner
 * @property string $banner
 * @property string $pc_footer
 */
class Portal extends \yii\db\ActiveRecord
{
    public $action;
    public $source_path;
    public $dest_ip;
    public $dest_path;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'portal';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['banner'], 'required', 'on' => 'pc'],
            [['logo', 'top_banner', 'footer', 'examples_name'], 'required', 'on' => ['pc', 'mobile']],
            [['action', 'source_path', 'dest_ip', 'dest_path'], 'required', 'on' => ['give']],
            [['portal_name', 'logo', 'top_banner', 'banner', 'footer', 'action', 'source_path', 'dest_ip', 'dest_path'], 'string'],
            [['type', 'pid'], 'integer'],
            ['examples_name', 'match', 'pattern' => '/^[0-9a-z_]{2,20}$/i', 'message' => Yii::t('app', 'portal_help5')],
            ['examples_name', 'unique'],
            ['dest_ip', 'match', 'pattern' => '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', 'message' => Yii::t('app', 'portal_help13')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'logo' => Yii::t('app', 'portal logo'),
            'top_banner' => Yii::t('app', 'portal top_banner'),
            'banner' => Yii::t('app', 'portal banner'),
            'footer' => Yii::t('app', 'portal footer'),
            'examples_name' => Yii::t('app', 'portal examples_name'),
            'portal_name' => Yii::t('app', 'portal portal_name'),
            'source_path' => Yii::t('app', 'portal source_path'),
            'dest_ip' => Yii::t('app', 'portal dest_ip'),
            'dest_path' => Yii::t('app', 'portal dest_path'),
            'action' => Yii::t('app', 'portal action'),
            'type' => Yii::t('app', 'client type'),
            'pid' => Yii::t('app', 'portal pid')
        ];
    }

    public function scenarios()
    {
        return [
            'pc' => ['logo', 'top_banner', 'footer', 'banner', 'examples_name'],
            'mobile' => ['logo', 'top_banner', 'footer', 'examples_name'],
            'give' => ['action', 'source_path', 'dest_ip', 'dest_path'],
            'default' => [''],
        ];
    }

    public function getAttributesList()
    {
        return [
            'action' => [
                2 => Yii::t('app', 'portal_help9'),
                1 => Yii::t('app', 'portal_help8')
            ]
        ];
    }

    //将模板需要的数据写入到文件中
    public function pc($model)
    {
        $username = Yii::$app->user->identity->username; //当前登录的用户名
        $portal_dir = 'portal/'; //portal 根目录
        $tmp_dir = $portal_dir . $model->portal_name; // 模板目录
        $user_dir = $portal_dir . $username; // 管理员目录
        $manager_tmp = $user_dir . '/' . $model->examples_name; //管理员可以管理的模板目录

        //常用路径
        $path = [
            'dir' => '/srun3/www/srun4-mgr/web/portal/' . $username, //管理员目录
            'js' => $manager_tmp . '/js', //js
            'css' => $manager_tmp . '/css', //css
            'img' => $manager_tmp . '/img', //img
            'data_js' => $manager_tmp . '/js/portal_data.js', //页面数据
            'uploads' => '/uploads/ueditor'
        ];
        $model->banner = !empty($model->banner) ? $model->banner : '';

        //剔除标签操作
        $search = ['<p>', '<br/>', ''];
        $top_banner_replace = ['<p>', '</p>', '<br />']; //剔除 top_banner 中的 <p> 标签

        //对banner 进行数据处理
        foreach ($search as $val) {
            $source = str_replace($val, '', $model->banner); //剔除 <p> 标签
            $model->banner = $source;
        }
        unset($source);
        foreach ($top_banner_replace as $val) {
            $source1 = str_replace($val, '', $model->logo); //剔除 <p> 标签
            $source = str_replace($val, '', $model->top_banner); //剔除 <p> 标签
            $model->logo = $source1;
            $model->top_banner = $source;
        }
        unset($source);
        $banner_arr = array_filter(explode('</p>', $model->banner)); // 将 banner 数据分割到数据中,此时得到的是纯净的img 标签数据

        //将 udeitor 目录中的 img 迁移到 portal 目录中
        //$banner 是所有banner 图片的临时文件目录位置, 目的是为了移动文件
        foreach ($banner_arr as $val) {
            //取出需要创建的图片上传目录
            $pattern = '<img.*?src="(.*?)">';
            preg_match($pattern, $val, $banner_dir);
            if (!empty($banner_dir)) {
                $pathname = pathinfo($banner_dir[1])['dirname'];
                $ltrim = ltrim($pathname, '/');
                $dirname[] = str_replace('uploads/ueditor', $path['img'], $ltrim); //文件目标位置
                $move[$ltrim] = $dirname;
            }

            //整理一份最新的 banner 路径数据.
            $data['banner'][] = '<li>' . str_replace('/uploads/ueditor', 'img', $val) . '</li>';
        }
        $dirname = array_unique($dirname);

        //创建目录并给目录授权  第一部分 基础操作
        if (!file_exists($manager_tmp)) {
            $makedir = $path;
            unset($makedir['dir']);
            unset($makedir['uploads']);
            unset($makedir['data_js']);
            array_unshift($makedir, $manager_tmp);
            array_unshift($makedir, $user_dir);
            $makedir = array_merge($makedir, $dirname);

            foreach ($makedir as $val) {
                //目录存在则不创建
                if (!file_exists($val)) {
                    mkdir($val, 0777, true);
                    chmod($val, 0777);
                }
            }
        }

        //为 srun_portal.js 文件准备数据
        $model->logo = str_replace($path['uploads'], 'img', $model->logo);
        $data['logo'] = !empty($model->logo) ? $model->logo : './img/logo.png';
        $data['banner'] = implode('', $data['banner']);
        $data['top_banner'] = !empty($model->top_banner) ? $model->top_banner : '';
        $data['footer'] = !empty($model->footer) ? $model->footer : Yii::t('app', 'userlink');


        //将数据赋值给 js 变量
        $json = json_encode($data);
        $str = "var ad_data=" . $json;
        //将模板需要的参数都写到 js 文件中, 页面直接加载js 文件来获取相应参数.
        $fp = fopen($path['data_js'], 'w+');
        fwrite($fp, $str);
        fclose($fp);

        //迁移目录
        $this->xCopy($tmp_dir, $manager_tmp, 1);
        foreach ($move as $key => $val) {
            $val = array_unique($val);

            if (is_array($val)) {
                foreach ($val as $vals) {
                    $this->xCopy($key, $vals, 1);
                }
            } else {
                $this->xCopy($key, $val, 1);
            }
        }
        return true;
    }

    //将模板需要的数据写入到文件中
    public function mobile($model)
    {
        $username = Yii::$app->user->identity->username; //当前登录的用户名
        $portal_dir = 'portal/'; //portal 根目录
        $tmp_dir = $portal_dir . $model->portal_name; // 模板目录
        $user_dir = $portal_dir . $username; // 管理员目录
        $manager_tmp = $user_dir . '/' . $model->examples_name; //管理员可以管理的模板目录

        //常用路径
        $path = [
            'dir' => '/srun3/www/srun4-mgr/web/portal/' . $username, //管理员目录
            'js' => $manager_tmp . '/js', //js
            'css' => $manager_tmp . '/css', //css
            'img' => $manager_tmp . '/img', //img
            'data_js' => $manager_tmp . '/js/portal_data.js', //页面数据
            'uploads' => '/uploads/ueditor'
        ];

        $model->logo = !empty($model->logo) ? $model->logo : '';

        //剔除标签操作
        $search = ['<p>', '<br/>', ''];

        //对banner 进行数据处理
        foreach ($search as $val) {
            $source = str_replace($val, '', $model->logo); //剔除 <p> 标签
            $model->logo = $source;
        }
        $banner_arr = array_filter(explode('</p>', $model->logo)); // 将 banner 数据分割到数据中,此时得到的是纯净的img 标签数据

        //将 udeitor 目录中的 img 迁移到 portal 目录中
        //$banner 是所有banner 图片的临时文件目录位置, 目的是为了移动文件
        foreach ($banner_arr as $val) {
            //取出需要创建的图片上传目录
            $pattern = '<img.*?src="(.*?)">';
            preg_match($pattern, $val, $banner_dir);
            if (!empty($banner_dir)) {
                $pathname = pathinfo($banner_dir[1])['dirname'];
                $ltrim = ltrim($pathname, '/');
                $dirname[] = str_replace('uploads/ueditor', $path['img'], $ltrim); //文件目标位置
                $move[$ltrim] = $dirname;
            }
        }

        //创建目录并给目录授权  第一部分 基础操作
        if (!file_exists($manager_tmp)) {
            $makedir = $path;
            unset($makedir['dir']);
            unset($makedir['uploads']);
            unset($makedir['data_js']);
            array_unshift($makedir, $manager_tmp);
            array_unshift($makedir, $user_dir);
            $makedir = array_merge($makedir);

            foreach ($makedir as $val) {
                //目录存在则不创建
                if (!file_exists($val)) {
                    mkdir($val, 0777, true);
                    chmod($val, 0777);
                }
            }
        }

        //为 srun_portal.js 文件准备数据
        $model->logo = str_replace($path['uploads'], 'img', $model->logo);
        $data['logo'] = !empty($model->logo) ? $model->logo : './img/logo.png';
        $data['top_banner'] = !empty($model->top_banner) ? $model->top_banner : '';
        $data['footer'] = !empty($model->footer) ? $model->footer : Yii::t('app', 'userlink');


        //将数据赋值给 js 变量
        $json = json_encode($data);
        $str = "var ad_data=" . $json;
        //将模板需要的参数都写到 js 文件中, 页面直接加载js 文件来获取相应参数.
        $fp = fopen($path['data_js'], 'w+');
        fwrite($fp, $str);
        fclose($fp);

        //迁移目录
        $this->xCopy($tmp_dir, $manager_tmp, 1);
        foreach ($move as $key => $val) {
            $val = array_unique($val);

            if (is_array($val)) {
                foreach ($val as $vals) {
                    $this->xCopy($key, $vals, 1);
                }
            } else {
                $this->xCopy($key, $val, 1);
            }
        }
        return true;
    }

    public function remove($name)
    {
        if (empty($name)) {
            return false;
        }

        $source = $this->deleteAll(['examples_name' => $name]);

        if ($source) {
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'delete success.'));
        } else {
            Yii::$app->getSession()->setFlash('error', Yii::t('app', 'delete success.'));
        }
    }

    //迁移文件, 仅适用于本 portal 操作
    public function xCopy($source, $destination, $child)
    {
        //用法：
        // xCopy("feiy","feiy2",1):拷贝feiy下的文件到 feiy2,包括子目录
        // xCopy("feiy","feiy2",0):拷贝feiy下的文件到 feiy2,不包括子目录
        //参数说明：
        // $source:源目录名
        // $destination:目的目录名
        // $child:复制时，是不是包含的子目录

        if (!is_dir($source)) {
            echo("Error:the $source is not a direction!");
            return 0;
        }

        if (!is_dir($destination)) {
            mkdir($destination, 0777);
            @chmod($destination, 0777);
        }

        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . "/" . $entry)) {
                    if ($child)
                        \center\modules\setting\models\Portal::xCopy($source . "/" . $entry, $destination . "/" . $entry, $child);
                } else {
                    copy($source . "/" . $entry, $destination . "/" . $entry);
                    @chmod($destination . "/" . $entry, 0777);
                }
            }
        }
        return 1;
    }

    //操作日志
    public function log($action)
    {
        switch ($action) {
            case 'add':
                $dirtyArr = LogWriter::dirtyData([], $this->attributes);
                break;
            case 'edit':
                $dirtyArr = LogWriter::dirtyData($this->oldAttributes->oldAttributes, $this->attributes);
                break;
            case 'delete':
                $dirtyArr = LogWriter::dirtyData([], $this->attributes);
                break;
            default:
                $dirtyArr = LogWriter::dirtyData($this->oldAttributes->oldAttributes, $this->attributes);
                break;
        }
        /*echo "<pre>";
        print_r($this->oldAttributes->oldAttributes);exit;*/
        $logData = [
            'operator' => Yii::$app->user->identity->username,
            'target' => $this->examples_name,
            'action' => $action,
            'action_type' => 'Setting Portal',
            'content' => \yii\helpers\Json::encode($dirtyArr),
            'class' => __CLASS__,
        ];
        LogWriter::write($logData);
        return true;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->oldAttributes = $this->findOne($this->id);
            return true;
        } else {
            return false;
        }
    }
}