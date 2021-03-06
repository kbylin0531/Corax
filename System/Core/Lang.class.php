<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/14
 * Time: 12:16
 */
namespace System\Core;
use System\Exception\CoraxException;
use System\Util\SEK;

/**
 * Class LangHelper 语言助手
 * 不依赖本地文件
 * @package System\Core
 */
class Lang{

    /**
     * 语言常量
     */
    const LANG_ZH_CN = 'zh-cn';
    const LANG_ZH_TW = 'zh-tw';
    const LANG_EN_US = 'en-us';

    private static $convention = [
        //外部语言包地址(相对于BASE_PATH而言)
        'LANG_OUTER_PATH'   => 'Lang/',
        'DEFAULT_LANG'      => self::LANG_ZH_CN,
    ];

    /**
     * 语言包缓存
     * @var array
     */
    private static $lang_cache = [];
    /**
     * 当前使用的语言包类型
     * @var string
     */
    private static $current_lang_type = null;

    private static $hasInited = false;

    /**
     * 私有化构造
     */
    private function __construct(){}

    public static function init(array $config = null){
        //惯例配置设置
        isset($config) or $config = Configer::load('lang');
        SEK::merge(self::$convention,$config);

        self::load();
        self::$hasInited = true;
    }

    /**
     * 陈述标识符对应的语言
     * @param string $identifier 陈述标识符
     * @param mixed $replace 当找不到标识符对应的内容时的替代，默认为null
     * @return mixed
     */
    public static function express($identifier,$replace=null){
        self::$hasInited or self::init();
        return isset(self::$lang_cache[self::$current_lang_type][$identifier])?
            self::$lang_cache[self::$current_lang_type][$identifier]:$replace;
    }

    /**
     * 加载、获取语言包
     * @param string $type 语言包类型
     * @return array 返回语言包数组
     * @throws CoraxException
     */
    public function load($type=null){
        self::$current_lang_type = isset($type)?$type:($type = self::$convention['DEFAULT_LANG']);//类型设置

        if(!isset(self::$lang_cache[$type])){
            //文件不存在时会报错！
            $innerLang = include_once SYSTEM_PATH."Lang/{$type}.php"; // 系统内置语言包
            $outerLang = include_once BASE_PATH.self::$convention['LANG_OUTER_PATH']."{$type}.php"; // 用户自定义语言包，会覆盖系统的

            self::$lang_cache[$type] = array_merge(self::$lang_cache,$innerLang,$outerLang);// 后面覆盖前面的
        }
    }
}