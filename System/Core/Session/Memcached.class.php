<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/12
 * Time: 19:32
 */
namespace System\Core\Session;

use System\Util\SEK;

class Memcached {
    /**
     * 惯例配置
     * @var array
     */
    private $convention = [
        'SESSION_EXPIRE'    => null,
    ];

    protected $lifeTime     = 3600;
    protected $sessionName  = '';
    protected $handle       = null;

    public function __construct(array $config=null){
        if(null !== $config){
            SEK::merge($this->convention,$config);
        }

    }

    /**
     * 打开Session
     * @return bool
     */
    public function open() {
        $this->lifeTime     = C('SESSION_EXPIRE') ? C('SESSION_EXPIRE') : $this->lifeTime;
        // $this->sessionName  = $sessName;
        $options            = array(
            'timeout'       => C('SESSION_TIMEOUT') ? C('SESSION_TIMEOUT') : 1,
            'persistent'    => C('SESSION_PERSISTENT') ? C('SESSION_PERSISTENT') : 0
        );
        $this->handle       = new \Memcache;
        $hosts              = explode(',', C('MEMCACHE_HOST'));
        $ports              = explode(',', C('MEMCACHE_PORT'));
        foreach ($hosts as $i=>$host) {
            $port           = isset($ports[$i]) ? $ports[$i] : $ports[0];
            $this->handle->addServer($host, $port, true, 1, $options['timeout']);
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close() {
        $this->gc(ini_get('session.gc_maxlifetime'));
        $this->handle->close();
        $this->handle       = null;
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param string $sessID
     */
    public function read($sessID) {
        return $this->handle->get($this->sessionName.$sessID);
    }

    /**
     * 写入Session
     * @access public
     * @param string $sessID
     * @param String $sessData
     */
    public function write($sessID, $sessData) {
        return $this->handle->set($this->sessionName.$sessID, $sessData, 0, $this->lifeTime);
    }

    /**
     * 删除Session
     * @access public
     * @param string $sessID
     */
    public function destroy($sessID) {
        return $this->handle->delete($this->sessionName.$sessID);
    }

    /**
     * Session 垃圾回收
     * @access public
     * @param string $sessMaxLifeTime
     */
    public function gc($sessMaxLifeTime) {
        return true;
    }
}