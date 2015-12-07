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

defined('BASE_PATH') or die('No Permission!');
/**
 * Class Configer
 * 配置加载帮助类
 *  由于SAE部署的需要，决定配置文件的读取不能依赖Storage类
 *  因为配置文件仅仅使用include本地的配置文件，而Storage则涉及上传等操作可能导致权限的不足
 * 由于是include文件，故无法设置回掉等复杂的类型,否则会被替换成另一种形式
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
        //刷新间隔,经过这段时间间隔后，缓存中的数据将被清空，需要重新获取,这么提高了效率和实时性
        //此外用户可以到Memcache服务管理页面手动刷新
        'REFRESH_INTERVAL' => 3600,
        //缓存方式默认为文件缓存,本地部署使用，SAE环境下不适用
        'CACHE_DRIVER_TYPE' => Cache::CACHEMODE_FILE,
    ];

    /**
     * 配置缓存信息
     * @var array
     */
    private static $config_cache = null;

    /**
     * 初始化配置类
     * 主要是获取或者设置配置集合
     * @param bool $forceRefresh 是否强制刷新配置，默认不刷新
     * @return void
     */
    public static function init($forceRefresh=false){
        Corax::status('config_init_begin');
        //获取配置缓存
        SEK::merge(self::$convention,self::read(CONFIG_PATH.'configer.php'));

        //要求强制刷新或者获取配置缓存为空时
        if($forceRefresh or (null === (self::$config_cache = Cache::get('configure',null,self::$convention['CACHE_DRIVER_TYPE']))) ){
            //配置未缓存
            foreach(self::$convention['CONFIG_LIST'] as $item){
                self::$config_cache[$item] = self::read(CONFIG_PATH."{$item}.php");
            }
            //缓存
            Cache::set('configure',self::$config_cache,self::$convention['REFRESH_INTERVAL'],self::$convention['CACHE_DRIVER_TYPE']);
            Corax::status('config_init_create_cache_done');
        }
        Corax::status('config_init_end');
    }

    /**
     * 加载配置文件
     * @param string $confnm 配置项名称,默认是有用户自定义的名称
     * @return array
     * @throws CoraxException
     */
    public static function load($confnm='custom'){
        //先要经过初始化
        null === self::$config_cache and self::init();
        $confnm = strtolower($confnm);
        if(!isset(self::$config_cache[$confnm])){//不存在该配置
            throw new CoraxException("Configure item '{$confnm}' not exist!");
        }
        return self::$config_cache[$confnm];
    }
    /**
     * 获取配置信息
     * 示例：
     *  database.DB_CONNECT.0.type
     * 除了第一段外要注意大小写
     * @param string $confnm 配置名称
     * @param null $rplvalue 当指定的配置项不存在时,仅仅在获取第二段开始的部分时有效
     * @return mixed 返回配置信息数组
     * @throws CoraxException
     */
    public static function get($confnm = null,$rplvalue=null){
        null === self::$config_cache and self::init();
        $configes = null;//配置分段，如果未分段则保持null的值

        if(null === $confnm){//默认参数时返回全部
            return self::$config_cache;
        }
        //检测是否分段
        if(false !== strpos($confnm,'.')){
            $configes = explode('.',$confnm);
            $confnm = array_shift($configes);
        }

        //获取第一段的配置
        $rtn = self::load($confnm);

        //如果为true表示是经过分段的
        if($configes){
            foreach($configes as $val){
                $val = strtoupper($val);//配置项全部大写
                if(isset($rtn[$val])){
                    $rtn = $rtn[$val];
                }else{
                    return $rplvalue;
                }
            }
        }
        return $rtn;
    }

    /**
     * 设置临时配置项
     * 下次请求时临时的配置将被清空
     * <code>
     *  UDK::dump(Configer::get());
     *  Configer::set('custom.name.value',true);
     *  UDK::dump(Configer::get());
     * </code>
     * @param string $confnm 配置项名称，同get方法，可以是分段的设置
     * @param mixed $value 配置项的值
     * @return void
     * @throws CoraxException 要设置的第一项不存在时抛出异常
     */
    public static function set($confnm,$value){
        null === self::$config_cache and self::init();
        $configes = null;//配置分段，如果未分段则保持null的值

        if(false !== strpos($confnm,'.')){
            $configes = explode('.',$confnm);
            $confnm = array_shift($configes);
        }

        $confnm = strtolower($confnm);
        if(!isset(self::$config_cache[$confnm])){//不存在该配置
            throw new CoraxException($confnm);
        }
        $confvars = &self::$config_cache[$confnm];

        if($configes){
            foreach($configes as $val){
                if(!isset($confvars[$val])){
                    $confvars[$val] = [];
                }
                $confvars = &$confvars[$val];
            }
            $confvars = $value;
        }
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
     * 原本用于本地部署的写入配置集合配置文件
     * SAE部署的环境下无法写入，由于有了Cache类的get和set方法,这个方法将没有使用的余地
     * @param string $path 配置文件的完整路径
     * @param array  $config 配置数组
     * @param string $type 写入的配置文件类型
     * @return bool 是否写入成功
     */
    public static function write($path,array $config,$type=self::CONFIGTYPE_PHP){
        switch($type){
            //...other config type ...
            case self::CONFIGTYPE_PHP:
            default:
                $filename = pathinfo($path,PATHINFO_FILENAME);
                $confname = substr($filename,0,strpos($filename,'.')); // 配置名称
                self::$config_cache[$confname] = $config;
                return Storage::write($path,'<?php return '.var_export($config,true).';'); //闭包函数无法写入
        }
    }

}