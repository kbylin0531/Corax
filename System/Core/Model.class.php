<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/16
 * Time: 10:17
 */
namespace System\Core;
use System\Exception\CoraxException;
use System\Util\SEK;

defined('BASE_PATH') or die('No Permission!');
/**
 * Class Model 模型类
 * @package System\Core
 */
class Model{

    /**
     * 数据操作对象
     * @var Dao
     */
    protected $dao = null;

    /**
     * 模型的惯例配置
     * @var array
     */
    protected static $convention = [
        //数据表的默认前缀
        'DEFAULT_TABLE_PREFIX'  => 'co_',
        //默认的主键名称
        'DEFAULT_PRIMAR_KEY'    => 'id',
    ];

    /**
     * SQL各个组成部分
     * @var array
     */
    protected $components = [];
    /**
     * 输入绑定参数
     * @var array
     */
    protected $input_params = [];

    /**
     * 开关
     * @var array
     */
    protected static $_switcher = array(
        'SHOW_ERROR' => true,//决定是否向用户显示错误信息
    );

    /**
     * 字段映射
     * 将表单提交的字段映射为数据库对应的字段名称
     * 例如：
     *  array(
     *      'key' => 'value'，//把表单中'key'映射到数据表的'value'字段
     *  );
     * @var array
     */
    protected $mapping = [];

    /**
     * 字段信息
     * @var array
     */
    protected $fields = [];

    /**
     * 表前缀
     * @var string
     */
    protected $prefix = '';
    /**
     * 实际的对应的数据表的名称
     * @var string
     */
    protected $real_tablename = null;

    /**
     * 主键，可以是复合主键(数组)
     * @var string|array
     */
    protected $primar_key = 'id';

    /**
     * 模型所属模块名称
     * @var string
     */
    protected $modulesname = null;
    /**
     * 模型名称，不包含命名空间部分和Model后缀
     * @var string
     */
    protected $modelname = null;

    /**
     * 构造函数
     * @param array $config 模型配置选项(array)
     * @throws CoraxException
     */
    public function __construct(array $config=null){
        //自动识别模型所属模块和模型类名称
        $matches = null;
        $currentModelName = get_called_class();
        if(preg_match('/^Application\\\(.*)\\\Model\\\(.*)Model$/',$currentModelName,$matches)){
            $this->modulesname = str_replace('\\','/',$matches[1]);
            $this->modelname = $matches[2];
        }else{/*不匹配说明是Model类*/}

        //加载模型配置
        SEK::merge(self::$convention,Configer::load('database'));

        $modelname = SEK::toCStyle($matches[2]);
        $this->setTableName($modelname);

        if(isset($config)){
            //动态设置属性
            foreach($config as $name => $item){
                $this->$name = $item;
            }
        }else{
            //默认设置的情况下
            if(null === $this->real_tablename){
            }
        }
        $this->reset();
    }


    /**
     * 重置组件和input_params设置
     */
    public function reset(){
        $this->components = [
            /**
             * 用于告诉SQL服务器返回唯一不同的值的集合
             */
            'distinct'  =>  null,
            /**
             * 不同的数据库用法是不同的
             * SQL server   :SELECT TOP number|percent column_name(s) FROM table_name
             * MySQL        :SELECT column_name(s) FROM table_name LIMIT number
             * Oracel       :SELECT column_name(s) FROM table_name WHERE ROWNUM <= number
             */
            'top'           => null,
            'top_percent'   => false,//针对MSSQL有效
            /**
             * 表示获取表的列的名称
             */
            'fields'=>' * ', //查询的表域情况
            'table' => null,
            'join'  => null,     //join部分，需要带上join关键字
            'where' => null, //where部分
            'group' => null, //分组 需要带上group by
            'having'=> null,//having子句，依赖$group存在，需要带上having部分
            'order' => null,//排序，不需要带上order by
            /**
             * 注意limit可能会与top在MYSQL数据库中使用冲突(两个limit)
             * Oracel和MSSQL无法使用limit关键字
             * Mysql中limit放到最后
             * 使用offset的前提是limit不为null
             */
            'offset'=> null,
            'limit' => null,
        ];
        $this->input_params = [];
    }
    /**
     * 设置表的名称
     * @param string $tablename 数据表名称，不带前缀
     * @param bool $autofill_prefix 是否自动检查并填充前缀
     * @return void
     */
    protected function setTableName($tablename,$autofill_prefix=true){
        if($autofill_prefix and $this->prefix and 0 !== stripos($tablename,$this->prefix)){//不是以前缀开头，自动添加前缀
            $this->real_tablename = $this->prefix.$tablename;
        }else{
            $this->real_tablename = $tablename;
        }
    }

    /**
     * 获取表名称
     * @return string
     */
    protected function getTableName(){
        return $this->real_tablename;
    }

    /**
     * 初始化连接配置
     * @param string|array $identifier 连接配置标识符 或者 配置数组
     * @return void
     */
    public function init($identifier=null){
        $this->dao = Dao::getInstance($identifier);
    }

//------------------ 常用操作 --------------------------------------------------------------//
    /**
     * 执行一段查询SQL
     * @param string|array $mixedParams 为string类型时表示直接查询的SQL语句，为array类型时表示绑定的输入参数数组，此时返回链式操作的结果
     * @param array|null $inputs 如果参数1是string类型，则参数二代表输入的参数
     * @return array|false 返回数组结果集合,返回false表示执行失败
     */
    public function query($mixedParams,array $inputs=null){
        isset($this->dao) or $this->init();
        if(is_array($mixedParams)){
            //链式操作结果
            $mixedParams = $this->dao->compile($mixedParams);
        }
        $rst = $this->dao->prepare($mixedParams)->execute($inputs);
        if(false === $rst){
            return false;
        }
        return $this->dao->fetchAll();
    }

    /**
     * 执行一段受影响SQL
     * @param string $sql
     * @param array $inputs
     * @return bool|int 返回受影响行数,返回false表示执行失败
     */
    public function execute($sql,array $inputs=null){
        isset($this->dao) or $this->init();
        $rst = $this->dao->prepare($sql)->execute($inputs);
        if(false === $rst){
            return false;
        }
        return $this->dao->rowCount();
    }

    /**
     * 获取查询出错信息
     * @return string
     */
    public function getErrorInfo(){
        return isset($this->dao)? $this->dao->getErrorInfo() : '';
    }

//----------------- 链式操作的方法 ----------------------------------------------------------//
    /**
     * 表示是否设置distinct
     * @param bool $isdist
     * @return $this 用于链式调用
     */
    public function distinct($isdist=null){
        null !== $isdist and $this->components['distinct'] = ' DISTINCT ';
        return $this;
    }

    /**
     * 表示是否设置top
     * @param null|int $number 获取的数量
     * @param bool $isPercent
     * @return $this 用于链式调用
     */
    public function top($number=null,$isPercent=false){
        null !== $number and $this->components['top'] = $number;
        $isPercent and $this->components['top_percent'] = true;
        return $this;
    }

    /**
     * 设置获取的表域
     * @param null|string|array $fields 不设置参数时获取全部
     * @return $this
     * @throws CoraxException
     */
    public function fields($fields=null){
        if(null !== $fields){
            if(is_array($fields)){
                //默认全部转义
                array_map(function($item){
                    return $this->dao->escape($item);
                },$fields);
                $fields = implode(',',$fields);
            }elseif(!is_string($fields)){
                throw new CoraxException("Require the parameter which type is 'array' or 'string'");
            }
            $this->components['fields'] = $fields;
        }
        return $this;
    }

    /**
     * 设置操作的表的名称
     * @param string $tablename 数据表的名称
     * @return $this
     */
    public function table($tablename){
        $this->components['table'] = $tablename;
        return $this;
    }

    /**
     * 设置join部分
     * @param null|string $join
     * @return $this
     */
    public function join($join = null){
        null !== $join and $this->components['join'] = $join;
        return $this;
    }

    /**
     * 设置where部分
     * @param null|string $where
     * @return $this
     */
    public function where($where = null){
        null !== $where and $this->components['where'] = $where;
        return $this;
    }
    /**
     * 设置group部分
     * @param null|string $group
     * @return $this
     */
    public function group($group = null){
        null !== $group and $this->components['group'] = $group;
        return $this;
    }
    /**
     * 设置having部分
     * @param null|string $having
     * @return $this
     */
    public function having($having = null){
        null !== $having and $this->components['having'] = $having;
        return $this;
    }

    /**
     * 设置order部分
     * @param null|string $order
     * @return $this
     */
    public function order($order = null){
        null !== $order and $this->components['order'] = $order;
        return $this;
    }

    /**
     * 设置偏移和数量限制
     * @param null $limit
     * @param null $offset
     * @return $this
     */
    public function limit($limit=null,$offset=null){
        null !== $limit  and $this->components['limit']  = $limit;
        null !== $offset and $this->components['offset'] = $offset;
        return $this;
    }

    /**
     * 变异组件并返回SQL语句和绑定参数
     * @param bool|true $clear 变异完成后是否清空，默认清空
     * @return string
     * @throws CoraxException
     */
    public function compile($clear=true){
        if($this->components['table']){
            throw new CoraxException('Empty table is invalid!');
        }
        $sql = $this->dao->compile($this->components);
        $bind = $this->input_params;
        $clear and $this->reset();
        return [$sql,$bind];
    }

//----------------- 基本操作，同时可以用于链式操作  ----------------------------------------------------------//
    /**
     * 创建数据对象
     * @param array $fields
     * @param string $tablename
     * @return int|string
     * @throws \Exception
     */
    public function create(array $fields,$tablename=null){
        isset($this->dao) or $this->init();
        isset($tablename) or $tablename = $this->getTableName();
        return $this->dao->create($tablename,$fields);
    }

    /**
     * 插入数据库记录
     * @param array|null $fields
     * @param null $tablename
     * @return bool|int 返回false表示发生了错误，返回int表示收到影响的行的数目
     * @throws CoraxException
     */
    public function insert(array $fields=null,$tablename=null){
        $this->dao or $this->init();
        null === $fields    and $fields = $this->components['fields'];
        null === $tablename and $tablename = $this->getTableName();
        $piled = $this->dao->create($tablename,$fields);
        return $this->execute($piled[0],$piled[1]);
    }

    /**
     * 更新记录
     * @param mixed $fields
     * @param mixed $where
     * @param string $tablename
     * @return int|string 受影响行数或者错误信息
     * @throws \Exception
     */
    public function update($fields=null,$where=null,$tablename=null){
        $this->dao or $this->init();
        null === $fields    and $fields = $this->components['fields'];
        null === $tablename and $tablename = $this->getTableName();
        null === $where     and $where = $this->components['where'];
        return $this->dao->update($tablename,$fields,$where);
    }

    /**
     * 获取记录
     * @param string|array $fields
     * @param string|array $where
     * @param string $tablename
     * @return array|bool
     */
    public function select($fields=null,$where=null,$tablename=null){
        isset($this->dao) or $this->init();
        null === $fields    and $fields = $this->components['fields'];
        null === $tablename and $tablename = $this->getTableName();
        null === $where     and $where = $this->components['where'];
        return $this->dao->select($tablename,$fields,$where);
    }

    /**
     * 获取一条记录
     * 如果记录有多条，需要使用select进行获取，否则获取结果数目不等于1时会返回string类型错误信息
     * 调用这个方法时需要知道的是这个方法只会返回单条记录，如果不是单条记录或者查询出错都会返回false
     * @param mixed $fields
     * @param mixed $where
     * @param string $tablename
     * @return array|bool
     */
    public function find($fields=null,$where=null,$tablename=null){
        $rst = $this->dao->select($tablename,$fields,$where);
        return (false === $rst or count($rst) !== 1)? false : $rst[0];
    }

    /**
     * 删除记录
     * @param mixed $where
     * @param string $tablename
     * @return int|string
     */
    public function delete($where=null,$tablename=null){
        isset($this->dao) or $this->init();
        null === $tablename and $tablename = $this->getTableName();
        null === $where     and $where = $this->components['where'];
        return $this->dao->delete($tablename,$where);
    }



}