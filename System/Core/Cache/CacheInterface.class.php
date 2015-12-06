<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/6
 * Time: 19:58
 */
namespace System\Core\Cache;

interface CacheInterface {
    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expire 超时时间，如果为0表示永不超时
     * @return bool
     */
    public function set($key,$value,$expire=0);

}