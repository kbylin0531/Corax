<?php
/**
 * Email:784855684@qq.com
 * User: Lin
 * Date: 2015/9/12
 * Time: 12:32
 */
namespace System\Utils;
use System\Exception\CoraxException;

/**
 * Class FileUtil 文件操作工具
 * @package System\Utils
 */
class FileUtil {
    /**
     * 读取目录下的所有文件
     * @param string $path 目录的路径
     * @param bool|true $clear 清空原来的记录
     * @return array 文件名和对应的路径
     * @throws CoraxException
     */
    public static function readDirFiles($path,$clear=true){
        static $_file = array();
        $clear and $_file = array();
        if (is_dir($path)) {
            $handler = opendir ($path);
            while (($filename = readdir( $handler )) !== false) {//未读到最后一个文件   继续读
                if ($filename !== '.' && $filename !== '..' ) {//文件除去 .和..
                    if(is_file($path . '/' . $filename)) {
                        $_file[$filename] = $path . '/' . $filename;
                    }elseif(is_dir($path . '/' . $filename)) {
                        self::readDirFiles($path . '/' . $filename,false);//递归
                    }
                }
            }
            closedir($handler);//关闭目录指针
        }else{
            throw new CoraxException("Path '{$path}' is not a dirent!");
        }
        return $_file;
    }
}