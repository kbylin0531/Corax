<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/24
 * Time: 16:51
 */
namespace System\Core;
use System\Corax;
use System\Exception\ClassNotFoundException;
use System\Exception\CoraxException;
use System\Util\SEK;

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
     * 日志记录的时间
     * @var array
     */
    protected static $time = null;

    /**
     * @var Log\File;
     */
    private static $_driver = null;

    /**
     * 日志类型
     * @var string
     */
    private static $log_type = null;
    /**
     * 日志频率
     * @var string
     */
    private static $log_rate = null;

    private static $hasInited = false;

    /**
     * 初始化日志类
     * @param string $type 日志驱动类型
     * @param int $rate    日志记录频率
     * @throws CoraxException
     */
    public static function init($type = self::LOGTYPE_FILE,$rate = self::LOGRATE_DAY){
        Corax::status('log_init_begin');
        self::$log_type = $type;
        self::$log_rate = $rate;
        if(!isset(self::$_driver)) {
            $clsnm = "System\\Core\\Log\\$type";
            if(!class_exists($clsnm)){
                throw new CoraxException($clsnm);
            }
            self::$_driver = new $clsnm();
        }
        self::$hasInited = true;
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
        self::$hasInited or self::init();
//        Util::dump($content,$level);exit;
        return self::$_driver->write($content,$level);
    }

    /**
     * 写入跟踪信息,信息参数可变长
     * @param ...
     * @return string
     */
    public static function trace(){
        self::$hasInited or self::init();
        $content = '';
        if(DEBUG_MODE_ON){
            $params = func_get_args();
            foreach($params as $val){
                $content .= var_export($val,true).' █ ';
            }
            self::$_driver->write($content,self::LOG_LEVEL_TRACE);
        }
        return $content;
    }

    /**
     * 返回此次运行保留的日志信息
     * @return array
     */
    public static function getCache(){
        self::$hasInited or self::init();
        return self::$_driver->getCache();
    }

    /**
     * 获取日期
     * 短日期格式如："1992-05-31"
     * 长日期格式如："2038-01-19 11:14:07"(date('Y-m-d H:i:s',PHP_INT_MAX))
     * @param bool|false $refresh
     * @return string 日期字符串
     */
    public static function getDate($refresh=false){
        static $date = null;
        if(!isset($date) or $refresh){
            $datestr = SEK::date();
            $date[0] = LOG_RATE?'':substr($datestr,0,10);//年月日 文件夹名称,''表示创建文件夹
            $date[1] = LOG_RATE?substr($datestr,0,10):substr($datestr,11,2);//时 文件名称,按日频度计算则显示年月入，否则显示小时
            $date[2] = substr($datestr,11);//时分秒 具体时间
        }
        return $date;
    }

    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $path 日志文件路径或日志文件名（日志文件生成日期,格式如YYYY-mm-dd）
     * @param null|string $level 日志级别
     * @return string
     */
    public static function read($path,$level=self::LOG_LEVEL_DEBUG){
        self::$hasInited or self::init();
        return self::$_driver->read($path,$level);
    }


}