<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/25
 * Time: 9:08
 */
namespace System\Core;
use System\Corax;

defined('BASE_PATH') or die('No Permission!');

/**
 * Class Storage 持久化存储类
 * 实际文件可能写在伺服器的文件中，也可能存放到数据库文件中，或者远程文件服务器中
 * @package System\Core
 */
class Storage {
    /**
     * 文件信息获取类型
     */
    const FILEINFO_LAST_ACCESS_TIME = 'fileatime';
    const FILEINFO_LAST_MODIFIED_TIME = 'filemtime';
    const FILEINFO_PERMISSION = 'fileperms';
    const FILEINFO_SIZE = 'filesize';//文件大小
    const FILEINFO_TYPE = 'filetype';//可能的值有 fifo，char，dir，block，link，file 和 unknown。

    const STORAGEMODE_FILE = 'File';
    const STORAGEMODE_SAE = 'Sae';
    const STORAGEMODE_KVDB = 'Kvdb';
    const STORAGEMODE_MEMCACHE = 'Memcache';

    /**
     * 存储类驱动实例
     * 云服务器环境下普通文件操作函数可能面临失效的情况
     * @var Storage\StorageInterface
     */
    private static $driver = null;

    /**
     * 私有化构造函数
     */
    private function __construct(){}

    /**
     * 文件名编码输入输出转写
     * （PHP的中文编码是GB2312）
     * PHP代码中文件名包括中文时需要设置参数二为true来转换成PHP可以识别的GB2312编码
     * PHP读取本地文件输出到屏幕上时需要设置参数二位false来转换成UTF-8编码
     * @param string $str 需要转换的文件名
     * @param bool $to_system 是否转成系统支持的编码格式
     * @return string 转换后的字符串
     */
    public static function transliteration(&$str,$to_system=true){
        return $to_system ? iconv('UTF-8','GB2312//IGNORE',$str) : iconv('GB2312','UTF-8//IGNORE',$str);
    }

    /**
     * 根据存储模式初始化驱动类
     * @param string $mode
     */
    public static function init($mode = null){
        Corax::status('storage_init_begin');
        //获取运行环境
        if(null === $mode and RUNTIME_ENVIRONMENT === self::STORAGEMODE_SAE){
            $mode = self::STORAGEMODE_SAE;
        }else{
            $mode = self::STORAGEMODE_FILE;
        }
        //实例化驱动类
        $driverName = "System\\Core\\Storage\\{$mode}";
        self::$driver = new $driverName();
        Corax::status('storage_init_done');
    }

    /**
     * 获取文件内容
     * @param string $filepath 文件路径
     * @param string $file_encoding 文件内容实际编码
     * @param string $output_encode 文件内容输出编码
     * @return string|false 文件不存在时返回false
     */
    public static function read($filepath,$file_encoding='UTF-8',$output_encode='UTF-8'){
        return self::$driver->read($filepath,$file_encoding,$output_encode);
    }

    /**
     * 文件写入
     * @param string $filepath 文件名
     * @param string $content 文件内容
     * @param string $write_encode 文件写入编码
     * @return int 返回写入的字节数目,失败时抛出异常
     */
    public static function write($filepath,$content,$write_encode='UTF-8'){
        return self::$driver->write($filepath,$content,$write_encode);
    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  追加的文件内容
     * @param string $write_encode 文件写入编码
     * @return string 返回写入内容
     */
    public static function append($filename,$content,$write_encode='UTF-8'){
        return self::$driver->append($filename,$content,$write_encode);
    }

    /**
     * 文件是否存在
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public static function has($filename){
        return self::$driver->has($filename);
    }


    /**
     * 文件删除
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public static function unlink($filename){
        return self::$driver->unlink($filename);
    }

    /**
     * 读取文件信息
     * 可以使用stat获取信息
     * @access public
     * @param string $filename  文件名
     * @param null $type
     * @return array|mixed
     */
    public static function filemtime($filename,$type=null){
        return self::$driver->filemtime($filename,$type);
    }

    /**
     * 获取文件大小
     * @param string $filename 文件路径信息
     * @return mixed
     */
    public static function filesize($filename){
        return self::$driver->filesize($filename);
    }

    /**
     * 删除文件夹
     * @param string $dir 文件夹目录
     * @param bool $recursion 是否递归删除
     * @return bool true成功删除，false删除失败
     */
    public static function removeFolder($dir,$recursion=false) {
        return self::$driver->removeFolder($dir,$recursion);
    }

    /**
     * 创建文件夹
     * 如果文件夹已经存在，则修改权限
     * @param string $fullpath 文件夹路径
     * @param int $auth 文件权限，八进制表示
     * @return bool
     */
    public static function makeFolder($fullpath,$auth = 0755){
        return self::$driver->makeFolder($fullpath,$auth);
    }
    /**
     * 读取文件夹内容，并返回一个数组(不包含'.'和'..')
     * array(
     *      //文件内容  => 文件内容
     *      'filename' => 'file full path',
     * );
     * @param string $dir 文件夹路径
     * @return array
     */
    public static function readFolder($dir){
        return self::$driver->readFolder($dir);
    }

}