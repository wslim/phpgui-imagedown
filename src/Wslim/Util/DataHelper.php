<?php
namespace Wslim\Util;

/**
 * DataHelper 常用的数据处理方法
 * 
 * 1. filter        过滤类方法
 * 2. addslashes, html_entities 转义类方法
 * 3. verify_xxx    验证类方法
 * 4. nomalize_xxx  规范化方法
 * 5. uuid, random, serial, hash, password  生成类方法
 * 6. toXml, fromXml 方法
 * 7. toTree 方法
 * 
 * 
 * @author 28136957@qq.com
 * @link   wslim.cn
 */
class DataHelper
{
    /**
     * 获取或设置全局转码启用状态
     * @return boolean
     */
    static public function global_escape_enabled($enabled=null)
    {
        if ($enabled !== null) {
            defined('ENABLED_ESCAPE') ? define('ENABLED_ESCAPE', $enabled) : null;
        }
        
        return defined('ENABLED_ESCAPE') ? constant('ENABLED_ESCAPE') : true;
    }
    
    /***************************************************************************
     * 过滤类方法, filter_xxx()
     ***************************************************************************/
    
    /**
     * @param string $str
     * @return string
     */
    static public function filter_sql($str)
    {
        return $str;
    }
    
    /**
     * 过滤不安全字符，适用于用户名, url, image, 限制级标题, 关键词等通用型字串安全过滤.
     *
     * @param string $str
     * @return string
     */
    static public function filter_unsafe_chars($str)
    {
        $str = str_replace('%20','',$str);
        $str = str_replace('%27','',$str);
        $str = str_replace('%2527','',$str);
        $str = str_replace('*','',$str);
        $str = str_replace('"','&quot;',$str);
        $str = str_replace("'",'',$str);
        $str = str_replace('"','',$str);
        $str = str_replace(';','',$str);
        $str = str_replace('<','&lt;',$str);
        $str = str_replace('>','&gt;',$str);
        $str = str_replace("{",'',$str);
        $str = str_replace('}','',$str);
        $str = str_replace('\\','',$str);
        return $str;
    }
    
    /**
     * 过滤ASCII码从0-28的控制字符，适用于通用型字串过滤.
     * @return String
     */
    static public function filter_control_chars($str) {
        $rule = '/[' . chr ( 1 ) . '-' . chr ( 8 ) . chr ( 11 ) . '-' . chr ( 12 ) . chr ( 14 ) . '-' . chr ( 31 ) . ']*/';
        return str_replace ( chr ( 0 ), '', preg_replace ( $rule, '', $str ) );
    }
    
    /**
     * xss过滤函数，过滤掉脚本等相关的代码，适用于允许html内容但不支持脚本运行的类型.
     * 应用场合：标题、关键词、image, url, callback 等都需要进行此过滤
     *
     * @param $string
     * @return string
     */
    static public function filter_xss($str) {
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
        $parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $parm = array_merge($parm1, $parm2);
        
        for ($i = 0; $i < sizeof($parm); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($parm[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0([9][a][b]);?)?';
                    $pattern .= '|(&#0([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $parm[$i][$j];
            }
            $pattern .= '/i';
            $str = preg_replace($pattern, ' ', $str);
        }
        return $str;
    }
    
    /**
     * filter html
     * @param  string $value
     * @return string
     */
    static public function filter_html($value)
    {
        $value = strip_tags(static::html_entity_decode($value));
        if (static::global_escape_enabled()) $value = static::addslashes(trim($value));
        
        return $value;
    }
    
    /***************************************************************************
     * 转义相关方法
     ***************************************************************************/
    /**
     * 转义 javascript 代码标记，适用于不允许html内容的类型.
     * 应用场合：对于url,callback等类型值使用
     *
     * @param  string|array $str
     * @return mixed
     */
    static public function escape_script($str) {
        if(is_array($str)){
            foreach ($str as $key => $val){
                $str[$key] = escape_script($val);
            }
        }else{
            $str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
            $str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
            $str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
            $str = str_replace ( 'javascript:', 'javascript：', $str );
        }
        return $str;
    }
    
    /**
     * 转义引号，支持数组
     * @param  string|array $value
     * @return mixed
     */
    static public function addslashes($value, $force = 0)
    {
        if(!get_magic_quotes_gpc() || $force) {
            if(is_array($value)) {
                foreach($value as $key => $val) {
                    $value[$key] = static::addslashes($val, $force);
                }
            } elseif (is_string($value)) {
                $value = addslashes($value);
            }
        }
        return $value;
    }
    
    /**
     * 去除转义引号，支持数组
     * @param string|array $value
     * @return mixed
     */
    static public function stripslashes($value) {
        if(!is_array($value)) return stripslashes($value);
        foreach($value as $key => $val) $string[$key] = static::stripslashes($val);
        return $value;
    }
    
    /**
     * html entities
     * @param  string $str
     * @return string
     */
    static public function html_entities($str) {
        $encoding = 'utf-8';
        //if(strtolower(CHARSET)=='gbk') $encoding = 'ISO-8859-15';
        return htmlspecialchars($str, ENT_QUOTES, $encoding);
    }
    
    /**
     * 反转义html字符
     * @param  string $string
     * @return string
     */
    static public function html_entity_decode($string) {
        $encoding = 'utf-8';
        //return html_entity_decode($string, ENT_QUOTES, $encoding);
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }
    
    /**
     * sql string encode
     * @param  string $value
     * @return string
     */
    static public function sql_encode($value)
    {
        $value = str_replace("_", "\_", $value); // 把 '_'过滤掉
        $value = str_replace("%", "\%", $value); // 把 '%'过滤掉
        
        return $value;
    }
    
    /***************************************************************************
     * 验证类相关方法 verify_xxx()
     ***************************************************************************/
    /**
     * 进行regex验证
     * @param string $regex
     * @param string $value
     * @param string $option
     * @return boolean
     */
    static public function verify_regex($regex, $value, $option='')
    {
        return (bool) preg_match('/' . $regex . '/' . $option, $value);
    }
    
    /**
     * 验证标识符，仅允许字母数字下划线和反斜线
     * @param  string $str
     * @return boolean
     */
    static public function verify_identifier($str)
    {
        $regex = '^[a-z0-9_\/]+$';
        return static::verify_regex($regex, $str, 'i');
    }
    
    /**
     * 验证标识名称，仅允许字母、数字、下划线、横线、反斜线、冒号
     * @param  string $str
     * @return boolean
     */
    static public function verify_code($str)
    {
        $regex = '^[a-z0-9_\/\-\:]+$';
        return static::verify_regex($regex, $str, 'i');
    }
    
    /**
     * 进行email验证
     * @param  string $str
     * @return boolean
     */
    static public function verify_email($str)
    {
        //$regex = '^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';
        $regex = '^[a-zA-Z0-9.!#$%&_~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';
        return static::verify_regex($regex, $str);
    }
    /**
     * 进行username验证，符合返 true
     * @param  string $str
     * @return boolean
     */
    static public function verify_username($str)
    {
        if (is_numeric($str)) {
            return true;
        } elseif (static::verify_email($str) ) {
            return true;
        } else {
            $regex = "^[a-z0-9_\!\@\#\$\%\&\*\_\-\=]+$";
            return static::verify_regex($regex, $str, 'i');
        }
    }
    /**
     * 进行sql验证
     * @param  string $str
     * @return boolean
     */
    static public function verify_sql_inject($str)
    {
        $regex = 'select|insert|and|or|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile';
        return ! static::verify_regex($regex, $str);
    }
    
    /***************************************************************************
     * 规范化相关方法
     ***************************************************************************/
    /**
     * 规范化数字格式
     * @param  string $value
     * @return string
     */
    static public function normalize_number($value)
    {
        return preg_replace('/[^0-9]+/S', '', $value);
    }
    
    /**
     * 规范化英文名称输入，code类型只允许 a-zA-Z0-9_\/\-
     * @param  string $value
     * @return string
     */
    static public function normalize_code($value)
    {
        return preg_replace('/[^a-zA-Z0-9_\/\-\:]+/S', '', $value);
    }
    
    /**
     * 规范化title类型输入
     * @param string  $value
     * @return string
     */
    static public function normalize_title($value)
    {
        if (static::global_escape_enabled()) $value = static::addslashes(trim($value));
        if (is_array($value)) $value = static::json_encode($value);
        return static::html_entities($value);
    }
    
    /**
     * 反规范化title类型输入
     * @param string  $value
     * @return string
     */
    static public function unnormalize_title($value)
    {
        // 先去除转义，如果开启全局转义会先转义，这里需要先去除
        if (static::global_escape_enabled()) $value = static::stripslashes($value);
        return static::html_entity_decode($value);
    }
    
    /**
     * 规范化文本数据类型，适用于post['content']
     * @param  mixed $value string|array
     * @return mixed string|array
     */
    static public function normalize_text($value, $is_html=false)
    {
        
        if (is_string($value)) {
            // 先转义，如果开启全局转义会先转义
            if (static::global_escape_enabled()) $value = static::addslashes(trim($value));
            
            // 规范化处理
            $value = str_replace("_", "\_", $value); // 把 '_'过滤掉
            $value = str_replace("%", "\%", $value); // 把 '%'过滤掉
            //$str = nl2br($str); // 回车转换
            
            if (!$is_html) {
                $value = static::html_entities($value); // html标记转换
            }
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::normalize_text($v, $is_html);
            }
        }
        return $value;
    }
    
    /**
     * 反规范化text类型输入，反转义html内容
     * @param  mixed  $value string|array
     * @return string
     */
    static public function unnormalize_text($value)
    {
        if (is_string($value)) {
            if (static::global_escape_enabled()) $value = static::stripslashes($value);
            $value = static::html_entity_decode($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::unnormalize_text($v);
            }
        }
        return $value;
    }
    
    /**
     * 规范化url类型输入
     * @param  string  $value
     * @return string
     */
    static public function normalize_url($str)
    {
        $str = urldecode($str);
        if (static::global_escape_enabled()) $str = static::addslashes($str);
        return static::escape_script(static::filter_unsafe_chars($str));
    }
    
    /**
     * normalize path
     * @param  string  $str
     * @return string
     */
    static public function normalize_path($str)
    {
        $str = preg_replace('/\s+/u', '_', $str);
        //$str = preg_replace('/[^a-z\-\_\:\/\\\\\x{4e00}-\x{9fa5}]/iu', '', $str);
        $str = str_replace(["..", "\\\\", "\\"], ["", "/", "/"], $str);
        return $str;
    }
    
    /**
     * 格式化搜索字串
     * @param  string $value
     * @return string
     */
    static public function normalize_search($value)
    {
        $value = static::filter_xss($value);
        $value = static::sql_encode($value);
        
        return $value;
    }
    
    /**
     * 序列化内容
     * @param  string|array $value
     * @return string
     */
    static public function serialize($value)
    {
        return isset($value) && !is_string($value) ? serialize($value) : $value;
    }
    
    /**
     * 反序列化内容
     * @param  string $value
     * @return array
     */
    static public function unserialize($value=null)
    {
        return unserialize(static::unnormalize_text($value));
    }
    
    /**
     * json_encode
     * @param mixed  $value string|array
     * @return string
     */
    static public function json_encode($value)
    {
        return isset($value) && is_array($value) ? json_encode(static::normalize_text($value)) : $value;
    }
    
    /**
     * json_decode
     * @param  string  $value
     * @return array
     */
    static public function json_decode($value)
    {
        if (empty($value)) return $value;
        $jvalue = is_string($value) && strpos($value, '{') !== false  ? static::unnormalize_text(json_decode($value, true)) : null;
        return is_null($jvalue) ? $value : $jvalue;
    }
    
    /**
     * urlencode编码处理
     * @param  array|string $value
     * @return array|string
     */
    static public function urlencode($value)
    {
        if (is_string($value)) {
            return urlencode($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::urlencode($v);
            }
        }
        return $value;
    }
    
    /**
     * urldecode解码处理
     * @param  array|string $value
     * @return array|string
     */
    static public function urldecode($value)
    {
        if (is_string($value)) {
            return urldecode($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::urldecode($v);
            }
        }
        return $value;
    }
    
    /**
     * from str to unicode format \uAAAA
     * @param  string $name
     * @param  string $prefix
     * @param  string $parse_alpha
     * @param  string $exclude_str
     * @return string
     */
    function unicode_encode($str, $prefix='\u', $parse_alpha=true, $exclude_str=null)
    {
        if ($prefix) {
            if (strpos($prefix, '\\') !== 0) {
                $prefix = '\\' . $prefix;
            }
            
            if ($prefix !== '\\u' && $prefix !== '\\x') {
                $prefix = '\\u';
            }
        }
        $str = iconv('UTF-8', 'UCS-2', $str);
        $len = strlen($str);
        $newStr = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c  = $str[$i];
            $c2 = $str[$i + 1];
            if (ord($c) > 0)
            {    // 两个字节的文字
                $newStr .= '\\u' . strtoupper(base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16));
            }
            else
            {
                if ($exclude_str && strpos($exclude_str, $c2) !== false) {
                    $newStr .= $c2;
                } else {
                    if ($prefix == '\\u') {
                        $newStr .= $parse_alpha ? '\\u00' . strtoupper(bin2hex($c2)) : $c2;
                    } else {
                        $newStr .= $parse_alpha ? '\\x' . strtoupper(bin2hex($c2)) : $c2;
                    }
                }
            }
        }
        return $newStr;
    }
    
    /**
     * 转换编码，将Unicode编码转换成可以浏览的utf-8编码
     * @param  string $str
     * @return string
     */
    function unicode_decode($str)
    {
        
        $pattern = '/(?:\\\u(?:[\w]{4}))|(?:\\\x(?:[\w]{2}))|./i';
        preg_match_all($pattern, $str, $matches);
        if (!empty($matches))
        {
            $str = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $unistr = $matches[0][$j];
                if (strpos($unistr, '\\u') === 0)
                {
                    $code = base_convert(substr($unistr, 2, 2), 16, 10);
                    $code2 = base_convert(substr($unistr, 4), 16, 10);
                    $c = chr($code).chr($code2);
                    $c = iconv('UCS-2', 'UTF-8', $c);
                    $str .= $c;
                }
                elseif (strpos($unistr, '\\x') === 0)
                {
                    $code = base_convert(substr($unistr, 2, 1), 16, 10);
                    $code2 = base_convert(substr($unistr, 3), 16, 10);
                    if ($code > 7) {
                        $c = chr($code).chr($code2);
                        $c = iconv('UCS-2', 'UTF-8', $c);
                    } else {
                        $c = chr(hexdec(substr($unistr, 2, 2)));
                    }
                    $str .= $c;
                    /**/
                    
                    /*
                     $code = hexdec(substr($str, 2, 2));
                     $c = chr($code);
                     $name .= $c;
                     */
                }
                else
                {
                    $str .= $unistr;
                }
            }
        }
        return $str;
    }
    
    /**
     * 自动判断文字转换为 UTF-8格式
     * @param  string $str
     * @return string
     */
    static public function toUtf8($str)
    {
        $encoding = mb_detect_encoding($str);
        if ($encoding !== 'UTF-8') {
            return iconv($encoding, 'UTF-8', $str);
        }
        return $str;
    }
    
    static public function toLocalePath($path)
    {
        $encoding = mb_detect_encoding($path);
        return strtoupper(substr(PHP_OS, 0, 3))==='WIN' && $encoding !== 'GBK' ? iconv('UTF-8', 'GBK', $path) : $path;
    }
    
    static public function fromLocalePath($path)
    {
        return strtoupper(substr(PHP_OS, 0, 3))==='WIN' ? iconv('GBK', 'UTF-8', $path) : $path;
    }
    
    /***************************************************************************
     * 生成 uuid, random, serial, hash, password
     ***************************************************************************/
    /**
     * get uuid
     * @param  int    $length
     * @return string
     */
    static public function uuid($length=32)
    {
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string=md5(uniqid(microtime(true),true));
        for(;$length>=1;$length--)
        {
            $position=rand()%strlen($chars);
            $position2=rand()%strlen($string);
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }
    
    /**
     * get 64bit uuid
     *
     * @return string
     */
    static public function uuid64()
    {
        return static::uuid(64);
    }
    
    /**
     * get 128bit uuid
     *
     * @return string
     */
    static public function uuid128()
    {
        return static::uuid(128);
    }
    
    /**
     * 取得随机值
     * @param int     $length
     * @param boolean $is_numeric 是否仅取数字
     */
    static public function random($length=32, $is_numeric = false)
    {
        $chars = $is_numeric ? "0123456789" : "abcdefghijklmnopqrstuvwxyz012346789ABCDEFGHIGKLMNOPQRSTUVWXYZ";
        $str = "";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    
    /**
     * random number
     * @param  int    $length
     * @return string
     */
    static public function randomNumber($length=32)
    {
        return static::random($length, 1);
    }
    
    /**
     * random string
     * @param  int    $length
     * @return string
     */
    static public function randomString($length=32)
    {
        return static::random($length, 0);
    }
    
    /**
     * 生成流水号，最少位数为20
     * @param  int    $length
     * @param  string $prefix
     * @return string
     */
    static public function serial($length=20, $prefix=null)
    {
        if ($length < 20) $length = 20;
        $str = date('YmdHis'); // 14bit
        $str .= $length > 24 ? time() . static::randomNumber($length-24) : static::random($length - 14);
        if ($prefix) $str = $prefix . $str;
        
        return $str;
    }
    
    
    /**
     * 取得8位长hash值，参数为依次输入的参与生成hash的字串值.
     * hash 一是作为简单的session/cookie值用于验证，一是作为键或键的组成部分
     * @return string
     */
    static public function hash($args=null)
    {
        $args = is_array($args) ? $args : func_get_args();
        if (!empty($args)) {
            $value = implode('||', $args);
        } else {
            $value = time();
        }
        return substr(md5($value), 0, 8);
    }
    
    /**
     * return password from input, salt
     * @param string $input
     * @param string $salt
     * @return string
     */
    static public function password($input, $salt = '')
    {
        return strtolower(substr(md5(md5($input) . $salt), 0, 32));
    }
    
    static public function toObject($data)
    {
        if (is_object($data)) {
            return $data;
        }
        if (is_array($data)) {
            $data = json_encode($data);
        }
        return json_decode($json);
    }
    
    /**
     * to xml string
     * @param  string|array $data
     * @return string
     */
    static public function toXml($data)
    {
        return XmlHelper::encode($data);
    }
    
    /**
     * from xml str to array
     * @param  string $str
     * @return array
     */
    static public function fromXml($str)
    {
        return XmlHelper::decode($str);
    }
    
    /**
     * from string to SimpleXMLElement
     * @param  string|array $data
     * @return SimpleXMLElement
     */
    static public function asXml($data)
    {
        return XmlHelper::asXml($data);
    }
    
    /**
     * to tree 多维层次树
     * @param  array $data
     * @param  array $fields
     * @return array
     */
    static public function toTree($data, $fields=['id', 'parent_id'])
    {
        $treeHandler = new Tree($data, $fields);
        return $treeHandler->tree();
    }
    
    /**
     * to tree flatLeaf 一维平面树
     * @param  array $data
     * @param  array $fields
     * @param  int   $root_id
     * @return array
     */
    static public function toFlatLeaf($data, $fields=['id', 'parent_id'], $root_id=null)
    {
        $treeHandler = new Tree($data, $fields);
        return $treeHandler->flatLeaf($root_id);
    }
    
    /**
     * to unixtime
     * @param  string|int $value
     * @return int        $timestamp
     */
    static public function toUnixtime($value=null)
    {
        return (empty($value)) ? time() : (is_numeric($value) ? $value : strtotime($value));
    }
    
    /**
     * from unixtime to string '2017-01-01 12:00:00'
     * @param  string|int $value
     * @return int        $timestamp
     */
    static public function fromUnixtime($timestamp=null)
    {
        if(is_numeric($timestamp)) {
            // strftime format is different from date()
            //$value = strftime('%Y-%m-%d %H:%M:%S', $value);
            $timestamp = strftime('%Y-%m-%d %H:%M', $timestamp);
        }
        return $timestamp;
    }
    
    /**
     * implode
     * @param  string $glue
     * @param  string|array $data
     * @return string
     */
    static public function implode($glue, $data)
    {
        if (is_array($data)) {
            if (is_array(current($data))) {
                foreach ($data as $k => $v) {
                    $data[$k] = implode($glue, $v);
                }
            }
            
            return implode($glue, $data);
        }
        return $data;
    }
    
    /**
     * explode, 使用正则分隔
     * @param  string       $glue
     * @param  string|array $value
     * @return array
     */
    static public function explode($glue, $value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result = array_merge($result, static::explode($glue, $v));
            }
            return $result;
        } elseif (is_string($value)) {
            if (is_array($glue)) {
                $glue = implode('', $glue);
            }
            $value = preg_split('/[' . $glue . ']+/', $value);
            $value = (array) $value;
            $value = array_map(function ($v) { return trim($v); }, $value);
        }
        return $value;
    }
    
    
    
    
}