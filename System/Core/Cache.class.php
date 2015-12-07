<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 13:23
 */
namespace System\Core;
use System\Util\SEK;

/**
 * Class Cache 缓存类
 * @package System\Core
 */
class Cache {
    /**
     * 缓存模式
     */
    const CACHEMODE_MEMCACHE = 'Memcached';
    const CACHEMODE_FILE = 'File';
    const CACHEMODE_KVDB = 'Kvdb';

    /**
     * 惯例配置
     * @var array
     */
    private static $convention = [
        'DEFAULT_DRIVER'    => null,
        'MEMCACHE_CONF'     => [
            'HOST'  => 'localhost',
            'PORT'  => 10010,
            'TIMEOUT'   => 1, // 1秒超时
            'CACHE_EXPIRE'  => 3600,//默认缓存时间
        ],
    ];
    /**
     * @var Cache\CacheInterface
     */
    private static $driver = null;

    /**
     * 缓存驱动集合数组
     * @var array
     */
    private static $drivers = [];

    private static $inited = false;

    /**
     * 初始化缓存类
     * @param string $driver_type
     * @throws \System\Exception\CoraxException
     */
    public static function init($driver_type=null){
        //检查是否经过初始化过了
        if(false === self::$inited){
            //注意：Configer::read方法不需要初始化所以能安全使用
            SEK::merge(self::$convention,Configer::read(CONFIG_PATH.'cache.php'));
            self::$inited = true;
        }

        //根据不同环境设置不同的缓存环境
        if(!isset(self::$convention['DEFAULT_DRIVER'])){
            self::$convention['DEFAULT_DRIVER'] =
                (RUNTIME_ENVIRONMENT === 'Sae') ? self::CACHEMODE_MEMCACHE : self::CACHEMODE_FILE;
        }

        //检查对应的驱动是否设置过了
        self::using($driver_type);
    }

    /**
     * 切换使用驱动类型
     * @param null|string $driver_type 驱动类型
     */
    public static function using($driver_type=null){
        if(!isset(self::$drivers[$driver_type])){
            isset($driver_type) or $driver_type = self::$convention['DEFAULT_DRIVER'];
            $classname = "System\\Core\\Cache\\{$driver_type}";
            self::$drivers[$driver_type] = new $classname(self::$convention);
        }
        self::$driver = self::$drivers[$driver_type];
    }

    /**
     * 设置缓存
     * @param string $key 缓存数据ID
     * @param mixed $value 缓存数据
     * @param int $time 缓存时间,如果设置为0则认为永不过期
     * @param string $drivertype 驱动类型
     * @return bool
     */
    public static function set($key,$value,$time=0,$drivertype=null){
        self::init($drivertype);
        return self::$driver->set($key,$value,$time);
    }

    /**
     * 获取缓存数据
     * @param $key
     * @param mixed $replacement 当缓存数据不存在时返回的代替值,默认不存在时返回null
     * @param string $drivertype 驱动类型
     * @return mixed
     */
    public static function get($key,$replacement=null,$drivertype=null){
        self::init($drivertype);
        $rst = self::$driver->get($key);
        return (false === $rst)?$replacement:$rst;
    }

    /**
     * 清空一个缓存或者全部还粗
     * @param string|null $key 缓存数据ID或者设置成null表示清空全部缓存数据
     */
    public static function clear($key=null){
        null === self::$driver and self::init();
        echo $key;
    }

}