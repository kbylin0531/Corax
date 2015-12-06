<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/17
 * Time: 13:55
 */
use \System\Core\Dao;

return array(
    'MASTER_NO'    => 0,
    'DB_CONNECT'   =>   array(
        0   =>  array(
            'type'   =>  Dao::DB_TYPE_MYSQL,//数据库类型
            'dbname'   => 'mistcms',//选择的数据库
            'username'   =>  'root',
            'password'   => '123456',
            'host' => 'localhost',
            'port' => '3306',
            'charset'   => 'UTF8',
            'dsn'       => null,//默认先检查差DSN是否正确
            'options'    => array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,//默认异常模式
            ),
        )
    ),
);