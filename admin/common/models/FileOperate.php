<?php
namespace common\models;

use yii;

/**
 * 文件操作处理.
 * 应用场景比如 文件上传 文件下载 批量处理等.
 */
class FileOperate
{
    public static function dir($item)
    {
        $dataDir = \Yii::$aliases['@data'];
        $dir = $dataDir . self::getDirValue($item);

        if (!file_exists($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        return $dir;
    }

    public static function getDirValue($item)
    {
        $other = DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR;
        if ($item === '') {
            return $other;
        }

        $data = [
            'export' => DIRECTORY_SEPARATOR . 'export' . DIRECTORY_SEPARATOR, // 导出
            'import' => DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR, // 导入
            'account' => DIRECTORY_SEPARATOR . 'account' . DIRECTORY_SEPARATOR, //开户
            'checkout' => DIRECTORY_SEPARATOR . 'checkout' . DIRECTORY_SEPARATOR, //结算
            'card' => DIRECTORY_SEPARATOR . 'card' . DIRECTORY_SEPARATOR, //开户
            'temp' => DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR, //临时缓存
            'webservice' => DIRECTORY_SEPARATOR . 'webservice' . DIRECTORY_SEPARATOR, //webservice被调用日志
            'report' => DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR, //报表
            'key' => DIRECTORY_SEPARATOR . 'key' . DIRECTORY_SEPARATOR, //key
            'cloud' => DIRECTORY_SEPARATOR . 'cloud' . DIRECTORY_SEPARATOR, //cloud云端推送日志
            'monitor' => DIRECTORY_SEPARATOR . 'monitor' . DIRECTORY_SEPARATOR, //cloud云端推送日志
        ];

        if (!array_key_exists($item, $data)) {
            return $other;
        }

        return $data[$item];
    }

    /**
     * 文件下载方法.
     * @param $dir 下载文件所在的目录.
     * @param $fileName 下载的文件名称.
     * @return bool
     */
    public static function download($dir, $fileName)
    {
        if ($dir != '' && $fileName != '') {
            $path = Yii::$aliases['@data'] . '/' . $dir . '/' . $fileName;

            if (!file_exists($path)) {
                echo "<script>alert('Not Found!!!')</script>";
                return false;
            };

            return Yii::$app->response->sendFile($path);
        }

        return false;
    }
}