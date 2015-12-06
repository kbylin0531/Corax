<?php
/**
 * Created by Lin.
 * User: Administrator
 * Date: 2015/9/6
 * Time: 9:13
 */
use System\Corax;

//环境要求最低5.4,推荐5.6
version_compare(PHP_VERSION,'5.4.0','<') and die('Require PHP >= 5.4 !');

//报告全部错误
//error_reporting(-1);

/**
 * 基础目录定义
 */
define('BASE_PATH',str_replace('\\','/',__DIR__).'/');
//define('BASE_PATH',str_replace('\\','/',dirname(__DIR__)).'/');//如果放到public目录下
include_once BASE_PATH.'System/Corax.class.php';

/**
 * 数据库连接配置组成部分
 */
const DB_PREFIX = 'prefix';
const DB_PORT= 'port';
const DB_PWD = 'password';
const DB_UNAME = 'username';
const DB_DBNAME = 'dbname';
const DB_HOST = 'host';
const DB_TYPE = 'type';

Corax::init([
    'URL_MODE'          => 2,
//    'TIME_ZONE'         => 'Asia/Shanghai',
//    'APP_NAME'          => 'WebManagement',
//    'LOG_RATE'          => LOGRATE_DAY,
//
//    'DEBUG_MODE_ON'         => true,
//    'URLMODE_TOPSPEED_ON'   => false,
//    'REWRITE_ENGINE_ON'     => false,
    'PAGE_TRACE_ON'         => true,
//    'URL_ROUTE_ON'          => true,
//    'AUTO_CHECK_CONFIG_ON'  => false,
//    'TEMPLATE_ENGINE'       => 'Smarty',
]);

Corax::start();


