<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/16
 * Time: 13:15
 */
namespace System\Core;
use System\Exception\CoraxException;
use System\Corax;
use System\Util\SEK;
use System\Util\UDK;

defined('BASE_PATH') or die('No Permission!');
/**
 * Class Configer
 * 配置加载帮助类
 * 由于SAE部署的需要，决定配置文件的读取不能依赖Storage类
 * 因为配置文件仅仅涉及本地配置文件的读取，而Storage则涉及上传等操作可能导致权限的不足
 * @package System\Core
 */
class Configer{

    /**
     * 预定义的配置文件类型
     * 同时保存了配置文件后缀信息
     */
    const CONFIGTYPE_PHP = '.php';
    const CONFIGTYPE_INI = '.ini';
    //.....

    /**
     * 惯例配置
     * @var array
     */
    private static $convention = [
        //需要加载的配置文件数组
        'CONFIG_LIST'    => [
            'custom','database','guide','hook','log','modules','route','security','template','cache'
        ],
        'REFRESH_INTERVAL' => 3600,//刷新间隔
    ];

    /**
     * 配置缓存信息
     * @var array
     */
    private static $config_cache = null;

    /**
     * 初始化配置类
     * @param bool $forceRefresh 是否强制刷新配置，默认不刷新
     * @return void
     */
    public static function init($forceRefresh=false){
        Corax::status('config_init_begin');
        //获取配置缓存

        if($forceRefresh or
                (null === (self::$config_cache = Cache::get('configure',null))) ){
            //配置未缓存
            self::$convention = self::read(CONFIG_PATH.'configer.php');
            foreach(self::$convention['CONFIG_LIST'] as $item){
                self::$config_cache[$item] = self::read(CONFIG_PATH."{$item}.php");
            }
            Cache::set('configure',self::$config_cache,self::$convention['REFRESH_INTERVAL']);
            Corax::status('config_init_maketemp_done');
        }
        Corax::status('config_init_begin');
    }

    /**
     * 加载配置文件
     * @param string $confnm 配置项名称,默认是有用户自定义的名称
     * @return array
     * @throws CoraxException
     */
    public static function load($confnm='custom'){
        null === self::$config_cache and self::init();
        $confnm = strtolower($confnm);
        if(!isset(self::$config_cache[$confnm])){//不存在该配置
            throw new CoraxException($confnm);
        }
        return self::$config_cache[$confnm];
    }
    /**
     * 获取配置信息
     * 示例：
     *  database.DB_CONNECT.0.type
     * 除了第一段外要注意大小写
     * @param string $confnm
     * @return mixed
     * @throws CoraxException
     */
    public static function get($confnm = null){
        null === self::$config_cache and self::init();
        $configes = null;//配置分段，如果未分段则保持null的值
        $value = null;//最终将被返回的值
        if(null === $confnm){//默认参数时返回全部
            return self::$config_cache;
        }
        if(false !== strpos($confnm,'.')){
            $configes = explode(',',$confnm);
            $confnm = array_shift($configes);
        }
        $confnm = strtolower($confnm);
        if(!isset(self::$config_cache[$confnm])){//不存在该配置
            throw new CoraxException($confnm);
        }
        $value = self::$config_cache[$confnm];
        if($configes){
            foreach($configes as $val){
                if(isset($value[$val])){
                    $value = $value[$val];
                }else{
                    return null;
                }
            }
        }
        return $value;
    }

    /**
     * @param $confnm
     * @param $value
     * @return bool
     * @throws CoraxException
     */
    public static function set($confnm,$value){
        null === self::$config_cache and self::init();
        $configes = null;//配置分段，如果未分段则保持null的值
        $var = null;
        if(false !== strpos($confnm,'.')){
            $configes = explode(',',$confnm);
            $confnm = array_shift($configes);
        }
        $confnm = strtolower($confnm);
        if(!isset(self::$config_cache[$confnm])){//不存在该配置
            throw new CoraxException($confnm);
        }
        $var = self::$config_cache[$confnm];
        foreach($configes as $val){
            if(isset($value[$val])){
                $var = $var[$val];
            }else{
                return false;
            }
        }
        return true;
    }

    /**
     * 读取配置文件内容
     * @param string $path 配置文件的完整路径
     * @param string $type 配置文件类型，默认使用PHP形式的配置类型
     * @return array
     * @throws CoraxException
     */
    public static function read($path,$type=self::CONFIGTYPE_PHP){
        static $_conf = array();
        null === self::$config_cache and self::init();
        if(!isset($_conf[$path])){//部署运行阶段配置文件不会发生变化
            switch($type){
                //other config type ...
                case self::CONFIGTYPE_PHP:
                default:
                    $_conf[$path] = include $path;
            }
        }
        return $_conf[$path];
    }

    /**
     * 将配置写入到配置文件中文件中
     * @param string $path 配置文件的完整路径
     * @param array $config 配置数组
     * @param string $type 配置文件类型
     * @return bool
     */
    public static function write($path,array $config,$type=self::CONFIGTYPE_PHP){
        null === self::$config_cache and self::init();
        switch($type){
            //...other config type ...
            case self::CONFIGTYPE_PHP:
            default:
                $filename = pathinfo($path,PATHINFO_FILENAME);
                self::$config_cache[substr($filename,0,strpos($filename,'.'))] = $config;
                return Storage::write($path,'<?php return '.var_export($config,true).'; ?>'); //闭包函数无法写入
        }
    }

    /**
     * 将配置写入数组
     * 自动配置信息一般随模块的安装而生成的，不能随意修改
     * @param string$confnm
     * @param array $config
     * @param null $path
     * @return bool
     */
    public static function writeConfig($confnm,array $config,$path){
        null === self::$config_cache and self::init();
        //配置文件路径，不同的配置文件类型拥有不同的后缀
        $path = "{$path}/{$confnm}.php";
        if(Storage::has($path)){
            //文件存在，读取并合并配置
            $origin_config = self::read($path);
            SEK::merge($origin_config,$config);//后者覆盖前者
            $config = $origin_config;
        }
        return Storage::write($path,'<?php return '.var_export($config,true).'; ?>'); //闭包函数无法写入
    }

    /**
     * TODO:编译原始配置，使其呈现较高的效率
     */
    public static function build(){

    }

}