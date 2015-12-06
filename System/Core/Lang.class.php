<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/14
 * Time: 12:16
 */
namespace System\Core;
use System\Exception\CoraxException;

/**
 * Class LangHelper 语言助手
 * @package System\Core
 */
class Lang{

    /**
     * 语言常量
     */
    const LANG_ZH_CN = 'zh-cn';
    const LANG_ZH_TW = 'zh-tw';
    const LANG_EN_US = 'en-us';
    /**
     * 语言包类型，默认为简体中文
     * @var string
     */
    private static $default_lang = self::LANG_ZH_CN;
    /**
     * 系统以外语言包的路径
     * @var string
     */
    private static $outer_path  = null;
    /**
     * 语言包缓存
     * @var array
     */
    private static $lang_cache = array();
    /**
     * 是否已经完成加载
     * @var bool
     */
    private static $_has_loaded = false;

    /**
     * 私有化构造
     */
    private function __construct(){}


    /**
     * 设置外部语言包的路径
     * @param $path
     * @return void
     * @throws CoraxException
     */
    public static function setOuterPath($path){
        if(!Storage::has($path)){
            throw new CoraxException($path);
        }
        self::$outer_path = $path;
    }

    /**
     * 设置语言包类型
     * @param $type
     */
    public function setLang($type){
        self::$default_lang = $type;
    }
    /**
     * 获取语言包数组
     * @param string $type null时获取默认
     * @return array
     */
    public static function getLang($type=null){
        if(!self::$_has_loaded){
            return self::loadLang($type);
        }
        return self::$lang_cache;
    }

    /**
     * 加载、获取语言包
     * @param string $type 语言包类型
     * @return array
     * @throws CoraxException
     */
    public function loadLang($type=null){
        isset($type) or $type = self::$default_lang;
        if(!isset(self::$lang_cache[$type])){
            //加载框架内置语言包
            $innerpath = SYSTEM_PATH."Lang/{$type}.lang.php";
            if(Storage::has($innerpath)){
                $innerLang = include_once $innerpath;
            }else{
                throw new CoraxException($innerpath);
            }

            //加载用户自定义语言包
            $outerLang = array();
            if(isset(self::$outer_path)){
                $outerpath = self::$outer_path."{$type}.lang.php";
                if(Storage::has($outerpath)){
                    $outerLang = include_once $outerpath;
                }else{
                    throw new CoraxException($outerpath);
                }
            }
            self::$_has_loaded = true;
            return self::$lang_cache = array_merge(self::$lang_cache,$innerLang,$outerLang);
        }
        return self::$lang_cache[$type];

    }
}