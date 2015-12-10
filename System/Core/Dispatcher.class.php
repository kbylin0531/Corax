<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/15
 * Time: 19:07
 */
namespace System\Core;
use System\Corax;
use System\Exception\CoraxException;
use System\Util\SEK;
defined('BASE_PATH') or die('No Permission!');
/**
 * Class Dispatcher 引导帮助类
 * @package System\Core
 * 根据URL解析的参数引导到对应的模块的控制器的操作下
 */
class Dispatcher{
    /**
     * 惯例配置
     * @var array
     */
    protected static $convention = [];
    /**
     * 表示本类是否已完成初始化
     * @var bool
     */
    protected static $hasInited = false;

    /**
     * 初始化调度器
     */
    public static function init(){
        //获取静态方法调用的类名称使用get_called_class,对象用get_class
        SEK::merge(self::$convention,Configer::load('dispatcher'),true);
        static::$hasInited = true;
    }

    /**
     * 执行控制器操作
     * @param null|string|array $modules
     * @param null|string $ctrler
     * @param null|string $action
     * @param null|array  $parameters
     * @throws CoraxException
     * @throws \Exception
     */
    public static function execute($modules,$ctrler,$action,array $parameters=array()){
        Corax::status('execute_begin');

        self::$hasInited or self::init();
        if(!isset($modules,$ctrler,$action)){
            throw new CoraxException($modules,$ctrler,$action,$parameters);
        }
        //安全性检查
        if(is_array($modules)){
            $temp = '';
            foreach($modules as $val){
                if(self::checkAllValid($val)){
                    $temp .= "$val\\";
                }
            }
            $modules = trim($temp,'\\');
        }else{
            if(!self::checkAllValid($modules)){
                throw new CoraxException($modules);
            }
        }

        Corax::status('execute_instance_build_init_begin');
        $className = "Application\\{$modules}\\Controller\\{$ctrler}Controller";
//        if(!class_exists($className)){//此步骤操作比较耗时
//            //可以检查模块的空控制器函数 模块-全局
////            $moduleDir = str_replace('\\','/',BASE_PATH.$modules);
////            if(is_dir($moduleDir)){//模块存在，加载模块空控制器
////
////            }else{//加载全局空控制器
////
////            }
//            throw new ClassNotFoundException($className);
//        }


        //ajax参数分解
        if(isset($_POST['_PARAMS_'])){
            parse_str($_POST['_PARAMS_'],$temp);
            unset($_POST['_PARAMS_']);
            $_POST = array_merge($_POST,$temp);
        }
        if(isset($_GET['_PARAMS_'])){
            parse_str($_GET['_PARAMS_'],$temp);
            unset($_GET['_PARAMS_']);
            $_GET = array_merge($_GET,$temp);
        }
        //设置$_REQUEST数组
        $_REQUEST = array_merge($_GET,$_POST);

        Corax::status('execute_instance_build_begin');
        //Controller 子类实例
        $classInstance =  new $className();
        Corax::status('execute_instance_build_end');
        //检查方法
        $targetMethod = new \ReflectionMethod($classInstance, $action);
        if ($targetMethod->isPublic() && !$targetMethod->isStatic()) {//非静态的公开方法
            $class = new \ReflectionClass($classInstance);
            //方法的前置操作
            if ($class->hasMethod('_before_' . $action)) {
                $beforeMethod = $class->getMethod('_before_' . $action);
                if ($beforeMethod->isPublic()) {
                    $beforeMethod->invoke($classInstance, $parameters);//在对象上执行这个方法并传递参数
                }
            }


            //方法的参数检测
            if ($targetMethod->getNumberOfParameters()) {//有参数
                //获取输入参数
                $vars = array();
                switch(strtoupper($_SERVER['REQUEST_METHOD'])){
                    case 'POST':
                        $vars    =  array_merge($_GET,$_POST);
                        break;
                    case 'PUT':
                        parse_str(file_get_contents('php://input'), $vars);
                        break;
                    default:
                        $vars  =  $_GET;
                }
//                SEK::merge($vars,$parameters);//解析的参数覆盖到常规方法获取的参数上
//                SEK::dump($vars);

                //获取方法的参数列表 ，并且按照变量名绑定
                $methodParams = $targetMethod->getParameters();
                $args = array();

                //遍历方法的参数
                foreach ($methodParams as $param) {
                    $parameterName = $param->getName();
                    if(isset($vars[$parameterName])){
                        $args[] =   $vars[$parameterName];
                    }elseif($param->isDefaultValueAvailable()){
                        $args[] =   $param->getDefaultValue();
                    }else{
                        throw new \Exception("Method do not get valid  parameter with name of $parameterName !");
                    }
                }
                //参数过滤 ...
                //执行方法
                $targetMethod->invokeArgs($classInstance, $args);
            } else {//无参数的方法调用
                $targetMethod->invoke($classInstance);
            }
            //方法的后置操作
            if ($class->hasMethod('_after_' . $action)) {
                $after = $class->getMethod('_after_' . $action);
                if ($after->isPublic()) {
                    $after->invoke($classInstance);
                }
            }
        } else {
            throw new CoraxException($className, $action);
        }
        Corax::status('execute_method_called_end');
    }

    /**
     * 检验参数是否都是字母开头的标识符
     * @return bool
     */
    private static function checkAllValid(){
        $args = func_get_args();
        foreach($args as $val){
            if(is_array($val)){
                foreach($val as $k=>$v){
                    if(!self::checkAllValid($v)){
                        return false;
                    }
                }
            }else{
                if(!preg_match('/^[A-Za-z](\/|\w)*$/',$val)){
                    return false;
                }
            }
        }
        return true;
    }

}