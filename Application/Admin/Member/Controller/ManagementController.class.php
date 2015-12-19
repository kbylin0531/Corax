<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/19
 * Time: 12:50
 */
namespace Application\Admin\Member\Controller;
use Application\Admin\Common\Controller\AdminController;

/**
 * Class ManagementController 用户管理控制器
 * @package Application\Admin\Member\Controller
 */
class ManagementController extends AdminController {

    public function __construct(){
        parent::__construct();
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

    public function index(){
        $this->display();
    }



}