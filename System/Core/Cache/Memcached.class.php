<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 15:13
 */
namespace System\Core\Cache;
use System\Exception\CoraxException;

/**
 * Class Memcached 缓存类的kvdb驱动
 *
 * @package System\Core\Cache
 */
class Memcached implements CacheInterface{
    /**
     * @var \Memcache
     */
    private $mmc = null;

    /**
     * 构造
     * 注意Memcahe的完整类名称
     * 由于在命名空间下，所以需要额外添加'\'否则将会使用“System\Core\Cache\Memcache”的类名而导致以下的错误：
     * Fatal error: Class 'System\Core\Cache\Memcache' not found in /data1/www/htdocs/232/linzhv/1/corax/System/Core/Cache/Memcached.class.php on line 26
     *
     * @param array $config
     * @throws CoraxException
     */
    public function __construct(array $config){
        //根据不同的环境使用不同的配置
        if('Sae' === RUNTIME_ENVIRONMENT){
            $this->mmc = new \Memcache();
            $ret = @$this->mmc->connect();            //使用本应用Memcache
//            $ret = $this->mmc->connect("accesskey"); //使用其他应用的Memcache
            if(false === $ret){
                throw new CoraxException('Initialize memcache for sae failed!');
            }
        }else{
            $this->mmc = new \Memcache();
            $this->mmc->connect($config['HOST'], $config['PORT'], $config['TIMEOUT']);
        }
    }

    public function get($key){
        return $this->mmc->get($key);
    }

    public function set($key,$value,$expire=0){
        return $this->mmc->set($key,$value,0,$expire);
    }

}