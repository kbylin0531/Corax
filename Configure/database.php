<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/17
 * Time: 13:55
 */
use \System\Core\Dao;

return [
    //是否启用读写分离
    'IO_SPLITTING_ON'   => false,
    //主库，未选择数据库时默认选择这个连接
    'MASTER_CONF' => [
        'type'      =>  Dao::DB_TYPE_MYSQL,//数据库类型
        'dbname'    => 'test',//选择的数据库
        'username'  =>  'root',
        'password'  => '123456',
        'host'      => 'localhost',
        'port'      => '3306',
        'charset'   => 'UTF8',
        'dsn'       => null,//默认先检查差DSN是否正确,直接写dsn而不设置其他的参数可以提高效率，也可以避免潜在的bug
        'options'    => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,//默认异常模式
        ],
    ],
    //默认的PDO连接配置
    'PDO_DEFAULT_OPTION'    => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,//默认异常模式
        \PDO::ATTR_AUTOCOMMIT => true,//为false时，每次执行exec将不被提交
        \PDO::ATTR_EMULATE_PREPARES => false,//不适用模拟预处理
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,//结果集返回形式
    ],
    //从连接库
    'SLAVE_CONFS'    =>   [],
];