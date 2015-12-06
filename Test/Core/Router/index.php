<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/18
 * Time: 16:11
 */
namespace test;
use System\Core\Router;
use System\Util\UDK;

Router::init([
    'DOMAIN_DEPLOY_ON'    => true,
    'FUL_DOMAIN'=>'corax.com',
    'SUB_DOMAIN_MODULE_MAPPING_ON'  => true,
    'SUB_DOMAIN_DEPLOY_RULES' => array(
        //支持URL重定向
        'baidu'     => 'http://www.baidu.com',
        'admin'     => '/corax/index.php',
        //直接绑定字符串模块
        'home'      => 'Home',
        'homework'  => 'home/work',
        'news'      => ['Home/news','NewsIndex','newlist',['nid'=>'111111']],
        'sports'    => ['Home/sports','SportsIndex','sportslist',['sid'=>'222222']],
        'video'     => ['Home/video','videoIndex','videolist',['vid'=>'222222']],
    ),
    //直接路由开关
    'DIRECT_ROUTE_ON'    => true,
    //简介路由开关
    'INDIRECT_ROUTE_ON'  => true,
    //直接路由发生在URL解析之前，直接路由如果匹配了URL字符串，则直接链接到指定的模块，否则将进行URL解析和间接路由
    'DIRECT_ROUTE_RULES'    => [
        'DIRECT_STATIC_ROUTE_RULES'  => [
            //静态跳转链接
            'product/baidu'   => 'http://www.baidu.com',
            'product/siteside'   => '/corax/index.php/home/index/index',
            //
            'product/special'   => ['DS_Home/DS_Index' /*DOMAIN_MODULE_BIND 为true的前提下将失效*/,'DS_Ctl','DS_Act',['asd'=>'fgh']],
            'product/specials'   => 'SpecialMS/SC/SA',
            'product/callable'  => function($arg){
                return [
                    null, // M
                    'CALLBACK', // C
                    $arg  //参数action
                ];
            }
        ],
        'DIRECT_WILDCARD_ROUTE_RULES'  => [
            //导向固定的模块、控制器、操作
            'product/[product_id]/[detail]'  => ['TESTMS','TESTC','TESTA',[
                //注意顺序
                'product_id'    => null,
                'detail'        => null,
            ]],
            //动态导向，但是无法逆向生成规则
            'product/[product_id]'      => function($matches,$fulltext){
                //也可以直接返回下面的字符串，交给URL解析器进行进一步的解析
                return [null,'DC','DA_'.$matches[0],[
                    'product_id'    => 'id_'.$matches[0],
                    'detail'        => 'detail_'.$fulltext,
                ]];
            },
        ],
        //直接正则路由
        'DIRECT_REGULAR_ROUTE_RULES'    => [
            //使用正则表达式需要使用小括号圈出表达式
            'product/(.+?)/(.+?)/(\w+?)'       => ['CATMS','CATC','CATA',[
                //注意顺序
                'product_id'    => null,
                'num'           => null,
                'detail'        => null,
            ]],
            'goods/(\d+)_(.+)'   => ['TESTMS2','TESTC2','TESTA2',[
                'goods_id'    => null,
                'detail'        => null,
            ]],
            'goods/(\d+)/(.+)'      => function($matches){
                //也可以直接返回下面的字符串，交给URL解析器进行进一步的解析
                return [null,'DC','DA_'.$matches[0],[
                    'product_id'    => $matches[0],
                    'detail'        => '123456'.$matches[1],
                ]];
            },
        ],
    ],
    //使用的协议名称
    'HOST_PROTOCOL' => 'http',
    'HOST_PORT' => 8088,
    //间接路由在URL解析之后
    'INDIRECT_ROUTE_RULES'   => [],
]);
//测试子域名部署
//$rst = Router::parse('news.corax.com');
//$rst = Router::parse(null,'product/specials');
//$rst = Router::parse(null,'product/callable');
//$rst = Router::parse(null,'product/112233');
//$rst = Router::parse('news.corax.com','product/cat/112233/lookdoor');

//逆向创建
$rst = Router::create('Home/news','Comment','showContent',[],Router::URLMODE_COMPATIBLE);
//$rst = Router::parse('news.corax.com','Comment/showContent.html');





UDK::dump(isset($rst)?$rst:null);
