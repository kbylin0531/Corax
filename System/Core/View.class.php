<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/30
 * Time: 13:38
 */
namespace System\Core;
use System\Corax;
use System\Exception\FileNotFoundException;
use System\Util\SEK;
use System\Utils\LiteBuilder;

/**
 * Class View 视图类，控制模板引擎的行为(当前版本统一使用Smarty模板引擎)
 * @package System\Core
 */
class View{

    protected $_config = array(
        'left_delimiter'    => '{',
        'right_delimiter'   => '}',
    );

    /**
     * 模板文件存放目录
     * @var string
     */
    protected $_tpl_dir = null;
    /**
     * 编译缓存目录，包括编译后的模板和静态缓存
     * @var string
     */
    protected $_tpl_cache_dir = null;

    /**
     * 模板文件编译后的存放目录
     * @var string
     */
    protected $_tpl_compile_dir = null;

    /**
     * 静态缓存的输出文件存放目录
     * @var string
     */
    protected $_tpl_static_dir = null;
    /**
     * 一个控制器对应一个视图
     * @var array
     */
    protected static $_context = null;

    /**
     * 模板引擎驱动
     * @var \Smarty
     */
    public static $tpl_engine = null;

    protected static $_smarty_lite_file = null;

    protected static $registed = false;

    protected $_tVars = array();

    /**
     * 构造函数
     * @param array $context 控制器闯入的伪上下文环境
     * @throws \Exception
     */
    public function __construct($context){
        self::$_context = $context;
        defined('SMARTY_DIR') or define('SMARTY_DIR',BASE_PATH.'System/Projects/Smarty/libs/');

        if(!isset(self::$tpl_engine)){
            require_once SMARTY_DIR.'Smarty.class.php';
            static::$tpl_engine = new \Smarty();
            static::$tpl_engine->left_delimiter  = $this->_config['left_delimiter'];
            static::$tpl_engine->right_delimiter = $this->_config['right_delimiter'];
            null === self::$_smarty_lite_file and self::$_smarty_lite_file =  RUNTIME_PATH.'Smarty.lite.php';
        }
        self::$registed or $this->registerTemplateFunctions();
    }

    /**
     * 批量注册模板函数
     * @return void
     */
    private function registerTemplateFunctions(){
        //注册U函数
        $this->registerPlugin('function','U',array($this,'U'));
        self::$registed = true;
    }

    /**
     * 创建模板URL
     * U参数规则：url参数表示modules/controller/action 其他参数则作为URL参数
     * @param array $params U函数的属性数组，属性名为变量名称，属性值为变量值
     * @return string
     */
    public function U($params){
        $url = $params['url'];
        unset($params['url']);
        if(isset($params['mode'])){
            $mode = $params['mode'];
            unset($params['mode']);
        }else{
            $mode = null;
        }
        return SEK::url($url,$params,$mode);
    }

    public function registerPlugin($type, $tag, $callback, $cacheable = true, $cache_attr = null){
        return static::$tpl_engine->registerPlugin($type, $tag, $callback, $cacheable , $cache_attr);
    }

    public function setTemplateDir($path){
//        Util::dump($path);
        return static::$tpl_engine->setTemplateDir($path);
    }

    public function setCompileDir($path){
        return static::$tpl_engine->setCompileDir($path);
    }
    public function setCacheDir($path){
        return static::$tpl_engine->setCacheDir($path);
    }
    /**
     * 保存控制器分配的变量
     * @param string $tpl_var
     * @param null $value
     * @param bool $nocache
     * @return $this
     */
    public function assign($tpl_var,$value=null,$nocache=false){
        return self::$tpl_engine->assign($tpl_var,$value,$nocache);
    }
    /**
     * 显示模板
     * @param string $template 全部模板引擎通用的
     * @param null $cache_id
     * @param null $compile_id
     * @param null $parent
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null){
//        Util::dump($template,$this->_context,TEMPLATE_ENGINE);exit;
        Corax::status('display_begin');

        $context = &self::$_context;
        if($template){
            $context = array_merge($context,self::parseTemplatePath($template));
        }
        //项目中省略模板文件后缀的情况
        $suffix = '.'.TEMPLATE_EXT;
        if(false === strpos($context['a'],$suffix)){
            $context['a'] .= $suffix;
        }
        //获取缓存目录
        $this->_tpl_cache_dir = RUNTIME_PATH."cache/{$context['m']}/{$context['c']}/";
        //确定是否存在该主题文件，不存在则使用默认的对应主题文件
        $this->_tpl_dir = APP_PATH."{$context['m']}/View/{$context['c']}/";
        if(isset($context['t']) and is_file($this->_tpl_dir."{$context['t']}/{$context['a']}")){
            $this->_tpl_dir .= "{$context['t']}/";
        }
        //判断模板文件是否存在（改为由模板引擎判断）
//        if(!is_file($this->_tpl_dir.$context['a'])){
//            throw new FileNotFoundException($this->_tpl_dir.$context['a']);
//        }

        //分配变量
        self::$tpl_engine->assign($this->_tVars);
        //设置模板目录(基础)
//        Util::dump($template,$this->_context,TEMPLATE_ENGINE);
        self::$tpl_engine->setTemplateDir($this->_tpl_dir);
        self::$tpl_engine->setCompileDir($this->_tpl_cache_dir.'compile/');
        self::$tpl_engine->setCacheDir($this->_tpl_cache_dir.'static/');
//        Util::dump($this->_tpl_dir,$template);exit;
        Corax::status('display_gonna_to_begin');


        //显示模板文件
        self::$tpl_engine->display(self::$_context['a'],$cache_id,$compile_id,$parent);
        Corax::status('display_end');
    }

    /**
     * 解析资源文件地址
     * 地址解析格式：
     *  ModuleA/ModuleB@Controller/action:theme
     * 解析结果可以是资源地址，也可以是位置组成数组
     * @param string $templatepath
     * @param bool $get_url 返回的是位置字符串还是数组
     * @return array|string
     */
    public static function parseTemplatePath($templatepath,$get_url=false){
        $rst = array();
        if($templatepath){
            $tpos = strpos($templatepath,':');
            $pathlen = strlen($templatepath);
            //解析主题
            if(false !== $tpos){
                //存在主题
                $rst['t'] = substr($templatepath,$tpos+1,$pathlen-1);//末尾的pos需要-1-1
                $templatepath = substr($templatepath,0,$tpos);
            }
            $mcpos = strpos($templatepath,'@');
            if(false !== $mcpos){
                $rst['m'] = substr($templatepath,0,$mcpos);
                $templatepath = substr($templatepath,$mcpos+1);
            }
            $capos = strpos($templatepath,'/');
            if(false !== $capos){
                $rst['c'] = substr($templatepath,0,$capos);
                $rst['a'] = substr($templatepath,$capos+1);
            }else{
                $rst['a'] = $templatepath;
            }
        }
        if($get_url){
            $url = APP_PATH;
            $context = &self::$_context;
            $url .= isset($rst['m']) ? "{$rst['m']}/":"{$context['m']}/";
            $url .= isset($rst['c']) ? "View/{$rst['c']}/":"View/{$context['c']}/";
            if(isset($rst['t'])){
                $url .= "{$url}/{$rst['t']}/";
            }elseif(isset($context['t'])){
                $url .= "{$url}/{$context['t']}/";
            }
            $url .= (isset($rst['a']) ? $rst['a']:$context['a']).'.'.TEMPLATE_EXT;
            return $url;
        }
        return $rst;
    }

    /**
     * @throws \Exception
     */
    public function buildSmartyLite(){
        defined('SMARTY_DIR') or define('SMARTY_DIR',BASE_PATH.'System/Projects/Smarty/libs/');
//        $files = FileUtil::readDirFiles(SMARTY_DIR);//不推荐全部读取
        //smarty常用的文件
        $files = array(
            SMARTY_DIR.'Smarty.class.php',
//
//            SMARTY_PLUGIN_DIR.'smarty_config_source.php'                  ,
//            SMARTY_PLUGIN_DIR.'smarty_security.php'                       ,
//            SMARTY_PLUGIN_DIR.'smarty_cacheresource.php'                  ,
//            SMARTY_PLUGIN_DIR.'smarty_compiledresource.php'               ,
//            SMARTY_PLUGIN_DIR.'smarty_cacheresource_custom.php'           ,
//            SMARTY_PLUGIN_DIR.'smarty_cacheresource_keyvaluestore.php'    ,
//            SMARTY_PLUGIN_DIR.'smarty_resource.php'                       ,
//            SMARTY_PLUGIN_DIR.'smarty_resource_custom.php'                ,
//            SMARTY_PLUGIN_DIR.'smarty_resource_uncompiled.php'            ,
//            SMARTY_PLUGIN_DIR.'smarty_resource_recompiled.php'            ,
//            SMARTY_PLUGIN_DIR.'smarty_template_source.php'                ,
//            SMARTY_PLUGIN_DIR.'smarty_template_compiled.php'              ,
//            SMARTY_PLUGIN_DIR.'smarty_template_cached.php'                ,
//            SMARTY_PLUGIN_DIR.'smarty_template_config.php'                ,
//            SMARTY_PLUGIN_DIR.'smarty_data.php'                           ,
//            SMARTY_PLUGIN_DIR.'smarty_variable.php'                       ,
//            SMARTY_PLUGIN_DIR. 'smarty_undefined_variable.php'             ,
//            SMARTY_PLUGIN_DIR.'smartyexception.php'                       ,
//            SMARTY_PLUGIN_DIR.'smartycompilerexception.php'               ,
//            SMARTY_PLUGIN_DIR.'smarty_internal_data.php'                  ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_template.php'              ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_templatebase.php'          ,
//            SMARTY_PLUGIN_DIR.'smarty_internal_resource_file.php'         ,
//            SMARTY_PLUGIN_DIR.'smarty_internal_resource_extends.php'      ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_resource_eval.php'         ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_resource_string.php'       ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_resource_registered.php'   ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_extension_codeframe.php'   ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_extension_config.php'      ,
//            SMARTY_PLUGIN_DIR.'smarty_internal_filter_handler.php'        ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_function_call_handler.php' ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_cacheresource_file.php'    ,
//            SMARTY_PLUGIN_DIR. 'smarty_internal_write_file.php'    ,

//            SMARTY_DIR.'sysplugins/smarty_internal_templatebase.php',
//            SMARTY_DIR.'sysplugins/smarty_internal_data.php',
//            SMARTY_DIR.'sysplugins/smarty_internal_template.php',
//            SMARTY_DIR.'sysplugins/smarty_resource.php',
//            SMARTY_DIR.'sysplugins/smarty_template_compiled.php',
        );
//        Util::dump($files);exit;
        LiteBuilder::build(self::$_smarty_lite_file,$files);
    }



}