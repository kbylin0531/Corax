<?php
/**
 * Created by PhpStorm.
 * User: Lin
 * Date: 2015/9/20
 * Time: 11:15
 */
namespace System\Util;
use System\Core\Router;
use System\Exception\CoraxException;
use System\Exception\ParameterInvalidException;
/**
 * Class SEK 系统执行工具(System Execute Kits)
 * 为保证系统运行而设置的通用工具库，开发者也可以使用
 * @package System\Utils
 */
final class SEK {

    /**
     * 合并数组配置(参数二合并到参数一上)
     * @param array $dest 合并目标数组
     * @param array $sourse 并入目标的数组
     * @param bool|false $cover 默认进行遍历覆盖
     * @return void
     * @throws ParameterInvalidException
     */
    public static function merge(array &$dest,array $sourse,$cover=false){
        if($cover){
            $dest = array_merge($dest,$sourse);
        }else{
            foreach($sourse as $key=>$val){
                if(isset($dest[$key]) and is_array($val)){
                    self::merge($dest[$key],$val);
                }else{
                    $dest[$key] = $val;
                }
            }
        }
    }
    /**
     * 获取日期时间
     * @param string $format
     * @param int $timestap
     * @return bool|string false时可能的原因是日期时间格式错误
     */
    public static function date($format='Y-m-d H:i:s',$timestap=null){
        return date($format,$timestap);
    }


    /**
     * 判断是否是重定向链接
     * 判断依据：
     *  ①以http或者https开头
     *  ②以'/'开头的字符串
     * @param string $link 链接地址
     * @return bool
     * @throws CoraxException
     */
    public static function isRedirectLink($link){
        if(is_string($link)){
            $link = trim($link);
            return (0 === strpos($link, 'http')) or (0 === strpos($link,'/')) or (0 === strpos($link, 'https'));
        }else{
            throw new CoraxException('Invalid parameter!');
        }
    }


    /**
     * 将C风格字符串转换成JAVA风格字符串
     * C风格      如： sub_string
     * JAVA风格   如： SubString
     * @param $str
     * @return string
     */
    public static function toJavaStyle($str){
        static $cache = [];
        if(!isset($cache[$str])){
            $cache[$str] = ucfirst(preg_replace_callback('/_([a-zA-Z])/',function($match){return strtoupper($match[1]);},$str));
        }
        return $cache[$str];
    }
    /**
     * JAVA风格字符串转换成将C风格字符串
     * C风格      如： sub_string
     * JAVA风格   如： SubString
     * @param $str
     * @return string
     */
    public static function toCStyle($str){
        static $cache = [];
        if(!isset($cache[$str])) {
            return strtolower(ltrim(preg_replace('/[A-Z]/', '_\\0', $str), '_'));
        }
        return $cache[$str];
    }

    /**
     * 检查或获取开启的PHP扩展
     * @param null|string $extname 扩展名称
     * @return array|bool
     */
    public static function phpExtend($extname=NULL){
        if(isset($extname)){
            //dl($extname) 运行时开启
            return extension_loaded($extname);
        }
        return get_loaded_extensions();
    }
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function encodeHtml($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml   .= "<{$root}{$attr}>";
        $xml   .= self::traslateData2Html($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }
    /**
     * 数据XML编码
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    private static function traslateData2Html($data, $item='item', $id='id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if(is_numeric($key)){
                $id && $attr = " {$id}=\"{$key}\"";
                $key  = $item;
            }
            $xml    .=  "<{$key}{$attr}>";
            $xml    .=  (is_array($val) || is_object($val)) ? self::traslateData2Html($val, $item, $id) : $val;
            $xml    .=  "</{$key}>";
        }
        return $xml;
    }
    /**
     * 判断是否是https请求
     * @return bool
     */
    public static function isHttps(){
        if(!isset($_SERVER['HTTPS']))  return FALSE;
        if($_SERVER['HTTPS'] === 1){  //Apache
            return true;
        }elseif($_SERVER['HTTPS'] === 'on'){ //IIS
            return true;
        }elseif($_SERVER['SERVER_PORT'] == 443){ //其他
            return true;
        }
        return false;
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public static function getClientIP($type = 0,$adv=false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//透过代理的正式IP
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {//客户端IP，如果是通过代理访问则返回代理IP
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
    /**
     * Formats a numbers as bytes, based on size, and adds the appropriate suffix
     * 摘录自：CI_SAE\system\helpers\number_helper.php
     * @access	public
     * @param int $num 待格式化的数据
     * @param int $precision 精度
     * @return string
     */
    public static function byteFormat($num, $precision = 1){
        $unit = 'Bytes';//合适的单位
        if ($num >= 1000000000000){//0.9XX +++
            $num = round($num / 1099511627776, $precision);
            $unit = 'TB';
        }elseif ($num >= 1000000000){
            $num = round($num / 1073741824, $precision);
            $unit = 'GB';
        }elseif ($num >= 1000000){
            $num = round($num / 1048576, $precision);
            $unit = 'MB';
        }elseif ($num >= 1000){
            $num = round($num / 1024, $precision);
            $unit = 'KB';
        }else{
            return number_format($num).' '.$unit;
        }
        return number_format($num, $precision).' '.$unit;
    }

    /**
     * 获得数据的签名，对于数组类型则先排序
     * 测试代码：
$testData01 = array(
    '102'   => array(
        '101'   => 'man',
        '102'   => 'hello ',
    ),
    '101'   => 'man',
);
$testData02 = array(
    '101'   => 'man',
    '102'   => array(
        '102'   => 'hello ',
        '101'   => 'man',
    ),
);
UDK::dump(SEK::dataAuthSign($testData01),SEK::dataAuthSign($testData02),$testData01,$testData02);
     *
     * @param mixed $data 被认证的数据
     * @param bool|false $inner 默认为false，值true留作内部使用
     * @return null|string
     */
    public static function dataAuthSign(&$data,$inner=false) {
        if($inner){
            ksort($data);
            foreach($data as &$value){//缺少将是临时数据
                if(is_array($value)){
                    self::dataAuthSign($value,true);
                }
            }
            return null;
        }else{
            //统一转换为数组类型
            if(!is_array($data)) $data = [$data];
            self::dataAuthSign($data,true);
            return sha1(serialize($data)); //序列化并生成生成签名
        }
    }

    /**
     * 判断是否有不合法的参数存在，不合法的参数参照参数一（使用严格的比较-判断类型）
     * 第一个参数将会被认为是不合法的值，参数一可以是单个字符串或者数组
     * 第二个参数开始是要比较的参数列表，如果任何一个参数"匹配"了参数一，将返回true表示存在不合法的参数
     * @return bool
     */
    public static function checkInvalidValueExistInStrict(){
        $params = func_get_args();
        return self::checkInvalidValueExist($params,true);
    }
    /**
     * 判断是否有不合法的参数存在，不合法的参数参照参数一（使用宽松的比较-不判断类型）
     * 第一个参数将会被认为是不合法的值，参数一可以是单个字符串或者数组
     * 第二个参数开始是要比较的参数列表，如果任何一个参数"匹配"了参数一，将返回true表示存在不合法的参数
     * @return bool
     */
    public static function checkInvalidValueExistInEase(){
        $params = func_get_args();
        return self::checkInvalidValueExist($params);
    }
    /**
     * 检测是否存在不合法的值
     * 参数一种第一个元素作为比较对象
     * 如果是数组，则数组中都是不合法的值，如果是单值，使用===进行比较
     * @param array $params 参与比较的值的有序集合
     * @param bool|false $district 比较时是否判断其类型，默认是
     * @return bool
     */
    public static function checkInvalidValueExist($params,$district=false){
        $invalidVal = array_shift($params);
        foreach ($params as $key=>&$val){
            if(is_array($invalidVal)){
                //参数三决定是否使用严格的方式
                if(in_array(trim($val),$invalidVal,$district)) return true;
            }else{
                if($district? ($invalidVal === $val) : ($invalidVal == $val)) return true;
            }
        }
        return false;
    }
    /**
     * $url规则如：
     *  .../Ma/Mb/Cc/Ad
     * 依次从后往前解析出操作，控制器，模块(如果存在模块将被认定为完整的模块路径)
     * @param string $url 快速创建的URL字符串
     * @param null $mode
     * @param array $params GET参数数组
     * @return string
     */
    public static function url($url=null,array $params=[],$mode=null){
        //解析参数中的$url
        if(!$url){
            return Router::build(null,null,null,$params,$mode);
        }
        $parts = @explode('/',$url);
        //调用URLHelper创建URL
        $action  = array_pop($parts);
        $ctler   = $action?array_pop($parts):null;
        $modules = $ctler?$parts:null;
        return Router::build($modules,$ctler,$action,$params,$mode);
    }
    /**
     * 只替换一次目标中的指定字符串
     * @param $needle
     * @param $replace
     * @param $haystack
     * @return string
     */
    public static function strReplaceJustOnce($needle, $replace, $haystack) {
        $pos = strpos($haystack, $needle);
        return false === $pos?$haystack:substr_replace($haystack, $replace, $pos, strlen($needle));
    }
    /**
     * 重定向
     * @param $url
     * @param int $time
     * @param string $message
     * @return void
     */
    public static function redirect($url,$time=0,$message=''){
        if(headers_sent()){//检查头部是否已经发送
            exit("<meta http-equiv='Refresh' content='{$time};URL={$url}'>$message");
        }else{
            if(0 === $time){
                header('Location: ' . $url);
            }else{
                header("refresh:{$time};url={$url}");
            }
            exit($message);
        }
    }

    /**
     * Determines if the current version of PHP is equal to or greater than the supplied value
     * 判断PHP版本是否大于参数指定的版本
     * @param	string
     * @return	bool	TRUE if the current version is $version or higher
     */
    public static function isPhpVersionTouched($version) {
        static $_is_php;
        $version = (string) $version;
        if ( ! isset($_is_php[$version])) {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }
        return $_is_php[$version];
    }

    /**
     * Remove Invisible Characters
     * 移除看不见的字符
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param	string $str
     * @param	bool $url_encoded
     * @return	string
     */
    public static function removeInvisibleCharacters($str, $url_encoded = TRUE) {
        $non_displayables = array(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S',// 00-08, 11, 12, 14-31, 127
        );
        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
        }

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        }while ($count);
        return $str;
    }
}