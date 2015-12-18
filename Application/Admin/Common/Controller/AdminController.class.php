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

        $this->assignTopNavBar([
            'menus' => [
                //Home模块
                [
                    'index'    => 1,
                    'name'  => 'Home',
                    'url'       => '#'
                ],
                //About单个菜单
                [
                    'index'    => 2,
                    'name'  => 'About',
                    'url'       => '#'
                ],
                //复合菜单一
                [
                    'index'    => 3,
                    'name'  => 'Contact',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'index'    => 3,
                            'name'  => 'Action',
                            'url'       => '#'
                        ],
                        [
                            'index'    => 3,
                            'name'  => 'Another',
                            'url'       => '#'
                        ],
                        [
                            'index'    => 3,
                            'name'  => 'Something',
                            'menus'     => [
                                [
                                    'index'    => 3,
                                    'name'  => 'Look',
                                    'url'       => '#'
                                ],
                                [
                                    'index'    => 3,
                                    'name'  => 'Nice',
                                    'url'       => '#'
                                ],
                            ],
                        ],
                    ],
                ],
                //复合菜单二
                [
                    'index'    => 4,
                    'name'  => 'Knowledge',
                    'type'  => 2,
                    'menus'     => [
                        [
                            [
                                'index'     => 4,
                                'name'      => 'Look',
                                'url'       => '#',
                            ],
                            [
                                'index'     => 4,
                                'name'      => 'Nice',
                                'url'       => '#'
                            ],
                        ],
                        [
                            [
                                'index'     => 4,
                                'name'      => 'Look',
                                'icon'      => 'fa-gear', // 设置了图标之后无法使url生效
                            ],
                            [
                                'index'     => 4,
                                'name'      => 'Nice',
                                'url'       => '#'
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assignUserInfo([
            'nickname'  =>  'Linzhv',
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

        $this->assignSideBar([
            'menus' => [
                [
                    'icon'  => 'fa-home',
                    'name'  => 'Hello',
                    'submenus'  => [
                        [
                            'index' => 1,
                            'name'  => 'name1',
                            'url'   => '#',
                            'meta'   => 'New',
                        ],
                        [
                            'index' => 2,
                            'name'  => 'name2',
                            'url'   => '#',
                            'meta'   => '',
                        ],
                    ],
                ],
                [
                    'icon'  => 'fa-smile-o',
                    'name'  => 'Elements',
                    'submenus'  => [
                        [
                            'index' => 3,
                            'name'  => 'name1',
                            'url'   => '#',
                            'meta'   => 'New',
                        ],
                        [
                            'index' => 4,
                            'name'  => 'name2',
                            'url'   => '#',
                            'meta'   => '',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array $barconf 配置顺粗
     * @param int $active 激活的顺序
     */
    protected function assignTopNavBar(array $barconf,$active=1){
        $this->assign('topbar_menuconf',$barconf);
        $this->assign('active_index',$active);
    }

    /**
     * 分配用户信息
     * @param array $info
     */
    protected function assignUserInfo(array $info){
        $this->assign('user_info',$info);
    }

    /**
     * 分配用户的离线消息
     * @param array $config
     */
    protected function assignMessages(array $config){
        $this->assign('message',$config);
    }

    /**
     * 设置侧边栏菜单
     * @param array $config
     * @param int $active_index
     */
    protected function assignSideBar(array $config,$active_index=1){
        $this->assign('sidebar_config',$config);
        $this->assign('active_index',$active_index);
    }


}