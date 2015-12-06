<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/23
 * Time: 18:03
 */
namespace test;

use System\Core\Router\RuleParser;

$config = [    //直接路由开关
    'DIRECT_ROUTE_ON'    => true,
    //简介路由开关
    'INDIRECT_ROUTE_ON'  => true,
    //直接路由发生在URL解析之前，直接路由如果匹配了URL字符串，则直接链接到指定的模块，否则将进行URL解析和间接路由
    'DIRECT_ROUTE_RULES'    => [
        'DIRECT_STATIC_ROUTE_RULES'  => [
            //静态跳转链接
            'product/baidu'   => 'http://www.baidu.com',
            'product/siteside'   => '/corax/index.php/home/index/index',
            'product/special'   => ['DS_Home/DS_Index', /*DOMAIN_MODULE_BIND 为true的前提下将失效*/
                'DS_Ctl',
                'DS_Act',
                ['asd'=>'fgh']],
            'product/specials'   => 'SpecialMS/SC/SA',
            'product/callable'  => function($arg){
                return [
                    null, // M
                    'CALLBACK', // C
                    $arg  //参数action
                ];
            }
        ],
        //匹配符解析
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
        //直接正则路由,正则直接路由无法反解析，主要用于PHP代码中自定义的URL的解析
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
];

$ruleParser = RuleParser::getInstance($config);
$rst = $ruleParser->parseDirectRules();