<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/9/12
 * Time: 9:33
 */
namespace System\Extension;
use System\Corax;
use System\Core\Storage;

/**
 * 用于创建PHP类文件轻量包
 * Class LiteBuilder 轻量级源码创建类
 * @package System\Extension
 */
class LiteBuilder {

    /**
     * 程序结束时调用
     * @param string $litefile 生成的lite文件名称
     * @param array $filelist 文件列表
     */
    public static function build($litefile = null,$filelist=null) {
        static $_cache = array();
        isset($litefile) or $litefile = RUNTIME_PATH.APP_NAME.'.lite.php';
        //获取所有的定义的常量
//        $constants = get_defined_constants(TRUE);
//        $content = self::buildArrayDefine($constants);
        // 读取编译列表文件
        isset($filelist) or $filelist = Corax::getClasses();
        $content = '';
        // 编译文件
        foreach ($filelist as $file){
            if(Storage::has($file)){
                if(!isset($_cache[$file])){
                    $content .= self::compile($file);
                    $_cache[$file] = true;
                }
            }
        }
        // 生成运行Lite文件
        Storage::write($litefile,'<?php '.$content);
//        Storage::write($litefile,self::stripWhitespace('<?php '.$content));
    }
    /**
     * 获取文件编译后的内容
     * @param string $filename 文件名
     * @return string
     */
    public static function compile($filename) {
        $content    =   php_strip_whitespace($filename);//删除PHP代码中的注释和空格
        $content    =   trim(substr($content, 5));//去除 '<?php'
        // 替换命名空间
        if(0===strpos($content,'namespace')){
            $content    =   preg_replace('/namespace\s(.*?);/','namespace \\1{',$content,1);
        }else{
            $content    =   'namespace {'.$content;
        }
        //去除  '? >'   也有可能不会带这个
        if ('?>' == substr($content, -2))
            $content    = substr($content, 0, -2);
        return $content.'}';
    }
    /**
     * 去除代码中的空白和注释
     * @param string $content 代码内容
     * @return string
     */
    public static function stripWhitespace($content) {
        $stripStr   = '';
        //分析php源码
        $tokens     = token_get_all($content);
        $last_space = false;
        for ($i = 0, $j = count($tokens); $i < $j; $i++) {
            if (is_string($tokens[$i])) {
                $last_space = false;
                $stripStr  .= $tokens[$i];
            } else {
                switch ($tokens[$i][0]) {
                    //过滤各种PHP注释
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        break;
                    //过滤空格
                    case T_WHITESPACE:
                        if (!$last_space) {
                            $stripStr  .= ' ';
                            $last_space = true;
                        }
                        break;
                    case T_START_HEREDOC:
                        $stripStr .= '<<<Mist ';
                        break;
                    case T_END_HEREDOC:
                        $stripStr .=  'Mist; ';
                        for($k = $i+1; $k < $j; $k++) {
                            if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                                $i = $k;
                                break;
                            } else if($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    default:
                        $last_space = false;
                        $stripStr  .= $tokens[$i][1];
                }
            }
        }
        return $stripStr;
    }
    /**
     * 根据数组生成常量定义
     * @param array $array 常量数组
     * @return string
     */
    public static function buildArrayDefine(array $array) {
        $content = ' ';
        foreach ($array as $key => $val) {
            $key = strtoupper($key);
            $content .= "defined('{$key}') or ";
            if (is_int($val) || is_float($val)) {
                $content .= "define('{$key}',{$val});";
            } elseif (is_bool($val)) {
                $val = ($val) ? 'true' : 'false';
                $content .= "define('{$key}',{$val});";
            } elseif (is_string($val)) {
                $val = addslashes($val);
                $content .= "define('{$key}','{$val}'');";
            }
            $content    .= ' ';
        }
        return $content;
    }
}