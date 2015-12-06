<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/9/12
 * Time: 21:53
 */
namespace System\Utils;
class CookieUtil {
    private static $_config = array(
        'prefix'    =>  '', // cookie 名称前缀
        'expire'    =>  0, // cookie 保存时间
        'path'      =>  '/', // cookie 保存路径
        'domain'    =>  '', // cookie 有效域名
        'secure'    =>  false, //  cookie 启用安全传输
        'httponly'  =>  0, // httponly设置
    );
    /**
     * 对session操作类进行初始化配置
     * @param array $config
     * @return bool
     */
    public static function init(array $config){
        if(is_array($config)){
            self::$_config = array_merge(self::$_config,array_change_key_case($config));
            return true;
        }
        return false;
    }
    public static function clear($name=''){
        if('' === $name){
            //清除所有
            if($_COOKIE){
                $prefix = empty($value) ? self::$_config['prefix'] : $value;
                //如果设置了前缀，则删除指定的前缀的cookie
                if($prefix){
                    foreach ($_COOKIE as $key => $val) {
                        if (0 === stripos($key, $prefix)) {
                            setcookie($key, '', time() - 3600,//时间设置为之前就可以了
                                self::$_config['path'],
                                self::$_config['domain'],
                                self::$_config['secure'],
                                self::$_config['httponly']
                            );
                            unset($_COOKIE[$key]);
                        }
                    }
                }
            }else{
                //为空数组
            }
        }else{
        }
    }
    /**
     * 向客户端发送cookie
     * @param $name
     * @param $value
     */
    public static function setCookie($name,$value){
        $name = self::$_config['prefix'].$name;
        $value  = serialize($value);
        $expire = self::$_config['expire']? time() + intval(self::$_config['expire']) : 0;
        setcookie($name, $value, $expire,
            self::$_config['path'],
            self::$_config['domain'],
            self::$_config['secure'],
            self::$_config['httponly']
        );
        $_COOKIE[$name] = $value;
    }
    /**
     * 获取cookie数据
     * @param $name
     * @param null $defult_value
     * @return null
     */
    public static function getCookie($name=null,$defult_value = null){
        if(null === $name) return $_COOKIE;
        $name = self::$_config['prefix'] . str_replace('.', '_', $name);
        $key = self::$_config['prefix'].$name;
        return isset($_COOKIE[$key])?$_COOKIE[$key]:$defult_value;
    }
}