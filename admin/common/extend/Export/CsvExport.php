<?php
/**
 * This file is part of easycrm, created by PhpStorm.
 * Author: wjh
 * Date: 2016/11/9 10:40
 * File: CsvExport.php
 */

namespace common\extend\Export;

use Yii;
use common\extend\Export\DataType\DataTypeInterface;

class CsvExport implements ExportInterface
{
    /** 每次循环获取的记录数 */
    const ROWS_PER_LOOP = 20000;


    /**
     * {@inheritdoc}
     */
    public function export($source, $fileName = '')
    {
        if (!$source instanceof DataTypeInterface) {
            throw new ExportException('The source is not instanceof the DataTypeInterface.');
        }

        $data = $source->getData();
        $type = $source->getType();
        $fields = $source->getFields();

        if (empty($fields)) {
            throw new ExportException('The fields of the source is not set.');
        }

        $fileName = empty($fileName) ? 'Data' . date('YmdHis') . '.csv' : $fileName . '.csv';
        $tempFilePath = '../data';
        $tempFileName = $tempFilePath . '/' . date('YmdHis') . '-' . mt_rand(1, 1000) . '.csv';

        //设置文件标题栏
        $title = array();
        foreach ($fields as $field => $value) {
            $title[] = empty($value['name']) ?
                $field : $this->convertCharset($value['name'], 'UTF-8', 'GBK');
        }

        //获取数据
        switch ($type) {
            case DataTypeInterface::TYPE_SQL://SQL
                $conn = Yii::$app->db;

                //SQL语句中包含了limit
                if (stristr($data, 'limit')) {
                    throw new ExportException('The SQL statement can not contain limit condition.');
                }

                //循环获取数据
                $startRow = 0;
                $rawSql = $data;
                $fp = fopen($tempFileName, 'a+');
                fputcsv($fp, $title);

                while (true) {
                    $start = $startRow * self::ROWS_PER_LOOP;
                    $sql = $rawSql . " LIMIT $start, " . self::ROWS_PER_LOOP;
                    $data = $conn->createCommand($sql)->queryAll();

                    //数据为空，则说明已经没有数据
                    if (empty($data)) {
                        break;
                    }

                    foreach ($data as $key => $row) {
                        foreach ($fields as $field => $value) {
                            //字段类型
                            $type = empty($value['type']) ? 'string' : $value['type'];

                            //处理字段类型
                            switch ($type) {
                                case ExportInterface::FIELD_TYPE_STRING://字符串类型
                                    $pValue = isset($row[$field]) ? strval($row[$field]) : '0';
                                    break;
                                case ExportInterface::FIELD_TYPE_DATE://日期
                                    $pValue = !empty($row[$field]) ? date('Y-m-d', $row[$field]) : '0';
                                    break;
                                case ExportInterface::FIELD_TYPE_DATETIME://日期时间
                                    $pValue = !empty($row[$field]) ? date('Y-m-d H:i:s', $row[$field]) : '0';
                                    break;
                                case ExportInterface::FIELD_TYPE_ASSOCIATEDARRAY://关联数组
                                    $associatedArray = empty($value['associatedArray'][$field]) ?
                                        array() : $value['associatedArray'][$field];

                                    if (empty($associatedArray)) {
                                        //如果未设置，则直接显示该值
                                        $pValue = $row[$field];
                                    } else {
                                        //如果设置了关联数组，但是该值不在关联数组中，则显示为“未设置”
                                        $pValue = isset($associatedArray[$row[$field]]) ?
                                            $associatedArray[$row[$field]] : '未设置' . $row[$field];
                                    }
                                    break;
                                default:
                                    $pValue = isset($row[$field]) ? strval($row[$field]) : '0';
                                    break;
                            }
                            if (empty($pValue)) {
                                break;
                            } else {
                                $row[$field] = $this->convertCharset($pValue, 'UTF-8', 'GBK');
                            }
                        }
                        if (!empty($row)) {
                            fputcsv($fp, $row);
                        }

                    }
                    unset($data);
                    $startRow++;
                }
                fclose($fp);
                break;
            case DataTypeInterface::TYPE_ARRAY://数组
                if (!is_array($data)) {
                    throw new ExportException(
                        sprintf('The data type is %s, but the $data is not an array', DataTypeInterface::TYPE_ARRAY)
                    );
                }

                $fp = fopen($tempFileName, 'a+');
                fputcsv($fp, $title);
                $num = 0;
                foreach ($data as $key => $row) {
                    $rows = array();
                    foreach ($fields as $field => $value) {
                        if ($num == self::ROWS_PER_LOOP) {
                            ob_flush();
                            flush();
                            $num = 0;
                        }
                        //字段类型
                        $type = empty($value['type']) ? 'string' : $value['type'];

                        //处理字段类型
                        switch ($type) {
                            case ExportInterface::FIELD_TYPE_STRING://字符串类型
                                $pValue = isset($row[$field]) ? strval($row[$field]) : '0';
                                break;
                            case ExportInterface::FIELD_TYPE_DATE://日期
                                $pValue = !empty($row[$field]) ? date('Y-m-d', $row[$field]) : '0';
                                break;
                            case ExportInterface::FIELD_TYPE_DATETIME://日期时间
                                $pValue = !empty($row[$field]) ? date('Y-m-d H:i:s', $row[$field]) : '0';
                                break;
                            case ExportInterface::FIELD_TYPE_ASSOCIATEDARRAY://关联数组
                                $associatedArray = empty($value['associatedArray'][$field]) ?
                                    array() : $value['associatedArray'][$field];

                                if (empty($associatedArray)) {
                                    //如果未设置，则直接显示该值
                                    $pValue = $row[$field];
                                } else {
                                    //如果设置了关联数组，但是该值不在关联数组中，则显示为“未设置”
                                    $pValue = isset($associatedArray[$row[$field]]) ?
                                        $associatedArray[$row[$field]] : '未设置' . $row[$field];
                                }
                                break;
                            default:
                                $pValue = isset($row[$field]) ? strval($row[$field]) : '0';
                                break;
                        }
                        if (empty($pValue)) {
                            $rows[$field] = $pValue;
                        } else {
                            $rows[$field] = $this->convertCharset($pValue, 'UTF-8', 'GBK');
                        }
                    }
                    fputcsv($fp, $rows);
                    $num++;
                }
                break;
            default:
                break;
        }

        $fileName = $this->convertCharset($fileName, "UTF-8", "GBK");
        header("Content-Type: application/octet-stream;charset:gbk");
        header("Content-Disposition: attachment; filename=" . $fileName);
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . filesize($tempFileName));
        readfile($tempFileName);
        @unlink($tempFileName);
        exit;
    }

    /**
     * 读取csv文件
     *
     */
    public function readcsv($filename)
    {
        $array = array();
        $handle = fopen($filename, "r");
        $i = 0;
        while ($data = $this->fgetcsv($handle, 1024, ",")) {
            foreach ($data as $key => $val) {
                $data[$key] = trim($this->convertCharset($val, 'GBK', "UTF-8"));
            }
            if ($data == array(0 => '')) {
                continue;
            }
            $array[] = $data;
            $i++;
        }
        fclose($handle);
        return $array;
    }

    /**
     * @param $handle
     * @param null $length
     * @param string $d
     * @param string $e
     * @return bool
     */
    private function fgetcsv(&$handle, $length = null, $d = ',', $e = '"')
    {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
            if ($itemcnt % 2 == 0) {
                $eof = true;
            }
        }
        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
            $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
            $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }
        return empty ($_line) ? false : $_csv_data;
    }

    /**
     * 转换编码
     *
     * @param string $string 需要转换编码的字符串
     * @param string $from 原始编码
     * @param string $to 目标编码
     *
     * @return bool|string
     */
    public function convertCharset($string, $from, $to)
    {
        if (empty($string)) {
            return false;
        }

        if (function_exists('mb_convert_encoding')) {
            $string = mb_convert_encoding($string, $to, $from);
        } else {
            $string = iconv($from, $to, $string);
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'csv';
    }
}
