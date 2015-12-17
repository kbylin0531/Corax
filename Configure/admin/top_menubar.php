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
];