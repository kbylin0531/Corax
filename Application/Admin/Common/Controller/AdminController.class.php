<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/17
 * Time: 11:01
 */
namespace Application\Admin\Common\Controller;
use System\Core\Configer;
use System\Core\Controller;

/**
 * Class AdminController 后台控制器基类
 * @package Application\Admin\Common\Controller
 */
class AdminController extends Controller{


    public function __construct(){
        parent::__construct();
        //模板中使用{$smarty.const.你定义的常量名}
        defined('ADMIN_PATH') or define('ADMIN_PATH',URL_PUBLIC_PATH.'/libs/bs3/');


        $this->setTopNavBar(Configer::load('admin.top_menubar'));


    }

    /**
     * @param array $barconf 配置顺粗
     * @param int $active 激活的顺序
     */
    protected function setTopNavBar(array $barconf,$active=1){
        $this->assign('topbar_menuconf',$barconf);
        $this->assign('active_index',$active);
    }

}