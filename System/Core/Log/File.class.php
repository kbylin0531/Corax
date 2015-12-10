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
use System\Util\SEK;

class File extends LogDriver{

    /**
     * 写入日志信息
     * 如果日志文件已经存在，则追加到文件末尾
     * @param string|array $content 日志内容
     * @param string $level 日志级别
     * @return string 写入内容返回
     */
    public function write($content,$level=Log::LOG_LEVEL_DEBUG){
        $sdate = $this->getDate();
        $path = BASE_PATH."Runtime/Log/{$level}/{$sdate[1]}/{$sdate[2]}.log";
        //写入文件内容
        $message = '';
        if(is_array($content)){//数组写入
            foreach($content as $key=>$val){
                $message .= is_numeric($key)?"{$val}\n":"||--{$key}--||\n{$val}\n";
            }
        }else{
            $message = $content;
        }
        $remoteIp = SEK::getClientIP();

        $level = Log::LOG_LEVEL_DEBUG === $level?'█DEBUG█':'█TRACE█';

        return Storage::append($path,"-------------------------------------------------------------------------------------\r\n{$level} {$sdate[0]}  IP:{$remoteIp}  URL:{$_SERVER['REQUEST_URI']}\r\n-------------------------------------------------------------------------------------\r\n{$message}\r\n\r\n\r\n\r\n");
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

}