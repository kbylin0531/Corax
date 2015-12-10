<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/6
 * Time: 19:11
 */


/**
 * 需要遍历的配置文件
 */
return [
    //需要加载的配置文件数组
    'CONFIG_LIST'    => [
        'custom','database','guide','hook','log','modules','route','security','template','cache','session','function',
    ],
    'REFRESH_INTERVAL' => 10,//刷新间隔
    //缓存驱动
    'CACHE_DRIVER_TYPE'    => null,
];