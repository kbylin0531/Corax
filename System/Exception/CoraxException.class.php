<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/3
 * Time: 20:30
 */
namespace System\Exception;

class CoraxException extends \Exception {

    public function __construct(){
        $args = func_get_args();
        $this->message = var_export($args,true);
    }

}