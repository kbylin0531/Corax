<?php
namespace System\Library;
/**
 * 3:  * SAE KV 服务 API
 * 4:  *
 * 5:  * @author Chen Lei <simpcl2008@gmail.com>
 * 6:  * @version $Id$
 * 7:  * @package sae
 *
 *
 *
 * 11:  * SAE KV 服务 API
 * 12:  *
 * 13:  * <code>
 * 14:  * <?php
 * 15:  * $kv = new SaeKV();
 * 16:  *
 * 17:  * // 初始化SaeKV对象
 * 18:  * $ret = $kv->init("accesskey"); //访问授权应用的数据
 * 19:  * var_dump($ret);
 * 20:  *
 * 21:  * // 增加key-value
 * 22:  * $ret = $kv->add('abc', 'aaaaaa');
 * 23:  * var_dump($ret);
 * 24:  *
 * 25:  * // 更新key-value
 * 26:  * $ret = $kv->set('abc', 'bbbbbb');
 * 27:  * var_dump($ret);
 * 28:  *
 * 29:  * // 替换key-value
 * 30:  * $ret = $kv->replace('abc', 'cccccc');
 * 31:  * var_dump($ret);
 * 32:  *
 * 33:  * // 获得key-value
 * 34:  * $ret = $kv->get('abc');
 * 35:  * var_dump($ret);
 * 36:  *
 * 37:  * // 删除key-value
 * 38:  * $ret = $kv->delete('abc');
 * 39:  * var_dump($ret);
 * 40:  *
 * 41:  * // 一次获取多个key-values
 * 42:  * $keys = array();
 * 43:  * array_push($keys, 'abc1');
 * 44:  * array_push($keys, 'abc2');
 * 45:  * array_push($keys, 'abc3');
 * 46:  * $ret = $kv->mget($keys);
 * 47:  * var_dump($ret);
 * 48:  *
 * 49:  * // 前缀范围查找key-values
 * 50:  * $ret = $kv->pkrget('abc', 3);
 * 51:  * var_dump($ret);
 * 52:  *
 * 53:  * // 循环获取所有key-values
 * 54:  * $ret = $kv->pkrget('', 100);
 * 55:  * while (true) {
 * 56:  *    var_dump($ret);
 * 57:  *    end($ret);
 * 58:  *    $start_key = key($ret);
 * 59:  *    $i = count($ret);
 * 60:  *    if ($i < 100) break;
 * 61:  *    $ret = $kv->pkrget('', 100, $start_key);
 * 62:  * }
 * 63:  *
 * 64:  * // 获取选项信息
 * 65:  * $opts = $kv->get_options();
 * 66:  * print_r($opts);
 * 67:  *
 * 68:  * // 设置选项信息 (关闭默认urlencode key选项)
 * 69:  * $opts = array('encodekey' => 0);
 * 70:  * $ret = $kv->set_options($opts);
 * 71:  * var_dump($ret);
 * 72:  *
 * 73:  * </code>
 * 74:  *
 * 75:  * 错误代码及错误提示消息：
 * 76:  *
 * 77:  *  - 0  "Success"
 * 79:  *  - 10 "AccessKey Error"
 * 80:  *  - 20 "Failed to connect to KV Router Server"
 * 81:  *  - 21 "Get Info Error From KV Router Server"
 * 82:  *  - 22 "Invalid Info From KV Router Server"
 * 83:  *
 * 84:  *  - 30 "KV Router Server Internal Error"
 * 85:  *  - 31 "KVDB Server is uninited"
 * 86:  *  - 32 "KVDB Server is not ready"
 * 87:  *  - 33 "App is banned"
 * 88:  *  - 34 "KVDB Server is closed"
 * 89:  *  - 35 "Unknown KV status"
 * 90:  *
 * 91:  *  - 40 "Invalid Parameters"
 * 92:  *  - 41 "Interaction Error (%d) With KV DB Server"
 * 93:  *  - 42 "ResultSet Generation Error"
 * 94:  *  - 43 "Out Of Memory"
 * 95:  *  - 44 "SaeKV constructor was not called"
 * 96:  *  - 45 "Key does not exist"
 * 97:  *
 * 98:  * @author Chen Lei <simpcl2008@gmail.com>
 * 99:  * @version $Id$
 * 100:  * @package sae
 * 101:  */
class SaeKV
{
    const EMPTY_PREFIXKEY = '';
    const MAX_MGET_SIZE = 32;
    const MAX_PKRGET_SIZE = 100;
    const MAX_KEY_LENGTH = 200;
    const MAX_VALUE_LENGTH = 4194304;

    /**
     * 构造函数
     * @param int $timeout KV操作超时时间，默认为3000ms
     */
    function __construct($timeout = 3000)
    {
    }

    /**
     * 初始化Sae KV 服务
     * @param string $accesskey 若不加参数，则使用本应用的Kvdb数据，若传入被授权应用的AccessKey，则使用被授权应用的Kvdb
     * @return bool
     */
    function init($accesskey = "")
    {
    }

    /**
     * 获得key对应的value
     * 时间复杂度 O(log N)
     * @param string $key 长度小于MAX_KEY_LENGTH字节
     * @return string|bool 成功返回value值，失败返回false
     */
    function get($key)
    {
    }

    /**
     * 更新key对应的value
     * @param string $key 长度小于MAX_KEY_LENGTH字节，当不设置encodekey选项时，key中不允许出现非可见字符
     * @param string $value 长度小于MAX_VALUE_LENGTH
     * @return bool 成功返回true，失败返回false
     */
    function set($key, $value)
    {
    }

    /**
     * 增加key-value对，如果key存在则返回失败
     * @param string $key 长度小于MAX_KEY_LENGTH字节，当不设置encodekey选项时，key中不允许出现非可见字符
     * @param string $value 长度小于MAX_VALUE_LENGTH
     * @return bool 成功返回true，失败返回false
     */
    function add($key, $value)
    {
    }

    /**
     * 替换key对应的value，如果key不存在则返回失败
     * @param string $key 长度小于MAX_KEY_LENGTH字节，当不设置encodekey选项时，key中不允许出现非可见字符
     * @param string $value 长度小于MAX_VALUE_LENGTH
     * @return bool 成功返回true，失败返回false
     */
    function replace($key, $value)
    {
    }

    /**
     * 删除key-value
     * @param @param string $key 长度小于MAX_KEY_LENGTH字节
     * @return bool 成功返回true，失败返回false
     */
    function delete($key)
    {
    }

    /**
     * 批量获得key-values
     * @param array $ary 一个包含多个key的数组，数组长度小于等于MAX_MGET_SIZE
     * @return array|bool 成功返回key-value数组，失败返回false
     */
    function mget($ary)
    {
    }

    /**
     * 前缀范围查找key-values
     * @param string $prefix_key 前缀，长度小于MAX_KEY_LENGTH字节
     * @param int $count 前缀查找最大返回的key-values个数，小于等于MAX_PKRGET_SIZE
     * @param string $start_key 在执行前缀查找时，返回大于该$start_key的key-values；默认值为空字符串（即忽略该参数）
     * @return array|bool 成功返回key-value数组，失败返回false
     */
    function pkrget($prefix_key, $count, $start_key)
    {
    }

    /**
     * 获得错误代码
     * @return int 返回错误代码
     */
    function errno()
    {
    }

    /**
     * 获得错误提示消息
     * @return string 返回错误提示消息字符串
     */
    function errmsg()
    {
    }

    /**
     * 获取选项值
     * array(1) {
     *   "encodekey" => 1 // 默认为1
     *                    // 1: 使用urlencode编码key；0：不使用urlencode编码key
     * }
     * @return array 成功返回选项数组，失败返回false
     */
    function get_options()
    {
    }

    /**
     * 设置选项值
     * @param array $options array (1) {
     *   "encodekey" => 1 // 默认为1
     *                    // 1: 使用urlencode编码key；0：不使用urlencode编码key
     * }
     * @return bool 成功返回true，失败返回false
     */
    function set_options($options)
    {
    }
}