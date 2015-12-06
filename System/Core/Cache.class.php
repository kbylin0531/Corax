<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 13:23
 */
namespace System\Core;
use System\Util\SEK;
use System\Util\UDK;

/**
 * Class Cache 缓存类
 * @package System\Core
 */
class Cache {
    /**
     * 缓存模式
     */
    const CACHEMODE_FILE = 'File';
    const CACHEMODE_KVDB = 'Kvdb';
    const CACHEMODE_MEMCACHE = 'Memcached';

    /**
     * 惯例配置
     * @var array
     */
    private static $convention = [];
    /**
     * @var Cache\CacheInterface
     */
    private static $driver = null;

    /**
     * 初始化缓存类
     * @param string $driver_type
     * @throws \System\Exception\CoraxException
     */
    public static function init($driver_type=null){
        SEK::merge(self::$convention,isset($config)?$config:Configer::load('cache'));
        if(null === self::$driver){
            isset($driver_type) or $driver_type = self::$convention['DEFAULT_DRIVER'];
            $classname = "System\\Core\\Cache\\{$driver_type}";
//            UDK::dumpout($classname,class_exists($classname));
            self::$driver = new $classname(self::$convention);
        }

    }

    /**
     * 设置缓存
     * @param string $key 缓存数据ID
     * @param mixed $value 缓存数据
     * @param int $time 缓存时间,如果设置为0则认为永不过期
     * @return bool
     */
    public static function set($key,$value,$time=0){
        null === self::$driver and self::init();
        return self::$driver->set($key,$value,$time);
    }

    /**
     * 获取缓存数据
     * @param $key
     * @param null $replacement 当缓存数据不存在时返回的代替值,默认不存在时返回null
     * @return mixed
     */
    public static function get($key,$replacement=null){
        null === self::$driver and self::init();
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