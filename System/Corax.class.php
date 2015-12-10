<?php
/**
 * Created by Lin.
 * User: Administrator
 * Date: 2015/9/6
 * Time: 9:16
 */
namespace System;
use System\Core\Configer;
use System\Core\Dao;
use System\Core\Dispatcher;
use System\Core\Log;
use System\Core\Router;
use System\Core\Storage;
use System\Exception\CoraxException;
use System\Extension\LiteBuilder;
use System\Util\SEK;

defined('BASE_PATH') or die('No Permission!');


/**
 * Class Corax 核心类
 * @package System
 */
final class Corax {
    /**
     * 应用配置
     * @var array
     */
    private static $_config = [
        /**
         * 写入session时区分不同的应用
         */
        'APP_NAME' => 'Mist',
        /**
         * 时区设置
         */
        'TIME_ZONE'=> 'Asia/Shanghai',

        /**
         * URL模式
         */
        'URL_MODE'          => 2,//URL模式
        'LOG_RATE'          => 1,//日志频度

        //-- 开关模式 --//
        /**
         * 调试模式，异常和错误不会被显示，但会写入到日志文件中
         */
        'DEBUG_MODE_ON' => true,

        /**
         * URL快速模式，不会判断URL模式而统一使用URL的通常模式
         * 解析速度最快，可以省略中间的很多步骤
         */
        'URLMODE_TOPSPEED_ON' => false,

        /**
         * 是否开启rewrite引擎,只有在运行环境支持的情况下建议开启
         */
        'REWRITE_ENGINE_ON'   => false,

        /**
         * 是否显示调试信息
         */
        'PAGE_TRACE_ON'    => true,

        /**
         * 是否开启路由功能
         */
        'URL_ROUTE_ON'      => true,

        /**
         * 是否自动开启配置文件检查与缓存，项目稳定运行的情况下建议关闭，可以减少三分之二的分析时间
         * 如果配置文件修改了或者集合配置文件被删除，需要手动开启并访问以建立集合配置文件，之后可以关闭
         */
        'AUTO_CHECK_CONFIG_ON'  => true,

        /**
         * 模板引擎选择
         */
        'TEMPLATE_ENGINE'       => 'Smarty',

        /**
         * 模板文件扩张名
         */
        'TEMPLATE_EXT'          => 'html',
    ];

    /**
     * 所有使用到的类
     * @var array
     */
    private static $_classes = [];

    /**
     * 系统预加载类
     * @var array
     */
    private static $_prev_loaded_classes = [];

    private static $_errors = [];

    private static $_url_components = null;

    /**
     * 初始化配置
     * @param array $config 配置数组
     * @return void
     */
    public static function init(array $config = array()){
        //合并配置
        $config and self::$_config = array_merge(self::$_config,$config);
        //设置失去和类加载函数
        date_default_timezone_set(self::$_config['TIME_ZONE']);
        spl_autoload_register('System\Corax::loadClass') or die('Function spl_autoload_register called failed!');//自动加载类定义

        //-- 普通常量定义 --//
        define('SYSTEM_PATH',BASE_PATH.'System/');
        define('APP_PATH',BASE_PATH.'Application/');
        define('CONFIG_PATH',BASE_PATH.'Configure/');
        define('RUNTIME_PATH',BASE_PATH.'Runtime/');
        define('PUBLIC_PATH',BASE_PATH.'Public/');
        define('FUNC_PATH',BASE_PATH.'Functions/');

        define('APP_NAME',self::$_config['APP_NAME']);   //定义应用名称

        define('IS_WIN',false !== stripos(PHP_OS, 'WIN')); //运行环境
        define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ));
        define('IS_POST',       strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' ? true : false);

        //-- 配置常量定义 --//
        define('URL_MODE',self::$_config['URL_MODE']);
        define('LOG_RATE',self::$_config['LOG_RATE']);//日志写入频率
        define('TEMPLATE_ENGINE',self::$_config['TEMPLATE_ENGINE']);
        define('TEMPLATE_EXT',self::$_config['TEMPLATE_EXT']);
        //-- 开关常量 --//
        define('DEBUG_MODE_ON',self::$_config['DEBUG_MODE_ON']);
        define('URLMODE_TOPSPEED_ON',self::$_config['URLMODE_TOPSPEED_ON']);
        define('REWRITE_ENGINE_ON',self::$_config['REWRITE_ENGINE_ON']);
        define('PAGE_TRACE_ON',self::$_config['PAGE_TRACE_ON']);
        define('AUTO_CHECK_CONFIG_ON',self::$_config['AUTO_CHECK_CONFIG_ON']);

        //-- 服务器环境常量 --//
        if(defined('SAE_APPNAME')){
            define('RUNTIME_ENVIRONMENT','Sae');
        }else{
            define('RUNTIME_ENVIRONMENT','Common');
        }
        //对应的驱动类都将使用该运行环境的名称为正式名称
        //....还可以是其他环境

        //-- 错误信息显示设置 --//
        if(DEBUG_MODE_ON){
            error_reporting(-1);
            ini_set('display_errors',1);
        }else{
            ini_set('display_errors',0);
            if(version_compare(PHP_VERSION,'5.3','>=')){
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            }else{
                error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
            }
        }

        //-- 常用URL可以访问的路径 --//
        define('URL_BASE_PATH', (SEK::isHTTPS() ? 'https://' :'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/');
        define('URL_PUBLIC_PATH',URL_BASE_PATH.'Public');

        self::status('init_begin');
        //-- 时间处理句柄注册 --//
        register_shutdown_function('System\Corax::end');
        set_error_handler('System\Corax::handleError',E_ALL);//报告所有的错误，PHP5.4以后E_STRICT成为E_ALL的一部分,PHP5.4版本一下需要使用"E_ALL|E_STRICT"以发现严格模式下的错误
        set_exception_handler('System\Corax::handleException');

        //-- 常用类的路径定义 --//
        self::$_prev_loaded_classes = [
            //核心类
            'System\Core\Configer'  	=> SYSTEM_PATH.'Core/Configer.class.php',
            'System\Core\Dispatcher'    => SYSTEM_PATH.'Core/Dispatcher.class.php',
            'System\Core\Controller'    => SYSTEM_PATH.'Core/Controller.class.php',
            'System\Core\Log'           => SYSTEM_PATH.'Core/Log.class.php',
            'System\Core\Storage'       => SYSTEM_PATH.'Core/Storage.class.php',
            'System\Core\Dao'           => SYSTEM_PATH.'Core/Dao.class.php',
            'System\Core\Model'         => SYSTEM_PATH.'Core/Model.class.php',
            'System\Core\View'          => SYSTEM_PATH.'Core/View.class.php',
            'System\Core\Router'        => SYSTEM_PATH.'Core/Router.class.php',

            //核心类常用驱动
            'System\Core\Cach\File'     => SYSTEM_PATH.'Core/Cache/File.class.php',
//            'System\Core\LogDriver\FileDriver'          => SYSTEM_PATH.'Core/LogDriver/File.class.php',
//            'System\Core\StorageDriver\FileDriver'    => SYSTEM_PATH.'Core/Storage/Common.class.php',

            //工具类
            'System\Utils\Util'     => SYSTEM_PATH.'Utils/Util.class.php',
        ];
        self::$_classes = array_merge(self::$_classes,self::$_prev_loaded_classes);

        self::status('init_end');
    }


    public static function start(){
        self::status('start');
        ob_start();//ob缓存开启，直到结束都不会自动输出

        if(RUNTIME_ENVIRONMENT === 'Common'){
            define('LITE_FILE_NAME',RUNTIME_PATH.APP_NAME.'.lite.php');//运行时核心文件
            //考虑到云服务器上lite文件直接使用is_file判断和包含，需要手动上传
            if(is_file(LITE_FILE_NAME)){
                self::status('load_lite_mid');
                include_once LITE_FILE_NAME;
            }
        }
        self::loadFunctions();

        //解析URL
        self::$_url_components = Router::analyse();
//        SEK::dump(self::$_url_components);
        //执行结果
        Dispatcher::execute(
            self::$_url_components['m'], self::$_url_components['c'],
            self::$_url_components['a'], self::$_url_components['p']);
    }

    /**
     * 脚本结束时自行调用
     * @return void
     */
    public static function end(){
        self::status('end');
        if(DEBUG_MODE_ON and PAGE_TRACE_ON){
            self::showTrace();//页面跟踪信息显示
        }

        if(RUNTIME_ENVIRONMENT === 'Common' and (DEBUG_MODE_ON or !is_file(LITE_FILE_NAME))){
            LiteBuilder::build(LITE_FILE_NAME,self::$_prev_loaded_classes);
        }
        ob_get_level() > 0 and ob_end_flush();
    }

    /**
     * 获取预加载的核心类
     * @param null|string $clsnm 类名称，传入null时返回全部
     * @return array
     */
    public static function getClasses($clsnm = null){
        if(isset($clsnm)){
            return self::$_classes[$clsnm];
        }else{
            return self::$_classes;
        }
    }


    private static function loadFunctions(){
        $funcs = Configer::load('function');
        foreach($funcs as $funcname){
            include BASE_PATH."Functions/{$funcname}.php";
        }
    }

    /**
     * 404页面
     */
    public static function showNotFound(){
        self::loadTemplate('404');
    }

    /**
     * 显示trace页面
     * @return void
     */
    public static function showTrace(){
        //吞吐率  1秒/单次执行时间
        $stat = self::status('init_begin','end');
        $reqs = empty($stat[0])?'Unknown':1000*number_format(1/$stat[0],8).' req/s';

        //包含的文件数组
        $files  =  get_included_files();
        $info   =   [];
        foreach ($files as $key=>$file){
            $info[] = $file.' ( '.number_format(filesize($file)/1024,2).' KB )';
        }

        //运行时间与内存开销
        $_infos = self::status();
        $fkey = null;
        $cmprst = [
            'Total' => "{$stat[0]}ms",//一共花费的时间
        ];
        foreach($_infos as $key=>$val){
            if(null === $fkey){
                $fkey = $key;
                continue;
            }
            $cmprst["[$fkey --> $key]    "] = number_format(1000 * floatval($_infos[$key][0] - $_infos[$fkey][0]),6).'ms&nbsp;&nbsp;'.
                number_format((floatval($_infos[$key][1] - $_infos[$fkey][1])/1024),2).' KB';
            $fkey = $key;
        }
        $vars = [
            'trace' => [
                'General'       => [
                    'Request'   => date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'],
                    'IP'        => SEK::getClientIP(),
                    'Time'      => "{$stat[0]}ms",
                    'QPS'       => $reqs,//吞吐率
                    'SessionID' => session_id(),
                    'Cookie'    => var_export($_COOKIE,true),
                    'Obcache-Size'  => number_format((ob_get_length()/1024),2).' KB (Lack TRACE!)',//不包括trace
                    'URLComponents' => var_export(self::$_url_components,true),
                ],
                'Files'         => array_merge(['Total'=>count($info)],$info),
                'Status'        => $cmprst,
                'SQL'           => Dao::log(true),
                'Log'           => Log::getCache(),
//                'Error'         => self::$_errorCache,
                'GET'           => $_GET,
                'POST'          => $_POST,
                'SERVER'        => $_SERVER,
                'FILES'         => $_FILES,
                'ENV'           => $_ENV,
                'SESSION'       => isset($_SESSION)?$_SESSION:['SESSION state disabled'],//session_start()之后$_SESSION数组才会被创建
                'IP'            => [
                    '$_SERVER["HTTP_X_FORWARDED_FOR"]'  =>  isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'NULL',
                    '$_SERVER["HTTP_CLIENT_IP"]'  =>  isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:'NULL',
                    '$_SERVER["REMOTE_ADDR"]'  =>  $_SERVER['REMOTE_ADDR'],
                    'getenv("HTTP_X_FORWARDED_FOR")'  =>  getenv('HTTP_X_FORWARDED_FOR'),
                    'getenv("HTTP_CLIENT_IP")'  =>  getenv('HTTP_CLIENT_IP'),
                    'getenv("REMOTE_ADDR")'  =>  getenv('REMOTE_ADDR'),
                ],
            ],
        ];
        self::loadTemplate('trace',$vars,false);//参数三表示不清空之前的缓存区
    }

    /**
     * 用于用户自定义闭包加载函数
     * @param callable $closure
     * @param bool $into_head 默认追加到标准函数之后，true时追加到标准函数之前
     * @return bool 是否注册成功
     * @Exception 注册失败时会抛出错误
     */
    public static function registerClassLoader(callable $closure,$into_head = false){
        return spl_autoload_register($closure,true,$into_head);
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     * @throws CoraxException
     */
    public static function instance($class,$method='') {
        static $_instances = [];
        $identify   =   $class.$method;
        if(!isset($_instances[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    $_instances[$identify] = call_user_func([&$o, $method]);
                else
                    $_instances[$identify] = $o;
            }else{
                throw new CoraxException($class);
            }
        }
        return $_instances[$identify];
    }

    /**
     * 根据命名空间进行类的自动加载
     * @param string $clsnm
     */
    public static function loadClass($clsnm){
        if(isset(self::$_classes[$clsnm])) {
            include_once self::$_classes[$clsnm];
        }elseif(false !== strpos($clsnm,'\\')){
            $basename           =   strstr($clsnm, '\\', true);//参数3将把第一次出现参数2之前的部分返回，否则返回之后的部分
            $dir = BASE_PATH.$basename;
            if(is_dir($dir)){
                $filename       =   BASE_PATH.str_replace('\\', '/', $clsnm).'.class.php';
                if(is_file($filename) ) {
                    //window下对is_file对文件名称不区分大小写，故这里需要作检测
                    defined('IS_WIN') or define('IS_WIN',false !== stripos(PHP_OS, 'WIN')); //运行环境
                    if (!(IS_WIN && false === strpos(str_replace('/', '\\', realpath($filename)), "{$clsnm}.class.php") )){
                        include_once self::$_classes[$clsnm] = $filename;
                    }
                }

            }
        }
    }

    /**
     * 处理错误的发生
     * 测试代码：trigger_error('发生了错误,- -#，这个是错误信息！ ',E_USER_ERROR);
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public static function handleError($errno, $errstr, $errfile, $errline ){
//        ob_end_clean();
        //错误信息
        if(!is_string($errstr)) $errstr = serialize($errstr);
        ob_start();
        debug_print_backtrace();
        $vars = [
            'message'   => "{$errno} {$errstr}",
            'position'  => "File:{$errfile}   Line:{$errline}",
            'trace'     => ob_get_clean(),//回溯信息
        ];
        try{
            self::$_errors[] = Log::write($vars);
        }catch (\Exception $e){}
        if(DEBUG_MODE_ON){
            self::loadTemplate('error',$vars);
        }else{
            self::loadTemplate('user_error');
        }
        //异常处理完成后仍然会继续执行，需要强制退出
        exit;
    }


    /**
     * @param array $trace
     * @return string
     */
    public static function formatErrorTrace(array $trace){
        $traceString = '';
        if(is_array($trace)){
            foreach($trace as $key => $val){
                //第一行
                $traceString .= "#{$key}";
                if(isset($val['file'])){
                    $traceString .= "  FILE[{$val['file']}] ";
                }
                if(isset($val['line'])){
                    $traceString .= "  LINE[{$val['line']}] ";
                }
                $traceString .= '<br />';

                //次行
                if(isset($val['function'])){
                    $traceString .= "CALL [";
                    if(isset($val['class'])){
                        if(isset($val['type'])){
                            $traceString .= "{$val['class']}{$val['type']}{$val['function']}";
                        }
                    }else{
                        $traceString .= $val['function'];
                    }
                    if(isset($val['args'])){
                        foreach($val['args'] as $v){
                            $traceString .= gettype($v).',';
                        }
                        $traceString = trim($traceString,',');
                    }
                }


            }
        }else{
            $traceString = var_export($trace,true);
        }
        return $traceString;
    }

    /**
     * 处理异常的发生
     * 开放模式下允许将Exception打印打浏览器中
     * 部署模式下不建议这么做，因为回退栈中可能保存敏感信息
     * @param \Exception $e
     */
    public static function handleException($e){
        ob_end_clean();
//        $trace = $e->getTrace();
        $traceString = $e->getTraceAsString();
        //错误信息
        $vars = [
            'message'   => get_class($e).' : '.iconv('gbk','utf-8',$e->getMessage()),
            'position'  => 'File:'.$e->getFile().'   Line:'.$e->getLine(),
            'trace'     => $traceString,//回溯信息，可能会暴露数据库等敏感信息
        ];
        try{
            self::$_errors[] = Log::write($vars);
        }catch (\Exception $e){}
        if(DEBUG_MODE_ON){
            self::loadTemplate('exception',$vars);
        }else{
            self::loadTemplate('user_error');
        }
        //异常处理完成后仍然会继续执行，需要强制退出
        exit;
    }

    /**
     * @param string $type 模板文件类型（名称）
     * @param null|array $vars 变量数组
     * @param bool $endclean 是否清空之前的缓存区
     * @return void
     */
    public static function loadTemplate($type,$vars=null,$endclean=true){
        $endclean and ob_get_level() >0 and ob_end_clean();
        if(is_array($vars)) extract($vars, EXTR_OVERWRITE);
        include SYSTEM_PATH."Tpl/{$type}.php";
    }

    /**
     * 获取和设置运行期间内存使用情况
     * 注：只有在开发模式下查看状态信息，部署模式下失效
     * <code>
     *      status();返回全部记录的信息
     *      status('begin');//记录当前内存使用状态为begin标签
     *      status('begin','end');//如果设置了end标签则返回begin到end之间的时间和内存差数组
     *                            //如果未设置end标签，则将当前状态设置为end，并返回begin到end之间的差数组
     *      status('begin','end'，6);//获取时间差的时候精确到小数点后面6位
     * </code>
     * @param null|string $begin 开始标签
     * @param null|string $end   结束标签
     * @param int $accuracy 小树点后面的位数
     * @return array
     */
    public static function status($begin=NULL,$end=NULL,$accuracy=6){
        static $_infos = [];
        if(DEBUG_MODE_ON) {
            if(NULL === $begin){//参数1为NULL，返回全部
                return $_infos;
            }elseif(NULL === $end){//参数1不为NULL参数2为NULL,设置$begin指向的状态
//                Log::trace($begin,microtime(true),microtime());
                return $_infos[$begin] = [
                    microtime(true),
                    memory_get_usage(),
                ];
            }else{//参数1和参数2都不为NULl
//                Log::trace($_infos,microtime(true));//
                if(isset($_infos[$end])){
                    return [
                        1000*round($_infos[$end][0] - $_infos[$begin][0], $accuracy),
                        number_format(($_infos[$end][1] - $_infos[$begin][1]), $accuracy)
                    ];
                }
            }
        }
        return null;
    }

}