<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/12
 * Time: 15:43
 */
namespace System\Util;

/**
 * 选自CodeIgniter的工具，原先是独立的函数
 * Class CodeIgniterKits
 * @package System\Util
 */
class CodeIgniterKits {

    /**
     * 原始函数名称：is_cli
     * Test to see if a request was made from the command line.
     * @return 	bool
     */
    function isClient(){
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }


}