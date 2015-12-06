<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/21
 * Time: 14:34
 */
namespace test;

use System\Core\Router;
use System\Core\Router\URLCreater;
use System\Core\Router\URLParser;
use System\Util\UDK;

$config = [

    //普通模式 与 兼容模式 获取$_GET变量名称
    'URL_MODULE_VARIABLE'   => '_m',
    'URL_CONTROLLER_VARIABLE'   => '_c',
    'URL_ACTION_VARIABLE'   => '_a',
    'URL_COMPATIBLE_VARIABLE' => '_pathinfo',

    //兼容模式和PATH_INFO模式下的解析配置，也是URL生成配置
    'MM_BRIDGE'     => '/',//模块与模块之间的连接桥
    'MC_BRIDGE'     => '/',
    'CA_BRIDGE'     => '/',
    'AP_BRIDGE'     => '/corax/',//*** 必须保证操作与控制器之间的符号将是$_SERVER['PATH_INFO']字符串中第一个出现的
    'PP_BRIDGE'     => '/',//参数与参数之间的连接桥
    'PKV_BRIDGE'    => '/',//参数的键值对之前的连接桥

    //伪装的后缀，不包括'.'号
    'MASQUERADE_TAIL'   => '.html',
    //重写模式下 消除的部分，对应.htaccess文件下
    'REWRITE_HIDDEN'      => '/develop.php',
];

$urlCreater = URLCreater::getInstance($config);
//默认使用跟随系统使用PATHINFO模式
//$url = $urlCreater->create('ma/mb','c','a',['p1'=>'v1','p2'=>'v2']);
//$url = $urlCreater->create(['ma','mb','mc'],'c','a',['p1'=>'v1','p2'=>'v2']);
$url = $urlCreater->create(['ma','mb','mc'],'c','a',[],Router::URLMODE_PATHINFO,false);
//普通模式测试
//$url = $urlCreater->create('maBA/mbCA','c','a',['p1'=>'v1','p2'=>'v2'],Router::URLMODE_COMMON,false);
//$url = $urlCreater->create(['maBA','mbCA'],'c','a',['p1'=>'v1','p2'=>'v2'],Router::URLMODE_COMMON,false);
//兼容模式测试
//$url = $urlCreater->create('ma/mb','c','a',['p1'=>'v1','p2'=>'v2'],Router::URLMODE_COMPATIBLE);
//$url = $urlCreater->create(['ma','mb','mc'],'c','a',['p1'=>'v1','p2'=>'v2'],Router::URLMODE_COMPATIBLE);
//$url = $urlCreater->create(['ma','mb','mc'],'c','a',[],Router::URLMODE_COMPATIBLE,false);



$urlParser = URLParser::getInstance($config);
$result = $urlParser->parse(substr($url,strlen('/corax/develop.php')));
//$result = $urlParser->parse(substr($url,strlen('/corax/develop.php?')));// 测试Common模式和compatible模式下粗腰多去除一个问号


//TODO:应用URL重写 需要实际部署，因为$urlParser->parse()调用时实际参数只有在实际部署的时候才能获取


echo "<a href='{$url}'>{$url}</a>";
UDK::dumpout($result);
