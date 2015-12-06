<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/11/7
 * Time: 21:13
 */
namespace System\Core;
use System\Exception\ParameterInvalidException;

/**
 * Class ExtDao 扩展的数据入口对象
 * @package System\Core
 */
class ExtDao extends Dao {
    /**
     * 为指定的数据表插入一条数据
     * <code>
     *      $fldsMap ==> array(
     *          //-- 第一种情况，不会进行转义 --//
     *          'fieldName' => 'fieldValue',
     *          //-- 第二种情况,[值，是否转义] --//
     *          'fieldName' => array('fieldValue',boolean),
     *      );
     *
     *     #牛人想到的一个办法(MySQL)
     *     $data = ['a'=>'foo','b'=>'bar'];
     *     $keys = array_keys($data);
     *     $fields = '`'.implode('`, `',$keys).'`';
     *     #here is my way
     *     $placeholder = substr(str_repeat('?,',count($keys),0,-1));
     *     $pdo->prepare("INSERT INTO `baz`($fields) VALUES($placeholder)")->execute(array_values($data));
     * </code>
     * @param string $tablename
     * @param array $fieldsMap
     * @return string|int
     * @throws ParameterInvalidException
     */
    public function create($tablename,$fieldsMap){
        $fields    = '';
        $placeholder     = '';
        $bind  = array();
        if($fieldsMap){
            $flag_n = true;
            $flag = true;
            foreach($fieldsMap as $fieldName=>$fieldValue){
                $fieldName = trim($fieldName,' :');
                $colnm = $fieldName;
                if($flag_n){
                    if(is_numeric($fieldName)){
                        $colnm = $fieldName = 'fields_'.$fieldName;////对于array('1', '[NAME]', '[PASS]', '[EMAIL]', '', '[TIME]', '[IP]', 0, 0, '[TIME]', '1')的情况
                        $flag = false;
                    }
                    $flag_n = false;
                }
                if(is_array($fieldValue)){
                    $colnm = $fieldValue[1]?$this->driver->escapeField($fieldName):$fieldName;
                    $fieldValue = $fieldValue[0];
                }
                if($flag){//字符
                    //拼接插入SQL字符串
                    $fields .= " {$colnm} ,";
                    $placeholder  .= " :{$fieldName} ,";
                    $bind[":{$fieldName}"] = $fieldValue;
                }else{
                    $placeholder .= ' ?,';
                    $bind[] = $fieldValue;
                }
            }
            $flag and ($fields = rtrim($fields,','));
            $placeholder  = rtrim($placeholder,',');
            if($flag){
                return $this->prepare("INSERT INTO {$tablename} ( {$fields} ) VALUES ( {$placeholder} );")->execute($bind);
            }else{
                return $this->prepare("INSERT INTO {$tablename} VALUES ( {$placeholder} );")->execute($bind);
            }
        }else{
            throw new ParameterInvalidException($fieldsMap);
        }
    }

    /**
     * 为指定的数据表更新记录
     * @param string $tablename
     * @param string|array $flds
     * @param string|array $whr
     * @return int|string 返回受影响的行数
     */
    public function update($tablename,$flds,$whr){
        $fields = is_string($flds)?array($flds,array()):$this->makeSegments($flds,false);
        $where  = is_string($whr) ?array($whr,array()) :$this->makeSegments($whr, false);
        $input_params = (empty($fields[1]) && empty($where[1]))?null:array_merge($fields[1],$where[1]);
        $this->prepare("update {$tablename} set {$fields[0]} where {$where[0]};")->execute($input_params);
        return $this->doneExecute();
    }

    /**
     * 删除数据
     * @param $tablename
     * @param $whr
     * @return int|string
     */
    public function delete($tablename,$whr){
        $where  = $this->makeSegments($whr);
        $this->prepare("delete from {$tablename} where {$where[0]};")
            ->execute($where[1]);
        return $this->doneExecute();
    }

    /**
     * 查询一段SQL
     * @param string $tablename
     * @param string|array|null $fields
     * @param string|array|null $whr
     * @return array|bool
     */
    public function select($tablename,$fields=null,$whr=null){
        if($fields and is_array($fields)){
            $fields = implode(',',$fields);
        }else{
            $fields = ' * ';
        }
        $whr  = is_string($whr)?array($whr,null):$this->makeSegments($whr);
        $rst = $this->prepare("select $fields from $tablename where {$whr[0]};")->execute($whr[1]);
        if(false === $rst ){
            return false;
        }
        return $this->fetchAll();
    }


    /**
     * @param string $namelike
     * @param string $dbname
     * @return array
     */
    public function getTables($namelike = '%',$dbname=null){
        return $this->driver->getTables($namelike,$dbname);
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
     * @throws ParameterInvalidException
     */
    protected function makeFieldBind($fieldName,$fieldValue,$operator='=',$translate=false){
        static $suffix = 1;//使用场景是update时既有set绑定又有where绑定时，区分前后二者，将suffix设置可以防止前后冲突
        //绑定设置
        $fieldName = trim($fieldName, ' :');
        $fieldBindName = null;
        if (false !== strpos($fieldName, '.')) {//存在则选择最后一节 ot_students.id  ==> id
            $arr = explode('.', $fieldName);
            $fieldBindName = ':' . array_pop($arr);
        } elseif (mb_strlen($fieldName, 'utf-8') < strlen($fieldName)) {//字段名称为其他的编码
            $fieldBindName = ':' . md5($fieldName);
        } else {
            $fieldBindName = ":{$fieldName}";
        }
        $fieldBindName .= $suffix;//后缀衔接
        //操作符设置
        $operator = strtolower(trim($operator));
        $sql = $translate ? $this->driver->escapeField($fieldName) : $fieldName ;
        $bind = array();

        switch ($operator) {
            case '=':
                $sql .= " = {$fieldBindName} ";
                $bind[$fieldBindName] = $fieldValue;
                break;
            case 'like':
                $sql .= " like {$fieldBindName} ";
                $bind[$fieldBindName] = $fieldValue;
                break;
            case 'in':
            case 'not in':
                if (is_string($fieldValue)) {
                    $sql .= " {$operator} ({$fieldValue}) ";
                } elseif (is_array($fieldValue)) {
                    $sql .= " {$operator} ('" . implode("','", $fieldValue) . "')";
                } else {
                    throw new ParameterInvalidException($fieldName);
                }
                break;
            default:
                throw new ParameterInvalidException($fieldValue);
        }
        ++$suffix;
        return array(
            $sql,
            $bind,
        );
    }

    /**
     * <note>
     *      片段准则
     *      $map == array(
     *          //-- 第一种情况,连接符号一定是'='，并且字段名称不是保留字 --//
     *          'key' => $val,
     *          //-- 第二种情况，[绑定值，是否转义，操作符] --//
     *          'key' => array($val,true,$operator),//布尔值情况如下,遗留问题，参数二和三应该倒置
     *          //-- 第三种情况，[完整的SQL片段，绑定名称，绑定值] --//
     *          array('assignSql',':bindSQLSegment',value),//参数4的值为true时表示对key进行[]转义
     *      );
     * </note>
     * @param array $segments 字段绑定片段
     * @param bool $is_and 表示是否使用and作为连接符，false时为,
     * @return array
     */
    public function makeSegments($segments,$is_and=true){
        //初始值与参数检测
        $bind = array();
        $sql = '';
        if(empty($segments)){
            return array($sql,$bind);
        }
        //片段之间的连接
        $bridge = $is_and?'and':',';

        //元素连接
        foreach($segments as $key=>$val){
            if(is_numeric($key)){//第三种情况
                $sql .= " {$val[0]} $bridge";
                $bind[$val[1]] = $val[2];
            }else{
                $rst = null;
                if(is_array($val)){//第二种情况
                    $rst = $this->makeFieldBind(
                        $val[0],
                        $val[1],
                        empty($val[2])?' = ':$val[2],
                        $val[3]
                    );
                }else{//第一种情况
                    $rst = $this->makeFieldBind($key,$val);
                }
                //合并绑定参数
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $bridge";
                    $bind = array_merge($bind, $rst[1]);
                }
            }
        }
        return array(
            substr($sql,0,strlen($sql)-strlen($bridge)),//去除最后一个and
            $bind,
        );
    }
    /**
     * 根据SQL的各个组成部分创建SQL查询语句
     * @param string $tablename 数据表的名称
     * @param array $components sql组成部分
     * @param int $offset
     * @param int $limit
     * @return string
     */
    public function buildSql($tablename,array $components,$offset=NULL,$limit=NULL){
        return $this->driver->buildSql($tablename,$components,$offset,$limit);
    }
    /**
     * 取得数据表的字段信息
     * @access public
     * @param $tableName
     * @return array
     */
    public function getFields($tableName){
        return $this->driver->getFields($tableName);
    }

    /**
     * 创建数据库
     * @param string $dbname 数据库名称
     * @return int 受影响的行数
     */
    public function createDatabase($dbname){
        return $this->driver->createDatabase($dbname);
    }
}