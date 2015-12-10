<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/8
 * Time: 15:23
 */
namespace Application\Home\Controller;
use System\Core\Cache;
use System\Core\Configer;
use System\Core\Controller;
use System\Core\Router;
use System\Util\UDK;

/**
 * Class IndexController
 * @package Application\Home\Controller
 */
class IndexController extends Controller {

    public function index(){
//        $this->testURLParse();
//        $this->testURLCreater();
        dump('hello world!');

        echo __METHOD__;
    }

    protected function testURLCreater(){
        $url = '';

        $url .= Router::build(array('admin','UserManagement'),'UserList','ulist01',['param01'=>'value01']);

        echo "<a href='{$url}' target='_blank'>{$url}</a><br />";
        echo __METHOD__;
    }

    /**
     * 测试URL常规解析，实际测试地址:
     * index.php?_m=admin/user_management&_c=user_list&_a=ulist01&param01=value01
     * index.php/admin/user_management/user_list/ulist01/co/param02/value02
     * index.php?_pathinfo=/admin/user_management/user_list/ulist01/co/param02/value02
     *
     * @throws \System\Exception\CoraxException
     */
    protected function testURLParse(){
        $rst1 = Router\URLParser::parse('_m=admin/user_management&_c=user_list&_a=ulist01&param01=value01');
        $rst2 = Router\URLParser::parse('/admin/user_management/user_list/ulist01/co/param02/value02');
        $rst3 = Router\URLParser::parse('_pathinfo=/admin/user_management/user_list/ulist01/co/param02/value02');
        UDK::dump($rst1,$rst2,$rst3);
    }


}
