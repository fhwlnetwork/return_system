<?php
/**
 * This file is part of easycrm, created by PhpStorm.
 * Author: wjh
 * Date: 2016/11/9 10:40
 * File: DataTypeInterface.php
 */

namespace common\extend\Export\DataType;

interface DataTypeInterface
{
    const TYPE_SQL = 'sql';
    const TYPE_ARRAY = 'array';

    /**
     * 获取具体的数据，例如SQL语句、数组类型的数据
     *
     * @return mixed
     */
    public function getData();

    /**
     * 获取数据标题
     *
     * @return array
     */
    public function getFields();

    /**
     * 获取数据类型
     *
     * @return string
     */
    public function getType();
}
