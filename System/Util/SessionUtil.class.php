<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/9/12
 * Time: 17:51
 */
namespace System\Util;
use System\Exception\ParameterInvalidException;
/**
 * Class SessionUtil 会话操作类
 * @package System\Utils
 *
 * 不再使用的函数列表：
 * ①session_is_registered
 * ②session_register
 * ③session_unregister
 */
class SessionUtil{
    /**
     * 客户端缓存控制策略
     * 客户端或者代理服务器通过检测这个响应头信息来 确定对于页面内容的缓存规则
     * nocache 会禁止客户端或者代理服务器缓存内容
     * public 表示允许客户端或代理服务器缓存内容
     * private 表示允许客户端缓存， 但是不允许代理服务器缓存内容
     * private 模式下， 包括 Mozilla 在内的一些浏览器可能无法正确处理 Expire 响应头， 通过使用 private_no_expire 模式可以解决这个问题：在这种模式下， 不会向客户端发送 Expire 响应头
     */
    /**
     * Expires: Thu, 19 Nov 1981 08:52:00 GMT
     * Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
     * Pragma: no-cache
     */
    const LIMITER_NOCACHE = 'nocache';
    /**
     * Expires：（根据 session.cache_expire 的设定计算得出）
     * Cache-Control： public, max-age=（根据 session.cache_expire 的设定计算得出）
     * Last-Modified：（会话最后保存时间）
     */
    const LIMITER_PUBLIC  = 'public';
    /**
     * Expires: Thu, 19 Nov 1981 08:52:00 GMT
     * Cache-Control: private, max-age=（根据 session.cache_expire 的设定计算得出）,
     *      pre-check=（根据 session.cache_expire 的设定计算得出）
     * Last-Modified: （会话最后保存时间）
     */
    const LIMITER_PEIVATE = 'private';
    /**
     * Cache-Control: private, max-age=（根据 session.cache_expire 的设定计算得出）,
     *      pre-check=（根据 session.cache_expire 的设定计算得出）
     * Last-Modified: （会话最后保存时间）
     */
    const LIMITER_PEIVATE_WITHOUT_EXPIRE = 'private_no_expire';
    /**
     * 配置选项
     * @var array
     */
    private static $_config = array(
        'cache_expire'  => 180,//默认session到期时间
        'name'          => APP_NAME,//会话名称
    );
    /**
     * 对session操作类进行初始化配置
     * @param array $config
     * @return bool
     */
    public static function init(array $config){
        if(is_array($config)){
            self::$_config = array_merge(self::$_config,$config);
            return true;
        }
        return false;
    }
    /**
     * 获取 或者 设置当前缓存的到期时间
     * 注意需要在session_start之前调用才有效
     * @param string|null $new_cache_expire 新的到期时间，单位为分钟，如果为null表示获取
     * @return bool|int false时表示设置失败
     */
    public static function cacheExpire($new_cache_expire =null){
        if(isset($new_cache_expire)){
            if('nocache' === ini_get('session.cache_limiter')){
                return false;
            }
            return session_cache_expire('session.cache_expire');
        }
        return session_cache_expire();
    }
    /**
     * 获取和设置当前缓存限制器的名称
     * 注意需要在session_start之前调用才有效
     * @param null|string $cache_limiter 为null时获取当前缓存限制器名称
     * @return string
     */
    public static function cacheLimiter($cache_limiter = null){
        if(null === $cache_limiter){
            return session_cache_limiter();
        }
        return session_cache_limiter($cache_limiter);
    }
    /**
     * 读取/设置会话名称
     * 用在 cookie 或者 URL 中的会话名称， 例如：PHPSESSID。
     * 只能使用字母和数字作为会话名称，建议尽可能的短一些，
     * 并且是望文知意的名字（对于启用了 cookie 警告的用户来说，方便其判断是否要允许此 cookie）。
     * 如果指定了 name 参数， 那么当前会话也会使用指定值作为名称
     * @param string $newname null时返回当前的session名称，否则设置并返回之前的名称
     * @return string
     */
    public static function name($newname=null){
        return session_name($newname);
    }
    /**
     * Session data is usually stored after your script terminated without the need to call session_write_close()
     * session数据通常在脚本执行结束后存储，而不需要调用函数session_write_close
     * but as session data is locked to prevent concurrent writes only one script may operate on a session at any time
     * 但是由于为阻止并行的写入session数据会被上锁，其结果是任何时候只有一个脚本才能操作一个session
     * When using framesets together with sessions you will experience the frames loading one by one due to this locking
     * 浏览器中使用frameset和session的时候，你会经历到frame会逐一加载frame，这归因于此
     * You can reduce the time needed to load all the frames by ending the session as soon as all changes to session variables are done.
     * 你可以减少所有的frame的加载时间，通过当session数据操作完成后尽快结束session的方式
     * @return void
     */
    public static function commit(){
        session_write_close();
    }
    /**
     * 返回当前会话编码后的数据，即$_SESSION
     * @return string
     */
    public static function encode(){
        return session_encode();
    }
    /**
     * 对参数进行session解码，并填充到$_SESSION变量中
     * @param string $code_data 待解码的数据
     * @return bool
     */
    public static function decode($code_data){
        return session_decode($code_data);
    }
    /**
     * Re-initialize session array with original values
     * 重置session的改动，恢复到最初的状态
     * @return void
     */
    public static function reset(){
        session_reset();
    }
    /**
     * 获取和设置session的保存路径
     * @param string|null $path 参数为null时获取保存路径
     * @return string
     */
    public static function savePath($path=null){
        return session_save_path($path);
    }
    /**
     * 获取session的状态(5.4)
     * PHP_SESSION_DISABLED if sessions are disabled.
     * PHP_SESSION_NONE if sessions are enabled, but none exists.
     * PHP_SESSION_ACTIVE if sessions are enabled, and one or more exists.
     * @return int 状态常量
     */
    public static function status(){
        return session_status();
    }
    /**
     * 清楚session
     * @param string|array $name
     * @return void
     * @throws ParameterInvalidException
     */
    public static function clear($name=null){
        if(null === $name){
            $_SESSION = array();
        }elseif(is_string($name)){
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                unset($_SESSION[$name1][$name2]);
            }else{
                unset($_SESSION[$name]);
            }
        }elseif(is_array($name)){
            foreach($name as $val){
                self::clear($val);
            }
        }else{
            throw new ParameterInvalidException($name);
        }
    }
    /**
     * 检查是否设置
     * @param string $name
     * @return bool
     */
    public static function has($name){
        if(strpos($name,'.')){ // 支持数组
            list($name1,$name2) =   explode('.',$name);
            return isset($_SESSION[$name1][$name2]);
        }else{
            return isset($_SESSION[$name]);
        }
    }
    /**
     * @param null|string $name 为null时获取全部session
     * @return null
     */
    public static function get($name=null){
        self::start();
        if(isset($name)){
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }
        }
        return $_SESSION;
    }

    public static function sessionAutoStart(){
    }
    /**
     * 开启会话
     * 必须在脚本输出之前调用
     * @return bool
     */
    public static function start(){
        if(PHP_SESSION_ACTIVE !== self::status()){
            return session_start();
        }
        return true;
    }
    /**
     * @return void
     */
    public static function pause(){
        session_write_close();
    }
    /**
     * 销毁会话中全部数据
     * 要想重新使用session，需要重新调用session_start函数
     * @return bool
     */
    public static function destroy(){
//        $_SESSION =  array();
        //unset($_SESSION)会导致$_SESSION数组彻底地不能使用
        //调用session_unset可以释放所有的注册的session变量
        session_unset();
        return session_destroy();
    }
    /**
     * sessionID操作
     * @param string|null  $id 设置的sessionID
     * @param bool|false $regenerate 是否重新生成sessionID
     * @return string
     */
    public static function id($id=null,$regenerate=false){
        $regenerate and session_regenerate_id();
        return session_id($id);
    }
    /**
     * 要求PHP版本在5.4之后才能使用
     *  设置用户自定义会话存储处理类（版本5.4以后使用）
     * @param \SessionHandlerInterface $session_handler 实现了 SessionHandlerInterface 接口的对象,例如 SessionHandler
     * @param bool|true $register_shutdown 将函数 session_write_close() 注册为 register_shutdown_function() 函数
     *                                     默认为true表示session自动在脚本执行结束的时候调用
     * @return bool
     */
    public static function setSaveHandler(\SessionHandlerInterface $session_handler, $register_shutdown= true){
        return @session_set_save_handler($session_handler, $register_shutdown);
    }
    /**
     * 获取/设置会话 cookie 参数
     * 返回数组 array(
     *      "lifetime",// - cookie 的生命周期，以秒为单位。
     *      "path",// - cookie 的访问路径。
     *      "domain",// - cookie 的域。
     *      "secure",// - 仅在使用安全连接时发送 cookie。
     *      "httponly",// - 只能通过 http 协议访问 cookie
     * )
     * 以下方法等效
     * ini_get('session.cookie_lifetime'),
     * ini_get('session.cookie_path'),
     * ini_get('session.cookie_domain'),
     * ini_get('session.cookie_secure'),
     * ini_get('session.cookie_httponly'),
     *      <==>
     * session_get_cookie_params()
     * @param array $params cookie参数设置
     * @return mixed
     */
    public static function cookieParams($params=null){
        if(isset($params)){
            session_set_cookie_params(
                $params[0],
                isset($params[1])?$params[1]:null,
                isset($params[2])?$params[2]:null,
                isset($params[3])?$params[3]:false,
                isset($params[4])?$params[4]:false
            );
        }
        return session_get_cookie_params();
    }
    /**
     * 设置session
     * @param string $name
     * @param mixed $value
     * @return mixed 返回设置的值
     */
    public static function set($name,$value){
        self::start();
        if(strpos($name,'.')){
            list($name1,$name2) =   explode('.',$name,2);
            $_SESSION[$name1][$name2] = $value;
        }else{
            $_SESSION[$name] = $value;
        }
        return $value;
    }
}