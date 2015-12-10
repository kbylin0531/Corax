<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/6
 * Time: 10:00
 */
namespace System\Core\LogDriver;
use System\Core\Log;
use System\Core\Log\LogDriver;
use System\Util\SEK;

/**
 * Class SaeDriver 使用SAE的日志系统进行日志记录
 * @package System\Core\LogDriver
 */
class Sae extends LogDriver{



    public function write($content,$level=Log::LOG_LEVEL_DEBUG){
        static $is_debug=null;

        $sdate = $this->getDate();
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

        $logstr = "-------------------------------------------------------------------------------------\r\n{$level} {$sdate[0]}  IP:{$remoteIp}  URL:{$_SERVER['REQUEST_URI']}\r\n-------------------------------------------------------------------------------------\r\n{$message}\r\n\r\n\r\n\r\n";
        if(null === $is_debug){
            preg_replace('@(\w+)\=([^;]*)@e', '$appSettings[\'\\1\']="\\2";', $_SERVER['HTTP_APPCOOKIE']);
            $is_debug = in_array($_SERVER['HTTP_APPVERSION'], explode(',', $appSettings['debug'])) ? true : false;
        }
        if($is_debug)
            sae_set_display_errors(false);//记录日志不将日志打印出来
        sae_debug($logstr);
        if($is_debug)
            sae_set_display_errors(true);

    }


    public function read($ymd,$level=Log::LOG_LEVEL_DEBUG){
        return '';
    }

}