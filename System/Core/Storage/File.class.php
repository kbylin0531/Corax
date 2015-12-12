<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/28
 * Time: 10:11
 */
namespace System\Core\Storage;
use System\Core\Storage;
use System\Exception\CoraxException;

/**
 * Class Common 文件系统驱动类基类
 * @package System\Core\Storage
 */
class File extends StorageDriver {
    /**
     * 文件名编码输入输出转写
     * （PHP的中文编码是GB2312）
     * PHP代码中文件名包括中文时需要设置参数二为true来转换成PHP可以识别的GB2312编码
     * PHP读取本地文件输出到屏幕上时需要设置参数二位false来转换成UTF-8编码
     * @param string $str 需要转换的文件名
     * @param bool $to_system 是否转成系统支持的编码格式
     * @return string 转换后的字符串
     */
    private function transliteration(&$str,$to_system=true){
        return $to_system ? iconv('UTF-8','GB2312//IGNORE',$str) : iconv('GB2312','UTF-8//IGNORE',$str);
    }

    /**
     * 获取文件内容
     * 注意：
     *  页面是utf-8，file_get_contents的页面是gb2312，输出时中文乱码
     * @param string $filepath 文件路径,PHP源码中格式是UTF-8，需要转成GB2312才能使用
     * @param string|array $file_encoding 文件内容实际编码,可以是数组集合或者是编码以逗号分开的字符串
     * @param string $output_encode 文件内容输出编码
     * @return string 返回文件时间内容
     * @throws CoraxException
     */
    public function read($filepath,$file_encoding='UTF-8',$output_encode='UTF-8'){
        $content = file_get_contents($this->transliteration($filepath));
        if(false === $content){
            throw new CoraxException($filepath);
        }elseif($file_encoding === $output_encode){
            return $content;
        }else{
            if(is_string($file_encoding) && false === strpos($file_encoding,',')){
                return iconv($file_encoding,$output_encode,$content);
            }
            return mb_convert_encoding($content,$output_encode,$file_encoding);
        }
    }

    /**
     * 将指定内容写入到文件中
     * @param string $filepath 文件路径
     * @param string $content 要写入的文件内容(一定是UTF-8编码)
     * @param string $write_encode 写入文件时的编码
     * @return int 返回写入的字节数目,失败时抛出异常
     * @throws CoraxException
     */
    public function write($filepath,$content,$write_encode='UTF-8'){
        $dir      =  dirname($filepath);
        if(!$this->has($dir)) $this->makeFolder($dir);//文件不存在则创建
        if($write_encode !== 'UTF-8'){//非UTF-8时转换编码
            $content = iconv('UTF-8',$write_encode,$content);
        }
        $rst = file_put_contents($this->transliteration($filepath),$content);
        if(false === $rst){
            throw new CoraxException($filepath,$content);
        }
        return $rst;
    }

    /**
     * 将指定内容追加到文件中
     * @param string $filepath 文件路径
     * @param string $content 要写入的文件内容
     * @param string $write_encode 写入文件时的编码
     * @return int 返回写入的字节数目
     * @throws CoraxException
     */
    public function append($filepath,$content,$write_encode='UTF-8'){
//        SEK::dump($filepath,$content,$write_encode);exit;
        if(!$this->has($filepath)){
            return $this->write($filepath,$content,$write_encode);
        }
        $temp = $this->transliteration($filepath);
        if(false === is_writable($temp)){
            throw new CoraxException($filepath);
        }
        $handler = fopen($temp,'a+');//追加方式，如果文件不存在则无法创建
        if($write_encode !== 'UTF-8'){
            $content = iconv('UTF-8',$write_encode,$content);
        }
        $rst = fwrite($handler,$content);
        if(false === fclose($handler)) throw new CoraxException($filepath,$content);
        return $rst;
    }
    /**
     * 确定文件或者目录是否存在
     * 相当于 is_file() or is_dir()
     * @param string $filepath 文件路径
     * @return bool
     */
    public function has($filepath){
        $filepath = $this->transliteration($filepath);
        return file_exists($filepath);
    }

    /**
     * 设定文件的访问和修改时间
     * @param string $filename 文件路径
     * @param int $time
     * @param int $atime
     * @return bool
     */
    public function touch($filename, $time = null, $atime = null){
        $filename = $this->transliteration($filename);
        return touch($filename, $time,$atime);
    }

    /**
     * 删除文件
     * @param string $filepath
     * @return bool
     */
    public function unlink($filepath){
        $filepath = $this->transliteration($filepath);
        return is_file($filepath)?unlink($filepath):rmdir($filepath);
    }

    /**
     * 读取文件信息
     * 可以使用stat获取信息
     * @param string $filepath  文件路径
     * @param string $type  文件信息类型，为null时获取全部信息
     * @return mixed
     * @throws CoraxException
     */
    public function info($filepath,$type=null){
        if(self::has($filepath)){
            $filepath = $this->transliteration($filepath);
            return isset($type)?call_user_func($type,$filepath):array(
                Storage::FILEINFO_LAST_ACCESS_TIME => call_user_func(Storage::FILEINFO_LAST_ACCESS_TIME,$filepath),
                Storage::FILEINFO_LAST_MODIFIED_TIME => call_user_func(Storage::FILEINFO_LAST_MODIFIED_TIME,$filepath),
                Storage::FILEINFO_PERMISSION => call_user_func(Storage::FILEINFO_PERMISSION,$filepath),
                Storage::FILEINFO_SIZE => call_user_func(Storage::FILEINFO_SIZE,$filepath),
                Storage::FILEINFO_TYPE => call_user_func(Storage::FILEINFO_TYPE,$filepath),
            );
        }else{
            throw new CoraxException($filepath);
        }
    }

    /**
     * 读取文件夹内容，并返回一个数组(不包含'.'和'..')
     * array(
     *      //文件内容  => 文件内容
     *      'filename' => 'file full path',
     * );
     * @param string $path 目录
     * @param bool $clear 是否清除之前的配置
     * @return array
     * @throws \Exception
     */
    public function readFolder($path,$clear=true){
        static $_file = array();
        if($clear){
            $_file = array();
            $path = $this->transliteration($path);//不能多次转换，iconv函数不能自动识别自负编码
        }
        if (is_dir($path)) {
            $handler = opendir($path);
            while (($filename = readdir( $handler )) !== false) {//未读到最后一个文件   继续读
                if ($filename !== '.' && $filename !== '..' ) {//文件除去 .和..
                    $fullpath = $path . '/' . $filename;
                    if(is_file($fullpath)) {
                        $filename = $this->transliteration($filename,false);
                        $fullpath = $this->transliteration($fullpath,false);
                        $_file[$filename] = str_replace('\\','/',$fullpath);
                    }elseif(is_dir($fullpath)) {
                        $this->readFolder($fullpath,false);//递归,不清空
                    }
                }
            }
            closedir($handler);//关闭目录指针
        }else{
            throw new \Exception("Path '{$path}' is not a dirent!");
        }
        return $_file;
    }

    /**
     * 删除文件夹
     * @param string $dirpath 文件夹名路径
     * @param bool $recursion 是否递归删除
     * @return bool
     */
    public function removeFolder($dirpath,$recursion=false){
        if(!$this->has($dirpath)) return false;
        //扫描目录
        $dh = opendir($this->transliteration($dirpath));
        while ($file = readdir($dh)) {
            if($file !== '.' && $file !== '..') {
                if(!$recursion) {//存在其他文件或者目录,非true时循环删除
                    closedir($dh);
                    return false;
                }
                $path = str_replace('\\','/',"{$dirpath}/{$file}");
//                SEK::dump($path);exit;
                if(false === (is_dir($this->transliteration($path))?$this->removeFolder($path,true):$this->unlink($path))){
                    return false;//***全等运算符优先级高于三目
                }
            }
        }
        closedir($dh);
        return $this->unlink($dirpath);
    }
    /**
     * 创建文件夹
     * 如果文件夹已经存在，则修改权限
     * @param string $dirpath 文件夹路径
     * @param int $auth 文件权限，八进制表示
     * @return bool
     */
    public function makeFolder($dirpath,$auth = 0755){
        $dirpath = $this->transliteration($dirpath);
        if(is_dir($dirpath)){
            return chmod($dirpath,$auth);
        }else{
            return mkdir($dirpath,$auth,true);
        }
    }
}