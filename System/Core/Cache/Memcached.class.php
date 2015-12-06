<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 15:13
 */
namespace System\Core\Cache;
use System\Exception\CoraxException;
use System\Util\UDK;

/**
 * Class Memcached 缓存类的kvdb驱动
 *
 * @package System\Core\Cache
 */
class Memcached implements CacheInterface{
    /**
     * @var Memcache
     */
    private $mmc = null;

    public function __construct(array $config){

        UDK::dumpout(class_exists('Memcache'));

//        if('Sae' === RUNTIME_ENVIRONMENT){
//            $this->mmc = new Memcache();
//            $ret = $this->mmc->connect();            //使用本应用Memcache
////            $ret = $this->mmc->connect("accesskey"); //使用其他应用的Memcache
//            if(false === $ret){
//                throw new CoraxException('Initialize memcache for sae failed!');
//            }
//        }else{
//            $this->mmc = new Memcache();
//            $this->mmc->connect($config['HOST'], $config['PORT'], $config['TIMEOUT']);
//        }
    }

    public function get($key){
//        return $this->mmc->get($this->mmc, $key);
    }

    public function set($key,$value,$expire=0){
//        return $this->mmc->set($this->mmc,$key,$value,0,$expire);
    }

}