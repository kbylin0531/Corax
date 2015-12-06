<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/25
 * Time: 20:13
 */
namespace test;

use System\Core\Router\RuleCreater;

$config = [

];


$ruleCreater = RuleCreater::getInstance($config);
$rst = $ruleCreater->create();
