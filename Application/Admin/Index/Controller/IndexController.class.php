<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/17
 * Time: 11:00
 */
namespace Application\Admin\Index\Controller;
use Application\Admin\Common\Controller\AdminController;


/**
 * Class IndexController 后台首页控制器
 * @package Application\Admin\Index\Controller
 */
class IndexController extends AdminController{
    /**
     * 后台首页
     */
    public function index(){
        $this->display();
    }

}