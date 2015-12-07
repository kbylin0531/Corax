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
    // 设置成null时，默认的缓存将视不同的使用环境而定
    'DEFAULT_DRIVER'    => null,

    //MEMCACHE缓存配置
    'MEMCACHE_CONF'     => [
        'HOST'  => 'localhost',
        'PORT'  => 10010,
        'TIMEOUT'   => 1, // 1秒超时
        'CACHE_EXPIRE'  => 3600,//默认缓存时间
    ],
    'FILE_CONF' => [
        /* 数据缓存设置 */
        'DATA_CACHE_PATH'       =>  RUNTIME_PATH.'Cache/',// 缓存路径设置 (仅对File方式缓存有效)
        'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
        'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存

        'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
        'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
        'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
        'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)
        'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
        'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别
    ],
];