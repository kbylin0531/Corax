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
use System\Core\Router;
use System\Util\SEK;
use System\Util\UDK;

/**
 * Class AdminController 后台控制器基类
 * @package Application\Admin\Common\Controller
 */
class AdminController extends Controller{


    public function __construct(){
        parent::__construct();
        //模板中使用{$smarty.const.你定义的常量名}
        defined('ADMIN_PATH') or define('ADMIN_PATH',URL_PUBLIC_PATH.'/libs/bs3/');


        //topbar active index
        $tai = isset($_REQUEST['_tai'])?intval($_REQUEST['_tai']):1;

        $this->assignTopNavBar([
            'menus' => [
                //Home模块
                [
                    'index'     => 1,
                    'name'      => 'User',
                    'url'       => $this->buildUrl('admin/member/management/index',1,1),
                ],
                //About单个菜单
                [
                    'index'     => 2,
                    'name'      => 'System',
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
                                'icon'      => 'icon-gear', // 设置了图标之后无法使url生效
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
        ],$tai);
        $this->assignUserInfo([
            'nickname'  =>  'Linzhv',
            'avatar'    =>  URL_PUBLIC_PATH.'/images/avatar2.jpg',
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

        $this->assignSideBar([
            'menus' => [
                [
                    'icon'  => 'icon-home',
                    'name'  => 'Member Group',
                    'submenus'  => [
                        [
                            //末端需要进行index编号
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
                    'icon'  => 'icon-smile',
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
     * 建立URL
     * @param string $url url
     * @param int $tai current topbar index
     * @param int $sai default sidebar index
     * @return string
     */
    private function buildUrl($url,$tai,$sai){
        return SEK::url($url,['_tai'=>$tai,'_sai'=>$sai]);
    }

    /**
     * @param array $barconf 配置顺粗
     * @param int $active 激活的顺序
     */
    protected function assignTopNavBar(array $barconf,$active=1){
        $this->assign('topbar_menuconf',$barconf);
        $this->assign('topbar_active_index',$active);
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
    protected function assignSideBar(array $config,$active_index=null){
        isset($active_index) or $active_index = isset($_REQUEST['_sai'])?intval($_REQUEST['_sai']):1;
        $this->assign('sidebar_config',$config);
        $this->assign('sidebar_active_index',$active_index);
    }


}