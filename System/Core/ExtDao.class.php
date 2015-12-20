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
 * Class ExtDao 方法扩展的Dao
 * @package System\Core
 */
class ExtDao extends Dao {
    /**
     * Ϊָ�������ݱ����һ������
     * <code>
     *      $fldsMap ==> array(
     *          //-- ��һ��������������ת�� --//
     *          'fieldName' => 'fieldValue',
     *          //-- �ڶ������,[ֵ���Ƿ�ת��] --//
     *          'fieldName' => array('fieldValue',boolean),
     *      );
     *
     *     #ţ���뵽��һ���취(MySQL)
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
                        $colnm = $fieldName = 'fields_'.$fieldName;////����array('1', '[NAME]', '[PASS]', '[EMAIL]', '', '[TIME]', '[IP]', 0, 0, '[TIME]', '1')�����
                        $flag = false;
                    }
                    $flag_n = false;
                }
                if(is_array($fieldValue)){
                    $colnm = $fieldValue[1]?$this->driver->escapeField($fieldName):$fieldName;
                    $fieldValue = $fieldValue[0];
                }
                if($flag){//�ַ�
                    //ƴ�Ӳ���SQL�ַ���
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
     * Ϊָ�������ݱ���¼�¼
     * @param string $tablename
     * @param string|array $flds
     * @param string|array $whr
     * @return int|string ������Ӱ�������
     */
    public function update($tablename,$flds,$whr){
        $fields = is_string($flds)?array($flds,array()):$this->makeSegments($flds,false);
        $where  = is_string($whr) ?array($whr,array()) :$this->makeSegments($whr, false);
        $input_params = (empty($fields[1]) && empty($where[1]))?null:array_merge($fields[1],$where[1]);
        $this->prepare("update {$tablename} set {$fields[0]} where {$where[0]};")->execute($input_params);
        return $this->doneExecute();
    }

    /**
     * ɾ������
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
     * ��ѯһ��SQL
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
     * �ۺ��ֶΰ󶨵ķ���
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
     * @param string $fieldName �ֶ�����
     * @param string|array $fieldValue �ֶ�ֵ
     * @param string $operator ������
     * @param bool $translate �Ƿ���ֶ����ƽ���ת��,MSSQL��ʹ��[]
     * @return array
     * @throws ParameterInvalidException
     */
    protected function makeFieldBind($fieldName,$fieldValue,$operator='=',$translate=false){
        static $suffix = 1;//ʹ�ó�����updateʱ����set������where��ʱ������ǰ����ߣ���suffix���ÿ��Է�ֹǰ���ͻ
        //������
        $fieldName = trim($fieldName, ' :');
        $fieldBindName = null;
        if (false !== strpos($fieldName, '.')) {//������ѡ�����һ�� ot_students.id  ==> id
            $arr = explode('.', $fieldName);
            $fieldBindName = ':' . array_pop($arr);
        } elseif (mb_strlen($fieldName, 'utf-8') < strlen($fieldName)) {//�ֶ�����Ϊ�����ı���
            $fieldBindName = ':' . md5($fieldName);
        } else {
            $fieldBindName = ":{$fieldName}";
        }
        $fieldBindName .= $suffix;//��׺�ν�
        //����������
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
     *      Ƭ��׼��
     *      $map == array(
     *          //-- ��һ�����,���ӷ���һ����'='�������ֶ����Ʋ��Ǳ����� --//
     *          'key' => $val,
     *          //-- �ڶ��������[��ֵ���Ƿ�ת�壬������] --//
     *          'key' => array($val,true,$operator),//����ֵ�������,�������⣬����������Ӧ�õ���
     *          //-- �����������[������SQLƬ�Σ������ƣ���ֵ] --//
     *          array('assignSql',':bindSQLSegment',value),//����4��ֵΪtrueʱ��ʾ��key����[]ת��
     *      );
     * </note>
     * @param array $segments �ֶΰ�Ƭ��
     * @param bool $is_and ��ʾ�Ƿ�ʹ��and��Ϊ���ӷ���falseʱΪ,
     * @return array
     */
    public function makeSegments($segments,$is_and=true){
        //��ʼֵ��������
        $bind = array();
        $sql = '';
        if(empty($segments)){
            return array($sql,$bind);
        }
        //Ƭ��֮�������
        $bridge = $is_and?'and':',';

        //Ԫ������
        foreach($segments as $key=>$val){
            if(is_numeric($key)){//���������
                $sql .= " {$val[0]} $bridge";
                $bind[$val[1]] = $val[2];
            }else{
                $rst = null;
                if(is_array($val)){//�ڶ������
                    $rst = $this->makeFieldBind(
                        $val[0],
                        $val[1],
                        empty($val[2])?' = ':$val[2],
                        $val[3]
                    );
                }else{//��һ�����
                    $rst = $this->makeFieldBind($key,$val);
                }
                //�ϲ��󶨲���
                if(is_array($rst)){
                    $sql .= " {$rst[0]} $bridge";
                    $bind = array_merge($bind, $rst[1]);
                }
            }
        }
        return array(
            substr($sql,0,strlen($sql)-strlen($bridge)),//ȥ�����һ��and
            $bind,
        );
    }
    /**
     * ����SQL�ĸ�����ɲ��ִ���SQL��ѯ���
     * @param string $tablename ���ݱ������
     * @param array $components sql��ɲ���
     * @param int $offset
     * @param int $limit
     * @return string
     */
    public function buildSql($tablename,array $components,$offset=NULL,$limit=NULL){
        return $this->driver->buildSql($tablename,$components,$offset,$limit);
    }
    /**
     * ȡ�����ݱ���ֶ���Ϣ
     * @access public
     * @param $tableName
     * @return array
     */
    public function getFields($tableName){
        return $this->driver->getFields($tableName);
    }

    /**
     * �������ݿ�
     * @param string $dbname ���ݿ�����
     * @return int ��Ӱ�������
     */
    public function createDatabase($dbname){
        return $this->driver->createDatabase($dbname);
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