<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/12
 * Time: 15:43
 */
namespace System\Util;

/**
 * ѡ��CodeIgniter�Ĺ��ߣ�ԭ���Ƕ����ĺ���
 * Class CodeIgniterKits
 * @package System\Util
 */
class CodeIgniterKits {

    /**
     * ԭʼ�������ƣ�is_cli
     * Test to see if a request was made from the command line.
     * @return 	bool
     */
    function isClient(){
        return (PHP_SAPI === 'cli' OR defined('STDIN'));
    }


}