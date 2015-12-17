<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/17
 * Time: 9:16
 */
namespace System\Core\Session;
use System\Exception\CoraxException;

/**
 * Class SessionInterface Session的接口类
 * @package System\Core\Session
 */
abstract class SessionDriver {

    /**
     * 获取指定名称的session的值
     * @param null|string $name 为null时获取全部session
     * @return mixed
     */
    abstract public function get($name=null);

    /**
     * 设置session
     * @param string $name
     * @param mixed $value
     * @return void
     */
    abstract public function set($name,$value);

    /**
     * 检查是否设置了指定名称的session
     * @param string $name
     * @return bool
     */
    abstract public function has($name);
    /**
     * 清除指定名称的session
     * @param string|array $name 如果为null将清空全部
     * @return void
     * @throws CoraxException
     */
    abstract public function clear($name=null);

}