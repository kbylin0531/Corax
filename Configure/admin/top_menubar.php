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
            'id'    => 1,
            'name'  => 'Home',
            'url'       => '#'
        ],
        //About单个菜单
        [
            'id'    => 2,
            'name'  => 'About',
            'url'       => '#'
        ],
        //复合菜单一
        [
            'id'    => 3,
            'name'  => 'Contact',
            'type'  => 1,
            'menus'     => [
                [
                    'id'    => 31,
                    'name'  => 'Action',
                    'url'       => '#'
                ],
                [
                    'id'    => 32,
                    'name'  => 'Another',
                    'url'       => '#'
                ],
                [
                    'id'    => 33,
                    'name'  => 'Something',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'id'    => '6',
                            'name'  => 'Look',
                            'url'       => '#'
                        ],
                        [
                            'id'    => '7',
                            'name'  => 'Nice',
                            'url'       => '#'
                        ],
                    ],
                ],
            ],
        ],
        //复合菜单二
        [
            'id'    => 4,
            'name'  => 'knowledge',
            'type'  => 2,
            'menus'     => [
                [
                    'id'    => 41,
                    'name'  => 'Users',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'id'    => 411,
                            'name'  => 'Look',
                            'icon' => 'fa-group',
                            'url'       => '#',
                        ],
                        [
                            'id'    => 412,
                            'name'  => 'Nice',
                            'url'       => '#'
                        ],
                    ],
                ],
                [
                    'id'    => 42,
                    'name'  => 'Something',
                    'type'  => 1,
                    'menus'     => [
                        [
                            'id'    => 421,
                            'name'  => 'Look',
                            'icon' => 'fa-gear',
                            'url'       => '#'
                        ],
                        [
                            'id'    => 422,
                            'name'  => 'Nice',
                            'url'       => '#'
                        ],
                    ],
                ],
            ],
        ],
    ],
];