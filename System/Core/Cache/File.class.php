<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 15:13
 */
namespace System\Core\Cache;
use System\Util\SEK;

/**
 * Class File Cache缓存的文件驱动
 * 修改自ThinkPHP：ThinkPHP\Library\Think\Cache\Driver\File.class.php
 * 配置文件修改自：ThinkPHP\Conf\convention.php
 * @package System\Core\Cache
 */
class File implements CacheInterface {

    /**
     * 惯例配置
     * @var array
     */
    private $convention = [

        'DATA_CACHE_PATH'       =>  RUNTIME_PATH.'Cache/',// 缓存路径设置 (仅对File方式缓存有效)
        'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
        'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
        'DATA_CACHE_LENGTH'     =>  0,
        'DATA_CACHE_TEMP_AUTH'  =>  0755,   //缓存目录权限

        'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)


        'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
        'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
        'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
        'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
        'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别
    ];
    /**
     * 采集自thinkphp的选项，方便移植需要未删除
     * @var array
     */
    private $options = [];

    /**
     * 架构函数
     * @access public
     * @param array $config 配置项
     */
    public function __construct(array $config=null) {
        isset($config) and SEK::merge($this->convention,$config['FILE_CONF']);

        $this->options['temp']      =   $this->convention['DATA_CACHE_PATH'];
        $this->options['prefix']    =   $this->convention['DATA_CACHE_PREFIX'];
        $this->options['expire']    =   $this->convention['DATA_CACHE_TIME'];
        $this->options['length']    =   $this->convention['DATA_CACHE_LENGTH'];

        // 创建应用缓存目录
        if(!is_dir($this->options['temp'])){
            mkdir($this->options['temp'],$this->convention['DATA_CACHE_TEMP_AUTH'],true);
        }else{
            chmod($this->options['temp'],$this->convention['DATA_CACHE_TEMP_AUTH']);
        }
    }


    /**
     * 取得变量的存储文件名
     * @access private
     * @param string $name 缓存变量名
     * @return string
     */
    private function filename($name) {
        $name	=	md5($this->convention['DATA_CACHE_KEY'].$name);
        if($this->convention['DATA_CACHE_SUBDIR']) {
            // 使用子目录
            $dir   ='';
            for($i=0;$i<$this->convention['DATA_PATH_LEVEL'];$i++) {
                $dir	.=	$name{$i}.'/';
            }
            if(!is_dir($this->options['temp'].$dir)) {
                mkdir($this->options['temp'].$dir,0755,true);
            }
            $filename	=	$dir.$this->options['prefix'].$name.'.php';
        }else{
            $filename	=	$this->options['prefix'].$name.'.php';
        }
        return $this->options['temp'].$filename;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $filename   =   $this->filename($name);
        if (!is_file($filename)) {
            return false;
        }
        $content    =   file_get_contents($filename);
        if( false !== $content) {
            $expire  =  (int)substr($content,8, 12);
            if($expire != 0 && time() > filemtime($filename) + $expire) {
                //缓存过期删除缓存文件
                unlink($filename);
                return false;
            }
            if($this->convention['DATA_CACHE_CHECK']) {//开启数据校验
                $check  =  substr($content,20, 32);
                $content   =  substr($content,52, -3);
                if($check != md5($content)) {//校验错误
                    return false;
                }
            }else {
                $content   =  substr($content,20, -3);
            }
            if($this->convention['DATA_CACHE_COMPRESS'] && function_exists('gzcompress')) {
                //启用数据压缩
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize($content);
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param int $expire  有效时间 0为永久
     * @return boolean
     */
    public function set($name,$value,$expire=null) {
        if(is_null($expire)) {
            $expire =  $this->options['expire'];
        }
        $filename   =   $this->filename($name);
        $data   =   serialize($value);
        if( $this->convention['DATA_CACHE_COMPRESS'] && function_exists('gzcompress')) {
            //数据压缩
            $data   =   gzcompress($data,3);
        }
        if( $this->convention['DATA_CACHE_CHECK']) {//开启数据校验
            $check  =  md5($data);
        }else {
            $check  =  '';
        }
        $data    = "<?php\n//".sprintf('%012d',$expire).$check.$data."\n?>";
        $result  =   file_put_contents($filename,$data);
        if($result) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            clearstatcache();
            return true;
        }else {
            return false;
        }
    }
    /**
     * 队列缓存
     * @access protected
     * @param string $key 队列名
     * @return mixed
     */
    //
    protected function queue($key) {
        static $_handler = array(
            'file'  =>  array('F','F'),
            'xcache'=>  array('xcache_get','xcache_set'),
            'apc'   =>  array('apc_fetch','apc_store'),
        );
        $queue      =   isset($this->options['queue'])?$this->options['queue']:'file';
        $fun        =   isset($_handler[$queue])?$_handler[$queue]:$_handler['file'];
        $queue_name =   isset($this->options['queue_name'])?$this->options['queue_name']:'think_queue';
        $value      =   $fun[0]($queue_name);
        if(!$value) {
            $value  =   array();
        }
        // 进列
        if(false===array_search($key, $value))  array_push($value,$key);
        if(count($value) > $this->options['length']) {
            // 出列
            $key =  array_shift($value);
            // 删除缓存
            $this->rm($key);
//            if(DEBUG_MODE_ON){
//                //调试模式下，记录出列次数
//                N($queue_name.'_out_times',1);
//            }
        }
        return $fun[1]($queue_name,$value);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name) {
        return unlink($this->filename($name));
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        $path   =  $this->options['temp'];
        $files  =   scandir($path);
        if($files){
            foreach($files as $file){
                if ($file != '.' && $file != '..' && is_dir($path.$file) ){
                    array_map( 'unlink', glob( $path.$file.'/*.*' ) );
                }elseif(is_file($path.$file)){
                    unlink( $path . $file );
                }
            }
            return true;
        }
        return false;
    }
}