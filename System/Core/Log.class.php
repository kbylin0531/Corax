<?php
/**
 * Email:linzhv@qq.com
 * User: Lin
 * Date: 2015/8/24
 * Time: 16:51
 */
namespace System\Core;
use System\Corax;
use System\Exception\CoraxException;

defined('BASE_PATH') or die('No Permission!');

/**
 * Class Log 日志管理类
 * @package System\Core
 */
class Log{

    /**
     * 日志驱动类型
     */
    const LOGTYPE_FILE = 'File';
    const LOGTYPE_DATABASE = 'Database';
    const LOGTYPE_MEMCACHE = 'Memcache';
    const LOGTYPE_SAE = 'Sae';

    /**
     * 日志级别为记录错误
     */
    const LOG_LEVEL_DEBUG = 'Debug';
    /**
     * 记录日常操作的数据信息，以便数据丢失后寻回
     */
    const LOG_LEVEL_TRACE = 'Trace';

    /**
     * 日志频率
     * LOGRATE_DAY  每天一个文件的日志频率
     * LOGRATE_HOUR 每小时一个文件的日志频率，适用于较频繁的访问
     */
    const LOGRATE_HOUR = 0;
    const LOGRATE_DAY = 1;


    /**
     * @var Log\LogDriver;
     */
    private static $_driver = null;

    /**
     * 日志频率
     * @var string
     */
    private static $log_rate = null;

    /**
     * 初始化日志类
     * @param string $type 日志驱动类型,默认为文件类型
     * @param int $rate    日志记录频率，默认按天计算
     * @throws CoraxException 驱动类不存在时抛出异常
     */
    public static function init($type = self::LOGTYPE_FILE,$rate = self::LOGRATE_DAY){
        Corax::status('log_init_begin');
        self::$log_rate = $rate;
        if(null === self::$_driver){
            $clsnm = "System\\Core\\Log\\$type";
            if(!class_exists($clsnm)){
                throw new CoraxException("Log driver '{$clsnm}' not exist!");
            }
            self::$_driver = new $clsnm();
        }
        Corax::status('log_init_end');
    }

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string|array $content 日志内容
     * @param string $level 日志级别
     * @return string 写入内容返回
     * @Exception FileWriteFailedException
     */
    public static function write($content,$level=self::LOG_LEVEL_DEBUG){
        self::$_driver or self::init();
        return self::$_driver->write($content,$level);
    }

    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $ymd 日志文件路径或日志文件名（日志文件生成日期,格式如YYYY-mm-dd）
     * @param null|string $level 日志级别
     * @return string|array 如果按小时写入，则返回数组
     */
    public static function read($ymd, $level=self::LOG_LEVEL_DEBUG){
        self::$_driver or self::init();
        return self::$_driver->read($ymd,$level);
    }

    /**
     * 写入DEBUG信息到日志中
     * @param ...
     * @return string
     * @throws CoraxException
     */
    public static function debug(){
        self::$_driver or self::init();$content = '';
        if(DEBUG_MODE_ON){
            $params = func_get_args();
            foreach($params as $val){
                $content .= var_export($val,true);
            }
            self::$_driver->write($content,self::LOG_LEVEL_DEBUG);
        }
        return $content;
    }

    /**
     * 写入跟踪信息,信息参数可变长
     * @param ...
     * @return string
     */
    public static function trace(){
        self::$_driver or self::init();
        $content = '';
        if(DEBUG_MODE_ON){
            $params = func_get_args();
            foreach($params as $val){
                $content .= '█TRACE█'.var_export($val,true);
            }
            self::$_driver->write($content,self::LOG_LEVEL_TRACE);
        }
        return $content;
    }

}