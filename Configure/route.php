<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/26
 * Time: 6:58
 */
/**
 * 路由设置及URL设置
 */
return [
    //直接路由开关
    'DIRECT_ROUTE_ON'    => false,
    //简介路由开关
    'INDIRECT_ROUTE_ON'  => false,
    //直接路由发生在URL解析之前，直接路由如果匹配了URL字符串，则直接链接到指定的模块，否则将进行URL解析和间接路�?
    'DIRECT_ROUTE_RULES'    => [
        //静态路由规则
        'DIRECT_STATIC_ROUTE_RULES' => [],
        //通配符路由规则,具体参考CodeIgniter,内部通过正则表达式实现
        'DIRECT_WILDCARD_ROUTE_RULES' => [],
        //正则表达式规则，
        'DIRECT_REGULAR_ROUTE_RULES' => [],
    ],
    //间接路由在URL解析之后
    'INDIRECT_ROUTE_RULES'   => [],
    //URL创建规则
    'URL_CREATION_RULE'     => [],

    //普通模式 与 兼容模式 获取$_GET变量名称
    'URL_MODULE_VARIABLE'   => '_m',
    'URL_CONTROLLER_VARIABLE'   => '_c',
    'URL_ACTION_VARIABLE'   => '_a',
    'URL_COMPATIBLE_VARIABLE' => '_pathinfo',
    'COMMONMODE_SOURCE' => \System\Core\Router::COMMONMODE_SOURCE_GET,

    //兼容模式和PATH_INFO模式下的解析配置，也是URL生成配置
    'MM_BRIDGE'     => '/',//模块与模块之间的连接桥
    'MC_BRIDGE'     => '/',
    'CA_BRIDGE'     => '/',
    'AP_BRIDGE'     => '/co/',//*** 必须保证操作与控制器之间的符号将是$_SERVER['PATH_INFO']字符串中第一个出现的
    'PP_BRIDGE'     => '/',//参数与参数之间的连接桥
    'PKV_BRIDGE'    => '/',//参数的键值对之前的连接桥

    //伪装的后缀，不包括'.'号
    'MASQUERADE_TAIL'   => '.html',
    //重写模式下需要重写消除的部分，参照.htaccess文件下
    'REWRITE_HIDDEN'      => '/index.php',

    //默认的模块，控制器和操作
    'DEFAULT_MODULE'      => 'Home',
    'DEFAULT_CONTROLLER'  => 'Index',
    'DEFAULT_ACTION'      => 'index',

    //是否开启子域名部署
    'DOMAIN_DEPLOY_ON'    => false,
    //解析结果中模块域名是否绑定，false时即使子域名指定了域名，当时URL解析后中还是可以修改的
    'DOMAIN_MODULE_BIND'  => false,
    //子域名部署模式下 的 完整域名
    'FUL_DOMAIN'=>'',
    //使用的协议名称
    'HOST_PROTOCOL' => 'http',
    //使用的端口号，默认为80时会显示为隐藏
    'HOST_PORT' => 8056,
    //是否将子域名和模块进行对应
    'SUB_DOMAIN_MODULE_MAPPING_ON'  => false,
    //子域名部署规则
    'SUB_DOMAIN_DEPLOY_RULES' => [
        /**
         * 分别对应子域名模式下 的 [模块、(控制器、(操作、(参数)))]
         * 控制器到参数为可选单元，模块对应着子域名
         * 设置为null是表示不做设置，将使用默认的通用配置
         *
         * 部署规则的反面则对应着 "模块序列"=>"子域名首部" 的键值对
         */
    ],
];