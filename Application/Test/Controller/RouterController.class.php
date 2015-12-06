<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/29
 * Time: 19:05
 */
namespace Application\Test\Controller;
use System\Core\Controller;
use System\Core\Router;
use System\Core\Router\URLParser;
use System\Core\Router\DomainCreater;
use System\Core\Router\DomainParser;
use System\Util\UDK;
use Application\Test;

class RouterController extends Controller{

    private $config = [
        /**
         * 子域名部署模式下 的 完整域名
         */
        'FUL_DOMAIN'    => 'corax.com',
        //是否将子域名和模块进行对应
        'SUB_DOMAIN_MODULE_MAPPING_ON'  => true,
        /**
         * 子域名部署规则
         */
        'SUB_DOMAIN_DEPLOY_RULES'   => [
            'sports'    => ['home/sports','CCCC','AAAA'],
            'home.ved'  => [['home','video']],
            'app.nand'  => ['Mand/Mend','cocnco'],
            'news'      => 'home/news',
        ],
        //使用的协议名称
        'HOST_PROTOCOL' => 'http',
        //使用的端口号，默认为80时会显示为隐藏
        'HOST_PORT' => 80,
        //普通模式 与 兼容模式 获取$_GET变量名称
        'URL_MODULE_VARIABLE'   => '_m',
        'URL_CONTROLLER_VARIABLE'   => '_c',
        'URL_ACTION_VARIABLE'   => '_a',
        'URL_COMPATIBLE_VARIABLE' => '_pathinfo',

        'COMMONMODE_SOURCE' => Router::COMMONMODE_SOURCE_GET,

        //兼容模式和PATH_INFO模式下的解析配置，也是URL生成配置
        'MM_BRIDGE'     => '/',//模块与模块之间的连接桥
        'MC_BRIDGE'     => '/',
        'CA_BRIDGE'     => '/',
        'AP_BRIDGE'     => '/corax/',//*** 必须保证操作与控制器之间的符号将是$_SERVER['PATH_INFO']字符串中第一个出现的
        'PP_BRIDGE'     => '/',//参数与参数之间的连接桥
        'PKV_BRIDGE'    => '/',//参数的键值对之前的连接桥
    ];

    public function testDomainCreater(){
        $domainCreater = DomainCreater::getInstance($this->config);
//        $rst = $domainCreater->create('home/news');
//        $rst = $domainCreater->create(['HOMe','news']);
//        $rst = $domainCreater->create(['HOMe','VIDEO']);
        $rst = $domainCreater->create(['HOMe','vedoe']);
        UDK::dumpout($rst);
    }

    public function testDomainParser(){
        $domainParser = DomainParser::getInstance($this->config);
//        $rst = $domainParser->parse('app.nand.corax.com');
        $rst = $domainParser->parse('news.corax.com');

        UDK::dumpout($rst);
    }

    public function testUrlParser(){
//        $urlparser = URLParser::getInstance($this->config);
        $query = [
            //普通模式下的测试
            '_m=testmodules01/testmodules02&_c=testcontrollers&_a=testaction&param01=value01',
            '',//或者null等判断为空的值时，将从$_GET/$_POST/$_REQUEST中获取参数，连接地址改为'develop.php?_m=testmoXXXdules&_c=testconXXXtrollers&_a=tesXXXtaction&paraXXm01=valuXXe01'
            //pathinfo模式下的测试
            '/pathinfomodule01/pathinfomodule02/pathinfocontroller/pathinfoaction/corax/param01/value01/param02/value02.html',
            //兼容模式测试
            '_pathinfo=/pathinfomodule01/pathinfomodule02/pathinfocontroller/pathinfoaction/corax/param01/value01/param02/value02.html'
        ];
        $parsed = [];
        foreach($query as $key=>$item){
//            $parsed[$key] = $urlparser->parse($item);
        }
        UDK::dumpout($query,$parsed);
    }


    private  $configure = null;

    /**
     * @throws \Exception
     */
    public function __construct(){
        parent::__construct();
        $this->configure = [
            'DOMAIN_DEPLOY_ON'    => true,
            'FUL_DOMAIN'=>'corax.com.cn',
            'SUB_DOMAIN_DEPLOY_RULES' => array(
                'home'      => 'Home',
                'news'      => ['Home/news','NewsIndex','newlist',['nid'=>'111111']],
                'sports'    => ['Home/sports','SportsIndex','sportslist',['sid'=>'222222']],
                'video'     => ['Home/video','videoIndex','videolist',['vid'=>'222222']],
            ),
            //直接路由开关
            'DIRECT_ROUTE_ON'    => true,
            //简介路由开关
            'INDIRECT_ROUTE_ON'  => false,
            //直接路由发生在URL解析之前，直接路由如果匹配了URL字符串，则直接链接到指定的模块，否则将进行URL解析和间接路由
            'DIRECT_ROUTE_RULES'    => [
                'DIRECT_STATIC_ROUTE_RULES'  => [
                    'product/special'   => ['DS_Home/DS_Index' /*DOMAIN_MODULE_BIND 为true的前提下将失效*/,'DS_Ctl','DS_Act',['asd'=>'fgh']],
                    'product/callable'  => function(){
                        return [
                            null, // M
                            'CALLBACK', // C
                        ];
                    }
                ],
                'DIRECT_WILDCARD_ROUTE_RULES'  => [
                    //导向固定的模块、控制器、操作
                    'product/[product_id]/[detail]'  => ['TESTMS','TESTC','TESTA',[
                        'product_id'    => null,
                        'detail'        => null,
                    ]],
                    'product/[product_id]'      => function($matches){
                        //也可以直接返回下面的字符串，交给URL解析器进行进一步的解析
                        return [null,'DC','DA_'.$matches[0],[
                            'product_id'    => null,
                            'detail'        => null,
                        ]];
                    },
                    'product/[param01]/[param02]'      => 'Home/index/index_$1_$2', // Home/index/index_nice_123
                ],
                //直接正则路由
                'DIRECT_REGULAR_ROUTE_RULES'    => [
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
                    //交给URL解析器进行进一步的解析
                    'goods/(.+)/(.+)'   => 'CONTROLLER007/ACTION008#$1/$2',
                ],
            ],
            //间接路由在URL解析之后
            'INDIRECT_ROUTE_RULES'   => [],
        ];
    }

    /**
     * 测试URL创建
     */
    public function testUrlCreater(){
        Router::init($this->configure);
        $url = Router::build('Home','index','testUrlCreater',['pn'=>'pv','pn2'=>'pv2'],Router::URLMODE_COMPATIBLE);

        UDK::dump(isset($url)?$url:'Null');
        echo "<a href='$url'>$url</a><br />";;
        echo __METHOD__;
    }


    public function index(){

        Test::p();

        //路由测试
//        $rule = 'product-[param01]-[:num]';
//        $rule = preg_replace(['[:num]','/\[.+?\]/'],['(\d+)','([^/\[\]]+)'],$rule);//非贪婪匹配
//        $match = preg_match('#^'.$rule.'$#', 'product-123456-nice', $matches);
//        UDK::dumpout($rule,$match,$matches);

//        $urlParser = new Router\URLParser();
//        $rst = $urlParser->parseMCA('home/lala/justi?n/bieber');
//        UDK::dump($rst);


//        $ruleCracker = Router\RuleParser::getInstance(array(

//            'DIRECT_ROUTE_RULES'    => array(
//                'DIRECT_STATIC_ROUTE_RULES' => array(
//                    'product'   => array('Home','index','index'),
//                ),
//                'DIRECT_WILDCARD_ROUTE_RULES'  => array(
//                    'product/:any/:num/:any' => array('Home','index','index',array(
//                        'pa'    => null,
//                        'pb'    => null,
//                        'pc'    => null,
//                        'pd'    => 'justin',
//                    )),
//                    'news/:num' => 'Home/Index/news?id=$1',
//                    'sports/:num/:any'   => function($matched){
//                        UDK::dump($matched);
//                        return serialize($matched);
//                    }
//                ),
//            ),
//            'INDIRECT_ROUTE_RULES'   => array(
//            ),
//        ));
//
//        $rst = $ruleCracker->parseDirectRules('product/good/123/nice/');
//        $rst = $ruleCracker->parseDirectRules('news/12345/');
//        $rst = $ruleCracker->parseDirectRules('sports/12345/nice');

//        $rst2 = $ruleCracker->parseDirectRules('PRODUCT');

//        UDK::dump(isset($rst)?$rst:'');

        echo __METHOD__;
    }
    public function testRouter(){
        $url = Router::build(['home'],'index','index');
        echo "<a href='$url'>$url</a><br />";

        Router::init($this->configure);
//        $rst = Router::parse('sports.corax.com.cn','Admin/User/login#a/b/c/d.html');
        //测试普通模式下
//        $rst = Router::parse('sports.corax.com.cn','m=NONONONONONONO_MODULE&c=user&a=lookdetail&ppp=pvv');//URL中的模块无法生效
        //测试compatible模式下
//        $rst = Router::parse('sports.corax.com.cn','pathinfo=NONONONONONONO_MODULE2/user/action#param01/2.html');
        //测试pathinfo模式下
//        $rst = Router::parse('sports.corax.com.cn','NONONONONONONO_MODULE2/user/action#param01/2.html');

        //测试直接路由
        //静态路由
//        $rst = Router::parse('sports.corax.com.cn','product/special',true);
//        $rst = Router::parse('sports.corax.com.cn','product/callable',true);
        //通配符路由
//        $rst = Router::parse(null,'product/123/goodman',true);
//        $rst = Router::parse('sports.corax.com.cn','product/123',true);
//        $rst = Router::parse('sports.corax.com.cn','product/nice/123',true);
        //正则式路由
//        $rst = Router::parse('sports.corax.com.cn','goods/123_hello',true);
//        $rst = Router::parse('sports.corax.com.cn','goods/hello/123',true);

        UDK::dump(isset($rst)?$rst:'No Parameters');
        echo __METHOD__;
    }



    public function testDomainPC(){
//        $domainConfig = array(
//            'FUL_DOMAIN'    => 'corax.com',
//            'SUB_DOMAIN_DEPLOY_RULES'   => array(
//                'zhang' => array('home/zhang','index2','testindex'),
//                'lin' => array(array('home','lin'),'index2','testindex'),
//                'zhao'  => 'http://www.baidu.com',
//                'ri'    => '/done',
//            ),
//        );
//        $domainParser = DomainParser::getInstance($domainConfig);
//        $rst = $domainParser->parse('lin.corax.com');

//        $domainCreater = DomainCreater::getInstance($domainConfig);
//        $rst = $domainCreater->create('home/zhang');
//        $rst = $domainCreater->create('home/lin');

//        UDK::dump($rst);
    }

}