<?php
/**
 * This file is part of easycrm, created by PhpStorm.
 * Author: LouXin
 * Date: 2014/12/23 10:10
 * File: ExportInterface.php
 */

namespace common\extend\Export;

use common\extend\Export\DataType\DataTypeInterface;

interface ExportInterface
{
    const FIELD_TYPE_STRING = 'string';
    const FIELD_TYPE_DATE = 'date';
    const FIELD_TYPE_DATETIME = 'datetime';
    const FIELD_TYPE_ASSOCIATEDARRAY = 'associatedArray';

    /**
     * 导出数据
     *
     * @param DataTypeInterface $source   数据类型
     * @param string            $fileName 导出的文件名称
     *
     * @return mixed
     *
     * @throws ExportException
     */
    public function export($source, $fileName = '');

    public function getName();
}
