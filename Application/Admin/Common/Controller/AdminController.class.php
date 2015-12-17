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


        $this->assignTopNavBar();
        $this->assignUserInfo([
            'nickname'  =>  'nickname',
            'avatar'    =>  ADMIN_PATH.'images/avatar2.jpg',
            'user_menu' => [
                [
                    'Account'   => '#',
                    'Profile'   => '#',
                    'Messages'  => '#',
                ],
                [
                    'Sign Out'  => '#',
                ],
            ],
        ]);
        $this->assignMessages([

        ]);

    }

    /**
     * @param array $barconf 配置顺粗
     * @param int $active 激活的顺序
     */
    protected function assignTopNavBar(array $barconf=null,$active=1){
        isset($barconf) or $barconf = Configer::load('admin.top_menubar');
        $this->assign('topbar_menuconf',$barconf);
        $this->assign('active_index',$active);
    }

    protected function assignUserInfo(array $info){
        $this->assign('user_info',$info);
    }

    protected function assignMessages(array $config){
        $this->assign('message',$config);
    }


}