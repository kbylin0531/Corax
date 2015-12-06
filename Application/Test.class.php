<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/2
 * Time: 21:05
 */
namespace Application;

use System\Core\Router;
use System\Util\UDK;

class Test extends Router{


    public static function p(){
        UDK::dumpout(self::$convention);
    }




}