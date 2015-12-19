<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/8/16
 * Time: 10:17
 */
namespace System\Core;
use System\Util\SEK;

defined('BASE_PATH') or die('No Permission!');
/**
 * Class Model 模型类
 * 一个模型对应一个数据表
 * 完成基本的CURD操作
 * @package System\Core
 */
class Model{

    /**
     * 数据操作对象
     * @var Dao
     */
    protected $dao = null;

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
     * 默认的表前缀
     * @var string
     */
    protected $prefix = '';

    /**
     * 主键，可以是复合主键(数组)
     * @var string|array
     */
    protected $primar_key = 'id';

    /**
     * 实际的对应的数据表的名称
     * @var string
     */
    protected $real_tablename = null;

    /**
     * 模型名称，不包含命名空间部分和Model后缀
     * @var string
     */
    protected $model_name = null;

    /**
     * 模型所属模块名称
     * @var string
     */
    protected $modules_name = null;

    /**
     * 构造函数
     * @param array $config 模型配置选项(array)
     * @throws \Exception
     */
    public function __construct($config=null){
        $matches = null;
        if(preg_match('/^Application\\\(.*)\\\Model\\\(.*)Model$/',get_called_class(),$matches)){
            $this->modules_name = str_replace('\\','/',$matches[1]);
            $this->model_name = $matches[2];
        }else{
            throw new \Exception('Class "'.get_called_class().'" auto fetch falied!');
        }
        if(isset($config)){
            //动态设置属性
            foreach($config as $name => $item){
                $this->$name = $item;
            }
        }else{
            //默认设置的情况下
            if(!isset($this->real_tablename)){
                $modelname = SEK::toJavaStyle($matches[2]);
                $this->setTableName($modelname);
            }
        }
    }

    /**
     * 设置表的名称
     * @param string $tablename 数据表名称，不带前缀
     * @param bool $autofill_prefix 是否自动填充前缀
     * @return void
     */
    protected function setTableName($tablename,$autofill_prefix=true){
        if($autofill_prefix and $this->prefix and 0 !== strpos($tablename,$this->prefix)){//不是以前缀开头，自动添加前缀
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
     * @param string|array $config 连接配置标识符 或者 配置数组
     * @return void
     */
    public function init($config='0'){
        $this->dao = Dao::getInstance($config);
    }

    /**
     * 获取查询出错信息
     * @return string
     */
    public function getErrorInfo(){
        return isset($this->dao)? $this->dao->getErrorInfo() : '';
    }

    /**
     * 执行一段查询SQL
     * @param string$sql
     * @param array $inputs
     * @return array|false 返回数组结果集合,返回false表示执行失败
     */
    public function query($sql,array $inputs=null){
        isset($this->dao) or $this->init();
        $rst = $this->dao->prepare($sql)->execute($inputs);
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
     * 插入数据库记录
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
     * 更新记录
     * @param mixed $fields
     * @param mixed $where
     * @param string $tablename
     * @return int|string 受影响行数或者错误信息
     * @throws \Exception
     */
    public function update($fields=null,$where=null,$tablename=null){
        isset($this->dao) or $this->init();
        isset($tablename) or $tablename = $this->getTableName();
        return $this->dao->update($tablename,$fields,$where);
    }

    /**
     * 获取记录
     * @param mixed $fields
     * @param mixed $where
     * @param string $tablename
     * @return array|bool
     */
    public function select($fields=null,$where=null,$tablename=null){
        isset($this->dao) or $this->init();
        isset($tablename) or $tablename = $this->getTableName();
        return $this->dao->select($tablename,$fields,$where);
    }

    /**
     * 获取一条记录
     * 如果记录有多条，需要使用select进行获取，否则获取结果数目不等于1时会返回string类型错误信息
     * @param mixed $fields
     * @param mixed $where
     * @param string $tablename
     * @return array|bool
     */
    public function find($fields=null,$where=null,$tablename=null){
        isset($this->dao) or $this->init();
        isset($tablename) or $tablename = $this->getTableName();
        $rst = $this->dao->select($tablename,$fields,$where);
        return count($rst) !== 1? false:$rst[0];
    }

    /**
     * 删除记录
     * @param mixed $where
     * @param string $tablename
     * @return int|string
     */
    public function delete($where=null,$tablename=null){
        isset($this->dao) or $this->init();
        isset($tablename) or $tablename = $this->getTableName();
        return $this->dao->delete($tablename,$where);
    }
}