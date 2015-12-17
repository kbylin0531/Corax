<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/17
 * Time: 13:59
 */
return [
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
                    'index'    => 31,
                    'name'  => 'Action',
                    'url'       => '#'
                ],
                [
                    'index'    => 32,
                    'name'  => 'Another',
                    'url'       => '#'
                ],
                [
                    'index'    => 33,
                    'name'  => 'Something',
                    'menus'     => [
                        [
                            'index'    => '6',
                            'name'  => 'Look',
                            'url'       => '#'
                        ],
                        [
                            'index'    => '7',
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
                    'index'    => 41,
                    'name'  => 'Users',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'index'    => 411,
                            'name'  => 'Look',
                            'icon' => 'fa-group',
                            'url'       => '#',
                        ],
                        [
                            'index'    => 412,
                            'name'  => 'Nice',
                            'url'       => '#'
                        ],
                    ],
                ],
                [
                    'index'    => 42,
                    'name'  => 'Something',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'index'    => 421,
                            'name'  => 'Look',
                            'icon' => 'fa-gear',
                            'url'       => '#'
                        ],
                        [
                            'index'    => 422,
                            'name'  => 'Nice',
                            'url'       => '#'
                        ],
                    ],
                ],
            ],
        ],
    ],
];