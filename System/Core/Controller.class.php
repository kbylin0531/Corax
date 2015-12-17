<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/15
 * Time: 18:48
 */
namespace System\Core;
use System\Corax;
use System\Util\SEK;
use System\Util\UDK;

defined('BASE_PATH') or die('No Permission!');
/**
 * Class Controller 控制器类
 * @package System\Core
 */
class Controller{

    /**
     * 分配给模板的变量集合
     * @var array
     */
    protected $_tVars = array();

    /**
     * 模板引擎实例
     * @var View
     */
    protected $_view = null;

    /**
     * 当前控制器的默认上下文环境
     * @var array
     */
    protected $context = [
        'm' => null,//当前访问的模块
        'c' => null,//当前访问的控制器名称
        't' => null,//默认的模板主题
    ];

    /**
     * 模块目录
     * @var string
     */
    protected $module_path = null;

    /**
     * 模板引擎驱动
     *  跳过模板类直接调用模板引擎驱动
     * @var \Smarty
     */
    protected static $template_engine = null;


    /**
     * 初始化控制器
     * @param array $context 设置默认的自定义上下文环境
     * @throws \Exception
     */
    public function __construct($context = null){
        $matches = null;
        //使用三个'\'才能转义'\' ?

        if(preg_match('/^Application\\\(.*)\\\Controller\\\(.*)Controller$/',get_called_class(),$matches)){
            $this->context['m'] = str_replace('\\','/',$matches[1]);
            $this->context['c'] = $matches[2];
//            Util::dump($matches);
        }else{
            throw new \Exception('Class "'.get_called_class().'" can not fetch modules and controller!');
        }
        null === $context or $this->context = array_merge($this->context,$context);
        $this->module_path = BASE_PATH.'Application/'.$this->context['m'].'/';
    }


    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @param int $json_option 传递给json_encode的option参数
     * @return void
     * @throws \Exception
     */
    protected function ajax($data,$type='JSON',$json_option=0) {
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data,$json_option));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(SEK::encodeHtml($data));
            default:
                throw new \Exception('Unknown rReturn content type');
        }
    }

    /**
     * ajax返回成功的信息
     * @param $message
     * @throws \Exception
     */
    protected function ajaxSuccess($message){
        $this->ajax(array(
            'type'      => 'success',
            'message'   => $message
        ));
    }

    /**
     * ajax返回失败的信息
     * @param $message
     * @throws \Exception
     */
    protected function ajaxFaiure($message){
        $this->ajax(array(
            'type'      => 'failure',
            'message'   => $message
        ));
    }

    /**
     * 跳转到成功显示页面
     * @param string $message 提示信息
     * @param int $waittime 等待时间
     * @param string $title 显示标题
     * @throws \Exception
     */
    public function success($message,$waittime=1,$title='success'){
        self::jump($message,$title,true,1,$waittime);
    }

    /**
     * 跳转到错误信息显示页面
     * @param string $message 提示信息
     * @param int $waittime 等待时间
     * @param string $title 显示标题
     * @throws \Exception
     */
    public function error($message,$waittime=3,$title='error'){
        self::jump($message,$title,false,1,$waittime);
    }

    /**
     * 页面跳转
     * @param string $compo   形式如'Cms/install/third' 的action定位
     * @param array $params   URL参数
     * @param int $time       等待时间
     * @param string $message 跳转等待提示语
     * @return void
     */
    public function redirect($compo,array $params=array(),$time=0,$message=''){
        SEK::redirect(SEK::url($compo,$params),$time,$message);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param string $title 跳转也main标题
     * @param bool $status 页面状态
     * @param int $jumpto 页面计时结束地址
     * @param int $wait 页面等待时间
     * @return void
     * @throws \Exception
     */
    protected static function jump($message,$title='跳转',$status=true,$jumpto=-1,$wait=1) {
        //保证输出不受静态缓存影响
        header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );
        $vars = array();
        $vars['wait'] = $wait;
        $vars['title'] = $title;
        $vars['message'] = $message;
        $vars['status'] = $status?1:0;
        switch($jumpto){
            case 1:
                $vars['jumpurl'] = 'javascript:history.back(-1);';
                break;
            case -1:
                $vars['jumpurl'] = 'javascript:history.back(-1);';
                break;
            case 0://提示完毕后自动关闭窗口
            default:
                $vars['jumpurl'] = 'javascript:window.close();';
        }
        Corax::loadTemplate('jump',$vars);
    }

    /**
     * 设置默认的模板主题
     * @param $tname
     */
    public function theme($tname){
        $this->context['t'] = $tname;
    }

    /**
     * 分配模板变量
     * 全部格式转换成：
     * $tpl_var =>  array($value,$nocache=false)
     * @param array|string $tpl_var 变量名称 或者 "名称/变量值"键值对数组
     * @param mixed $value 变量值
     * @param bool $nocache
     * @return $this 可以链式调用
     */
    public function assign($tpl_var,$value=null,$nocache=false){
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $_key => $_val) {
                if ($_key != '') {
                    $this->_tVars[$_key] = array($_val,$nocache);
                }
            }
        } else {
            if ($tpl_var != '') {
                $this->_tVars[$tpl_var] = array($value,$nocache);
            }
        }
    }

    /**
     * 显示模板
     * @param string $template   the resource handle of the template file or template object
     * @param mixed  $cache_id   cache id to be used with this template
     * @param mixed  $compile_id compile id to be used with this template
     * @param object $parent     next higher level of Smarty variables
     */
    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null){
        //未设置时使用调用display的函数名称
        if(!$template){//如果未设置参数一
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,2);
//            Util::dump($this->context['a']);exit;
            $this->context['a'] = $trace[1]['function'];
        }
        Corax::status('display_c_begin');
        if(null === $this->_view) $this->initView();
        foreach($this->_tVars as $key => $value){
            $this->_view->assign($key,$value[0],$value[1]);
        }
        Corax::status('display_cc_begin');
        $this->_view->display($template,$cache_id,$compile_id,$parent);
    }

    /**
     * 初始化模板引擎
     * @return void
     */
    protected function initView(){
        $this->_view = new View($this->context);
        self::$template_engine = &View::$tpl_engine;
    }


//    public function __call($name,$params){
//        if(null === $this->_view) $this->initView();
//        return call_user_func_array(array($this->_view,$name),$params);
//    }

}