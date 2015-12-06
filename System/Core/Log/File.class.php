<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/6
 * Time: 9:59
 */
namespace System\Core\Log;
use System\Core\Log;
use System\Core\Storage;

class File {

    /**
     * 日志缓存
     * @var array
     */
    protected $log_cache = array();

    /**
     * @var null
     */
    protected $time = null;

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string|array $content 日志内容
     * @param string $level 日志级别
     * @return string 写入内容返回
     */
    public function write($content,$level=Log::LOG_LEVEL_DEBUG){
        $sdate = Log::getDate();
        $path = BASE_PATH."Runtime/Log/{$level}/{$sdate[0]}/{$sdate[1]}.log";
        //写入文件内容
        $message = '';
        if(is_array($content)){//数组写入
            foreach($content as $key=>$val){
                $message .= is_numeric($key)?"{$val}\n":"||--{$key}--||\n{$val}\n";
            }
        }else{
            $message = $content;
        }
        $this->log_cache[] = $message;
        return Storage::append($path,
            "{$sdate[2]}\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n
            {$message}
            \n=====================================================================================\n\n\n\n\n\n");
    }

    /**
     * 读取日志文件内容
     * 如果设置了参数二，则参数一将被认定为文件名
     * @param string $ymd  日志文件路径或日志文件名（日志文件生成日期,格式如YYYY-mm-dd）
     * @param null|string $level 日志级别
     * @return string
     */
    public function read($ymd,$level=Log::LOG_LEVEL_DEBUG){
        $path = BASE_PATH."Runtime/Log/{$level}/{$ymd}.log";
        return Storage::read($path);
    }

    /**
     * 返回本次脚本执行的日志缓存数组
     * @return array
     */
    public function getCache(){
        return $this->log_cache;
    }

}