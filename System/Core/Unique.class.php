<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/10
 * Time: 19:30
 */
namespace System\Core;
use System\Util\SEK;

/**
 * Class Unique 单例设计模式类
 * @package System\Core
 */
class Unique {

    /**
     * 管理配置
     * @var array
     */
    protected $convention = [];

    /**
     * 类的实例
     * @var array
     */
    protected static $instances = [];

    /**
     * 初始化构造
     * @param array|null $config 覆盖惯例配置
     */
    protected function __construct(array $config=null){
        isset($config) and SEK::merge($this->convention,$config);
    }

    /**
     * 获取实例
     * @param array|null $config 覆盖惯例配置
     * @param bool|false $forcereflesh 是否强制刷新配置，默认为否，测试情况下可以为true
     * @return mixed
     */
    public static function getInstance(array $config=null,$forcereflesh=false){
        $key = get_called_class();//使用 "调用的函数名称 + 配置项签名" 作为key
        if($forcereflesh or !isset(static::$instances[$key])){
            static::$instances[$key] = new static($config);
        }
        return static::$instances[$key];
    }

}