<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/10
 * Time: 14:43
 */
namespace Application\Test\Controller;
use System\Core\Controller;
use System\Core\Log;

class LogController extends Controller{


    public function index(){

        Log::debug(['lin','zhonghuang']);


    }

}