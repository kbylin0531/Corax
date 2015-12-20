<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/7
 * Time: 21:13
 */
namespace System\Core;
use System\Exception\CoraxException;

/**
 * Class ExtDao 方法扩展的Dao
 * @package System\Core
 */
class ExtDao extends Dao {

    /**
     * 添加数据
     * <code>
     *      $fldsMap ==> array(
     *          'fieldName' => 'fieldValue',
     *          'fieldName' => array('fieldValue',boolean),//第二个元素表示是否对字段名称进行转义
     *      );
     *
     *     $data = ['a'=>'foo','b'=>'bar'];
     *     $keys = array_keys($data);
     *     $fields = '`'.implode('`, `',$keys).'`';
     *     #here is my way
     *     $placeholder = substr(str_repeat('?,',count($keys),0,-1));
     *     $pdo->prepare("INSERT INTO `baz`($fields) VALUES($placeholder)")->execute(array_values($data));
     * </code>
     *
     * 插入数据的sql可以是：
     * ①INSERT INTO 表名称 VALUES (值1, 值2,....)
     * ②INSERT INTO table_name (列1, 列2,...) VALUES (值1, 值2,....)
     *
     * @param string $tablename
     * @param array $fieldsMap
     * @return bool 返回true或者false
     * @throws CoraxException
     */
    public function create($tablename,$fieldsMap){
        $fields = $placeholder = '';
        $sql = null;
        $bind  = [];
        $flag = true;//标记是否进行插入形式判断

        foreach($fieldsMap as $fieldName=>$fieldValue){
            $colnm = $fieldName;
            if($flag){
                if(is_numeric($fieldName)){
                    $placeholder  = rtrim(str_repeat(' ?,',count($fieldsMap)),',');
                    $sql = "INSERT INTO {$tablename} VALUES ( {$placeholder} );";
                    $bind = $fieldsMap;
                    break;
                }
                $flag = false;
            }
            if(is_array($fieldValue)){ //不设置字段名称进行插入时$fieldName无意义
                $colnm = $fieldValue[1]?$this->driver->escapeField($fieldName):$fieldName;
                $fieldValue = $fieldValue[0];
            }
            $fields .= " {$colnm} ,";
            $placeholder  .= " :{$fieldName} ,";
            $bind[":{$fieldName}"] = $fieldValue;
        }

        if(isset($sql)){
            $fields = rtrim($fields,',');
            $sql = "INSERT INTO {$tablename} ( {$fields} ) VALUES ( {$placeholder} );";
        }
        return $this->prepare($sql)->execute($bind);
    }

    /**
     * 更新数据表
     * @param string $tablename
     * @param string|array $flds
     * @param string|array $whr
     * @return bool
     * @throws CoraxException
     */
    public function update($tablename,$flds,$whr){;
        $input_params = [];
        $fields = is_string($flds)?[$flds,[]]:$this->makeSegments($flds,false);
        $where  = is_string($whr) ?[$whr,[]] :$this->makeSegments($whr, false);
        empty($fields[1]) or $input_params = $fields[1];
        empty($where[1]) or array_merge($input_params,$where[1]);
        return $this->prepare("UPDATE {$tablename} SET {$fields[0]} WHERE {$where[0]};")->execute($input_params);
    }

    /**
     * 执行删除数据的操作
     * 如果不设置参数，则进行清空表的操作
     * @param string $tablename 数据表的名称
     * @param array $whr 字段映射数组
     * @return bool
     */
    public function delete($tablename,$whr=null){
        $bind = null;
        if(isset($whr)){
            $where  = $this->makeSegments($whr);
            $sql    = "delete from {$tablename} where {$where[0]};";
            $bind   = $where[1];
        }else{
            $sql = "delete from {$tablename};";
        }
        return $this->prepare($sql)->execute($bind);
    }

    /**
     * ��ѯһ��SQL
     * @param string $tablename
     * @param string|array|null $fields
     * @param string|array|null $whr
     * @return array|bool
     * @throws CoraxException
     */
    public function select($tablename,$fields=null,$whr=null){
        $bind = null;

        //设置选取字段
        if(null === $fields){
            $fields = ' * ';
        }elseif($fields and is_array($fields)){
            //默认转义
            array_map(function($param){
                return $this->driver->escapeField($param);
            },$fields);
            $fields = implode(',',$fields);
        }elseif(!is_string($fields)){
            throw new CoraxException('Parameter 2 require the type of "null","array","string" ,now is invalid!');
        }

        if(null === $whr){
            $sql = "select {$fields} from {$tablename};";
        }elseif(is_array($whr)){
            $whr  = is_string($whr)? [$whr,null] :$this->makeSegments($whr);
            $sql = "select {$fields} from {$tablename} where {$whr[0]};";
            $bind = $whr[1];
        }elseif(is_string($whr)){
            $sql = "select {$fields} from {$tablename} where {$whr};";
        }else{
            throw new CoraxException('Parameter 3 require the type of "null","array","string" ,now is invalid!');
        }


        if(false === $this->prepare($sql)->execute($bind) ){
            return false;
        }
        return $this->fetchAll();
    }





    /**
     * 综合字段绑定的方法
     * <code>
     *      $operator = '='
     *          $fieldName = :$fieldName
     *          :$fieldName => trim($fieldValue)
     *
     *      $operator = 'like'
     *          $fieldName = :$fieldName
     *          :$fieldName => dowithbinstr($fieldValue)
     *
     *      $operator = 'in|not_in'
     *          $fieldName in|not_in array(...explode(...,$fieldValue)...)
     * </code>
     * @param string $fieldName 字段名称
     * @param string|array $fieldValue 字段值
     * @param string $operator 操作符
     * @param bool $translate 是否对字段名称进行转义,MSSQL中使用[]
     * @return array
     * @throws CoraxException
     */
    protected function makeFieldBind($fieldName,$fieldValue,$operator='=',$translate=false){
        $fieldName = trim($fieldName,' :[]');
        $bindFieldName = null;
        if(false !== strpos($fieldName,'.')){
            $arr = explode('.',$fieldName);
            $bindFieldName = ':'.array_pop($arr);
        }elseif(mb_strlen($fieldName,'utf-8') < strlen($fieldName)){//其他编码
            $bindFieldName = ':'.md5($fieldName);
        }else{
            $bindFieldName = ":{$fieldName}";
        }

        $operator = strtolower(trim($operator));
        $sql = $translate?" [{$fieldName}] ":" {$fieldName} ";
        $bind = array();

        switch($operator){
            case '=':
                $sql .= " = {$bindFieldName} ";
                $bind[$bindFieldName] = $fieldValue;
                break;
            case 'like':
                $sql .= " like {$bindFieldName} ";
                $bind[$bindFieldName] = $fieldValue;
                break;
            case 'in':
            case 'not in':
                if(is_string($fieldValue)){
                    $sql .= " {$operator} ({$fieldValue}) ";
                }elseif(is_array($fieldValue)){
                    $sql .= " {$operator} ('".implode("','",$fieldValue)."')";
                }else{
                    throw new CoraxException("The parameter 1 '{$fieldValue}' is invalid!");
                }
                break;
            default:
                throw new CoraxException("The parameter 2 '{$operator}' is invalid!");
        }
        return [$sql,$bind];
    }

    /**
     * 片段设置
     * <note>
     *      片段准则
     *      $map == array(
     *           //第一种情况,连接符号一定是'='//
     *          'key' => $val,
     *          'key' => array($val,$operator,true),
     *          //第二种情况，数组键，数组值//
     *          array('key','val','like|=',true),//参数4的值为true时表示对key进行[]转义
     *          //第三种情况，字符键，数组值//
     *          'assignSql' => array(':bindSQLSegment',value)//与第一种情况第二子目相区分的是参数一以':' 开头
     *      );
     * </note>
     * @param $map
     * @param bool $and 表示是否使用and作为连接符，false时为,
     * @return array
     */
    protected function makeSegments($map,$and=true){
        //初始值与参数检测
        $bind = array();
        $sql = '';
        if(empty($map)){
            return array($sql,$bind);
        }
        $connect = $and?'and':',';


        //元素连接
        foreach($map as $key=>$val){
            if(is_numeric($key)){
                //第二种情况
                $rst = $this->makeFieldBind(
                    $val[0],
                    $val[1],
                    isset($val[2])?$val[2]:' = ',
                    !empty($val[3])
                );
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $connect";
                    $bind = array_merge($bind, $rst[1]);
                }
            }elseif(is_array($val) and strpos($val[0],':') === 0){
                //第三种情况,复杂类型，由用户自定义
                $sql .= " {$key} $connect";
                $bind[$val[0]] = $val[1];
            }else{
                //第一种情况
                $translate = false;
                $operator = '=';
                if(is_array($val)){
                    $translate = isset($val[2])?$val[2]:false;
                    $operator = isset($val[1])?$val[1]:'=';
                    $val = $val[0];
                }
                $rst = $this->makeFieldBind($key,trim($val),$operator,$translate);//第一种情况一定是'='的情况
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $connect";
                    $bind = array_merge($bind, $rst[1]);
                }
            }
        }
        $result = array(
            substr($sql,0,strlen($sql)-strlen($connect)),//去除最后一个and
            $bind,
        );
        return $result;
    }


    /**
     * 根据条件获得查询的SQL，SQL执行的正确与否需要实际查询才能得到验证
     * @param string $tablename 查找的表名称,不需要带上from部分
     * @param array $components  复杂SQL的组成部分
     * @param null|integer $offset 偏移
     * @param null|integer $limit  选择的最大的数据量
     * @return string
     */
    public function buildSql($tablename,array $components=[],$offset=NULL,$limit=NULL){
        return $this->driver->buildSqlByComponent($tablename,$components,$offset,$limit);
    }


    /**
     * 获取数据表字段
     * @access public
     * @param $tableName
     * @return array
     */
    public function getFields($tableName){
        return $this->driver->getFields($tableName);
    }


    /**
     * 执行结果信息返回
     * @return int|string 返回受影响行数，发生错误时返回错误信息
     */
    public function doneExecute(){
        if(null === $this->error){
            //未发生错误，返回受影响的行数目
            return $this->rowCount();
        }else{
            //发生饿了错误，得到错误信息并清空错误标记
            $temp = $this->error;
            $this->error = null;
            return $temp;
        }
    }
    /**
     * 查询结果集全部返回
     * 内部实现依赖于fetchAll方法，参数同
     * @param null $fetch_style
     * @param null $fetch_argument
     * @param null $constructor_args
     * @return string|Dao 返回查询结果集，发生错误时返回错误信息
     */
    public function doneQuery($fetch_style = null, $fetch_argument = null, $constructor_args = null){
        if(null === $this->error){
            //未发生错误，返回受影响的行数目
            return $this->fetchAll($fetch_style, $fetch_argument, $constructor_args);
        }else{
            //发生饿了错误，得到错误信息并清空错误标记
            $temp = $this->error;
            $this->error = null;
            return $temp;
        }
    }

}