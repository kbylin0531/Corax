<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/6
 * Time: 19:32
 */

/**
 * Cache类的配置
 */
return [
    'DEFAULT_DRIVER'    => \System\Core\Cache::CACHEMODE_MEMCACHE,
    'MEMCACHE_CONF'     => [
        'HOST'  => 'localhost',
        'PORT'  => 10010,
        'TIMEOUT'   => 1, // 1秒超时
        'CACHE_EXPIRE'  => 3600,//默认缓存时间
    ],
];