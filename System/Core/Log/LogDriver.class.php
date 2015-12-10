<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/10
 * Time: 15:05
 */
namespace System\Core\Log;
use System\Core\Log;

abstract class LogDriver {

    /**
     * 日志频度
     * @var int
     */
    protected $log_rate = null;

    public function __construct($rate=null){
        $this->log_rate = (null === $rate)?LOG_RATE:$rate;
    }


    /**
     * 获取日期
     * 短日期格式如："1992-05-31"
     * 长日期格式如："2038-01-19 11:14:07"(date('Y-m-d H:i:s',PHP_INT_MAX))
     * @param bool|false $refresh
     * @return array 日期各个部分数组
     */
    protected function getDate($refresh=false){
        static $date = null;
        if($refresh or !isset($date)){
            $date = [];
            //完整时间
            $date[0] = date('Y-m-d H:i:s:u');//精确到毫秒
            //年月日 文件夹名称,''表示创建文件夹
            $date[1] = $this->log_rate?'':substr($date[0],0,10);
            //时 文件名称,按日频度计算则显示年月入，否则显示小时
            $date[2] = $this->log_rate?substr($date[0],0,10):substr($date[0],11,2);
            //时分秒毫秒
            $date[3] = substr($date[0],11);
        }
        return $date;
    }
    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string|array $content 日志内容
     * @param string $level 日志级别
     * @return string 写入内容返回
     */
    abstract public function write($content,$level=Log::LOG_LEVEL_DEBUG);
    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $ymd  日志文件路径或日志文件名（日志文件生成日期,格式如YYYY-mm-dd）
     * @param null|string $level 日志级别
     * @return string
     */
    abstract public function read($ymd,$level=Log::LOG_LEVEL_DEBUG);

}