<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/12/5
 * Time: 10:40
 */
namespace Application\Admin\UserManagement\Controller;
use System\Core\Controller;
use System\Util\UDK;

class UserListController extends Controller {

    public function ulist01(){
        UDK::dump($_GET);
        echo __METHOD__;
    }

    public function ulist02(){
        echo __METHOD__;
    }

    public function ulist03(){
        echo __METHOD__;
    }

}