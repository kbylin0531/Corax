<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/17
 * Time: 9:45
 */
namespace System\Core\Dao;
use System\Exception\CoraxException;
use System\Util\SEK;

defined('BASE_PATH') or die('No Permission!');

/**
 * Class MysqlDriver
 * @package System\Core\DaoDriver
 */
class Mysql extends Base{

    protected static $_l_quote = '`';
    protected static $_r_quote = '`';


    public function __construct($dsn,$username,$password,$option=array()){
        //检查扩展是否开启
        if(!SEK::phpExtend('pdo_mysql')){
//            dl('pdo_mysql');
            throw new CoraxException('pdo_mysql');
        }
//        Util::dump($dsn,$username,$password,$option);exit;
        parent::__construct($dsn,$username,$password,$option);
    }

    public function getTables($namelike = '%',$dbname=null){
        $sql    = isset($dbname)?"SHOW TABLES FROM  $dbname  LIKE '$namelike' ":"SHOW TABLES   LIKE '$namelike' ";
        $result = $this->query($sql)->fetchAll();
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }


    public function escapeField($fieldname){
        return self::$_l_quote.$fieldname.self::$_r_quote;
    }


    /**
     * 根据SQL的各个组成部分创建SQL查询语句
     * @param string $tablename 数据表的名称
     * @param array $compos sql组成部分
     * @param int $offset
     * @param int $limit
     * @return string
     */
    public function buildSql($tablename,array $compos,$offset=NULL,$limit=NULL){
        $components = array(
            'distinct'=>'',
            'fields'=>' * ', //查询的表域情况
            'join'=>'',     //join部分，需要带上join关键字
            'where'=>'', //where部分
            'group'=>'', //分组 需要带上group by
            'having'=>'',//having子句，依赖$group存在，需要带上having部分
            'order'=>'',//排序，不需要带上order by
        );
        $components = array_merge($components,$compos);
        if($components['distinct']){//为true或者1时转化为distinct关键字
            $components['distinct'] = 'distinct';
        }
        $sql = " select {$components['distinct']} {$components['fields']}  from  {$tablename} ";

        //group by，having 加上关键字(对于如group by的组合关键字，只要判断第一个是否存在)如果不是以该关键字开头  则自动添加
        if($components['where'] && 0 !== stripos(trim($components['where']),'where')){
            $components['where'] = ' where '.$components['where'];
        }
        if($components['group'] && 0 !== stripos(trim($components['group']),'group')){
            $components['group'] = ' group by '.$components['group'];
        }
        if( $components['having'] && 0 !== stripos(trim($components['having']),'having')){
            $components['having'] = ' having '.$components['having'];
        }
        //去除order by
        $components['order'] = preg_replace_callback('|order\s*by|i',function(){return '';},$components['order']);

        //按照顺序连接，过滤掉一些特别的参数
        foreach($components as $key=>&$val){
            if(in_array($key,array('fields','order','distinct'))) continue;
            $sql .= " {$val} ";
        }

        $flag = true;//标记是否需要再次设置order by

        //是否湖区偏移
        if(NULL !== $offset && NULL !== $limit){
            $outerOrder = ' order by ';
            if(!empty($components['order'])){
                //去掉其中的order by
                $orders = @explode(',',$components['order']);//分隔多个order项目

                foreach($orders as &$val){
                    $segs = @explode('.',$val);
                    $outerOrder .= array_pop($segs).',';
                }
                $outerOrder  = rtrim($outerOrder,',');
            }else{
                $outerOrder .= ' rand() ';
            }
            $endIndex = $offset+$limit;
            $sql = "SELECT T1.* FROM (
            SELECT  ROW_NUMBER() OVER ( {$outerOrder} ) AS ROW_NUMBER,thinkphp.* FROM ( {$sql} ) AS thinkphp
            ) AS T1 WHERE (T1.ROW_NUMBER BETWEEN 1+{$offset} AND {$endIndex} )";
            $flag = false;
        }
        if($flag && !empty($components['order'])){
            $sql .= ' order by '.$components['order'];
        }
        return $sql;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @param $tableName
     * @return array
     */
    public function getFields($tableName) {
        list($tableName) = explode(' ', $tableName);
        $sql   = 'SHOW COLUMNS FROM `'.$tableName.'`';
        $result = $this->query($sql);
        $info   =   array();
        if($result) {
            foreach ($result->fetchAll() as $key => $val) {
                $info[$val['field']] = array(
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => $val['null'] === '', // not null is empty, null is yes
                    'default' => $val['default'],
                    'primary' => (strtolower($val['key']) === 'pri'),
                    'autoinc' => (strtolower($val['extra']) === 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     * 创建数据库
     * @param string $dbname 数据库名称
     * @return int 受影响的行数
     */
    public function createDatabase($dbname){
        $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
        return $this->exec($sql);
    }

}