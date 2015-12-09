<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/12
 * Time: 19:31
 */
namespace System\Core;
use System\Util\SEK;

/**
 * Class Session Session管理器，可以使用多种驱动
 * @package System\Core
 */
class Session {

    const SESSION_MODE_COMMON = 'Common';
    const SESSION_MODE_MEMCACHE = 'Memcache';
    const SESSION_MODE_KV_DB = 'Kvdb';

    /**
     * 惯例配置
     * @var array
     */
    private static $convention = [];

    private static $driver = null;

    private static $hasInited = false;

    public static function init(array $config=null){
        //检查是否经过初始化过了
        if(false === self::$hasInited){
            //注意：Configer::read方法不需要初始化所以能安全使用
            SEK::merge(self::$convention,Configer::load('session'));
            self::$hasInited = true;
        }




    }

    public static function set(){}

    public static function get(){}

    public static function clear(){}

}