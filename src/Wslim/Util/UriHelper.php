<?php
namespace Wslim\Util;

/**
 * Uri Helper
 *
 * This class provides an UTF-8 safe version of parse_url().
 *
 * This class is a fork from Joomla Uri.
 */
class UriHelper
{
	/**
	 * Sub-delimiters used in query strings and fragments.
	 *
	 * @const string
	 */
	const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

	/**
	 * Unreserved characters used in paths, query strings, and fragments.
	 *
	 * @const string
	 */
	const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string  $url  URL to parse
	 *
	 * @return  mixed  Associative array or false if badly formed URL.
	 *
	 * @see     http://us3.php.net/manual/en/function.parse-url.php
	 */
	public static function parseUrl($url)
	{
		$result = false;

		// Build arrays of values we need to decode before parsing
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]");

		// Create encoded URL with special URL characters decoded so it can be parsed
		// All other characters will be encoded
		$encodedURL = str_replace($entities, $replacements, urlencode($url));

		// Parse the encoded URL
		$encodedParts = parse_url($encodedURL);

		// Now, decode each value of the resulting array
		if ($encodedParts)
		{
			foreach ($encodedParts as $key => $value)
			{
				$result[$key] = urldecode(str_replace($replacements, $entities, $value));
			}
		}

		return $result;
	}

	/**
	 * build url from path and queryParams
	 * @param  string       $path
	 * @param  string|array $params
	 * @return string
	 */
	static public function buildUrl($path, $params=null)
	{
	    if (is_array($params)) {
	        $params = http_build_query($params);
	    }
	    if ($params) {
	        $params = (strpos($path, '?') !== false ? '&' : '?') . $params;
	    }
	    
	    return $path . $params;
	}
	
	/**
	 * parseQuery
	 *
	 * @param   string  $query
	 * @return  mixed
	 */
	public static function parseQuery($query)
	{
		parse_str($query, $vars);

		return $vars;
	}
	
	/**
	 * Build a query from a array (reverse of the PHP parse_str()).
	 *
	 * @param   array  $params  The array of key => value pairs to return as a query string.
	 *
	 * @return  string  The resulting query string.
	 *
	 * @see     parse_str()
	 */
	public static function buildQuery(array $params)
	{
	    return urldecode(http_build_query($params, '', '&'));
	}
	
	/**
	 * filterScheme
	 *
	 * @param   string  $scheme
	 *
	 * @return  string
	 */
	public static function filterScheme($scheme)
	{
		$scheme = strtolower($scheme);
		$scheme = preg_replace('#:(//)?$#', '', $scheme);

		if (empty($scheme))
		{
			return '';
		}

		return $scheme;
	}

	/**
	 * Filter a query string to ensure it is propertly encoded.
	 *
	 * Ensures that the values in the query string are properly urlencoded.
	 *
	 * @param   string  $query
	 *
	 * @return  string
	 */
	public static function filterQuery($query)
	{
		if (! empty($query) && strpos($query, '?') === 0)
		{
			$query = substr($query, 1);
		}

		$parts = explode('&', $query);
		foreach ($parts as $index => $part)
		{
			list($key, $value) = static::splitQueryValue($part);

			if ($value === null)
			{
				$parts[$index] = static::filterQueryOrFragment($key);

				continue;
			}

			$parts[$index] = sprintf(
				'%s=%s',
				static::filterQueryOrFragment($key),
				static::filterQueryOrFragment($value)
			);
		}

		return implode('&', $parts);
	}

	/**
	 * Split a query value into a key/value tuple.
	 *
	 * @param   string  $value
	 *
	 * @return  array  A value with exactly two elements, key and value
	 */
	public static function splitQueryValue($value)
	{
		$data = explode('=', $value, 2);

		if (1 === count($data))
		{
			$data[] = null;
		}

		return $data;
	}

	/**
	 * Filter a query string key or value, or a fragment.
	 *
	 * @param   string  $value
	 *
	 * @return  string
	 */
	public static function filterQueryOrFragment($value)
	{
		return preg_replace_callback(
			'/(?:[^' . static::CHAR_UNRESERVED . static::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
			function($matches)
			{
				return rawurlencode($matches[0]);
			},
			$value
		);
	}

	/**
	 * Filter a fragment value to ensure it is properly encoded.
	 *
	 * @param   string  $fragment
	 *
	 * @return  string
	 */
	public static function filterFragment($fragment)
	{
		if (null === $fragment)
		{
			$fragment = '';
		}

		if (! empty($fragment) && strpos($fragment, '#') === 0)
		{
			$fragment = substr($fragment, 1);
		}

		return static::filterQueryOrFragment($fragment);
	}

	/**
	 * Filters the path of a URI to ensure it is properly encoded.
	 *
	 * @param  string  $path
	 *
	 * @return  string
	 */
	public static function filterPath($path)
	{
		return preg_replace_callback(
			'/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
			function($matches)
			{
				return rawurlencode($matches[0]);
			},
			$path
		);
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 */
	public static function cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = count($path); $i < $n; $i++)
		{
			if ($path[$i] == '.' || $path[$i] == '..')
			{
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == ''))
				{
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				}
				elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != '')))
				{
					unset($path[$i]);
					unset($path[$i - 1]);
					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode('/', $path);
	}

	/**
	 * decode
	 *
	 * @param   string  $string
	 * @return  array|string
	 */
	public static function decode($string)
	{
		if (is_array($string))
		{
			foreach ($string as $k => $substring)
			{
				$string[$k] = static::decode($substring);
			}
		}
		else
		{
			$string = urldecode($string);
		}

		return $string;
	}

	/**
	 * encode
	 *
	 * @param   string  $string
	 * @return  array|string
	 */
	public static function encode($string)
	{
		if (is_array($string))
		{
			foreach ($string as $k => $substring)
			{
				$string[$k] = static::encode($substring);
			}
		}
		else
		{
			$string = urlencode($string);
		}

		return $string;
	}
	
	/**
	 * 从路径解析出 params, from /a/1/b/2 to ['a'=>1, 'b'=>2]
	 *
	 * @param  string $path
	 * @return array
	 */
	static public function parseQueryFromPath($path)
	{
	    $pathParams = array();
	    if (! empty($path)) {
	        $params = explode('/', trim($path, '/'));
	        $count = count($params);
	        $n = 0;
	        while ($n < $count) {
	            $pathParams[$params[$n]] = isset($params[$n + 1]) ? $params[$n + 1] : '';
	            $n = $n + 2;
	        }
	        unset($params);
	    }
	    return $pathParams;
	}
	
	/**
	 * 取得根域名
	 * @param  string $domain 域名
	 * @return string 返回根域名
	 */
	static public function GetRootDomain($domain='') 
	{
	    $domain = !empty($domain) ? $domain : $_SERVER['HTTP_HOST'];
	    $re_domain = '';
	    $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
	    $array_domain = explode(".", $domain);
	    $array_num = count($array_domain) - 1;
	    if ($array_domain[$array_num] == 'cn') {
	        if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
	            $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
	        } else {
	            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
	        }
	    } else {
	        $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
	    }
	    return $re_domain;
	}
	
	/**
	 * 获取二级域名
	 * @param string $http_host
	 * @return string
	 */
	static public function getSecondDomain($http_host, $main_domain='aaa.com')
	{
	    $n = preg_match('/([^.]*\.)?([^.]*\.)?\w+\.\w+$/', $http_host, $matches);
	    return isset($matches[2]) ? $matches[2] : (isset($matches[1]) ? $matches[1] : '');
	}
	
	/**
	 * get root url. example: http://example.com
	 * @return string
	 */
	static public function getRootUrl()
	{
	    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
	    $baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
	    return $baseUrl;
	}
	
	/**
	 * 取得基url，从 $_SEVER 中获取. example: http://example.com/test
	 *
	 * @return string
	 */
	static public function getBaseUrl($uri='')
	{
		$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
		$baseUrl .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
		
		return rtrim(str_replace('\\', '/', $baseUrl), '/');
	}
	
	/**
	 * get script full url. example: http://example.com/test/index.php
	 * @return string
	 */
	static public function getScriptUrl()
	{
	    return static::getRootUrl() . (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
	}
	
	/**
	 * get script basename, example: index.php
	 * @return string
	 */
	static public function getScriptBasename()
	{
	    return isset($_SERVER['SCRIPT_NAME']) ? basename($_SERVER['SCRIPT_NAME']) : '';
	}
	
	/**
	 * strip script name part
	 * @param  string $path
	 * @return string
	 */
	static public function stripScriptName($path)
	{
	    if (isset($_SERVER['SCRIPT_NAME']) && ($pos = strpos($path, $_SERVER['SCRIPT_NAME'])) === 0) {
	        $path = substr($path, strlen($_SERVER['SCRIPT_NAME']));
	    }
	    return $path;
	}
	
	/**
	 * get current relative url with query string, example: /test/index.php?a=1
	 * @return string
	 */
	static public function getRelativeUrl()
	{
	    $php_self = $_SERVER['PHP_SELF'] ? DataHelper::filter_unsafe_chars($_SERVER['PHP_SELF']) : DataHelper::filter_unsafe_chars($_SERVER['SCRIPT_NAME']);
	    $path_info = isset($_SERVER['PATH_INFO']) ? DataHelper::filter_unsafe_chars($_SERVER['PATH_INFO']) : '';
	    $relate_url = isset($_SERVER['REQUEST_URI']) ? DataHelper::filter_unsafe_chars($_SERVER['REQUEST_URI']) : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . DataHelper::filter_unsafe_chars($_SERVER['QUERY_STRING']) : $path_info);
	    return $relate_url;
	}
	
	/**
	 * 获取上一页的url，对于跳转的post提交可以用于返回前一页；
	 * @return string
	 */
	static public function getRefUrl()
	{
	    return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
	}
	
	/**
	 * get current absolute URL
	 * @return string
	 */
	static public function getCurrentUrl()
	{
	    $protocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443))
	    ? 'https://' : 'http://';
	    
	    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * url replace, from /a/{id} => [/a/1, /a/2]
	 *
	 * @param  string|array $url "http://domain.com/a/{id:num}"
	 * @param  string|array $replace 'id="1, 3-5, 7"'
	 * @return array
	 */
	static public function pregReplaceUrl($url, $replace=null)
	{
	    $newUrl = [];
	    if (is_string($url)) {
	        $url   = str_replace(["\r\n", "\n"], " ", $url);
	        $url   = preg_split('/[\s]+/', $url);
	        
	        if (!isset($url[1])) {
	            $url = $url[0];
	        }
	    }
	    
	    if (is_array($url)) {
	        foreach ($url as $v) {
	            if (!trim($v)) continue;
	            
	            $temps = static::pregReplaceUrl($v, $replace);
	            $newUrl = array_merge($newUrl, $temps);
	        }
	        return $newUrl;
	    }
	    
	    $url = static::formatUrl($url);
	    
	    // url 中的变量 {id:int}
	    preg_match('/\{(\w+)(\:(int|num|string))?\}/', $url, $matches);
	    
	    if (!$matches) {
	        return (array) $url;
	    }
	    
	    // url format
	    $name = $matches[1];
	    $type = isset($matches[2]) ? trim($matches[2], ':') : 'int';
	    if (!in_array($type, ['int', 'num', 'string'])) {
	        $type = 'string';
	    }
	    
	    // handle replace
	    $replaceArr = [];
	    if ($replace) {
	        if (is_string($replace)) {
	            $replace   = str_replace(["\r\n", "\n", "<br>"], "\n", $replace);
	            $replace   = explode("\n", $replace);
	            foreach ($replace as $v) {
	                $v = explode('=', $v, 2);
	                $rname = str_replace(['{', '}'], '', $v[0]);
	                if (isset($v[1])) {
	                    $replaceArr[$rname] = $v[1];
	                } else {
	                    $replaceArr[] = $v[0];
	                }
	            }
	        } else {
	            $replaceArr = & $replace;
	        }
	    }
	    
	    // replace
	    $newReplace = isset($replaceArr[$name]) ? $replaceArr[$name] : (isset($replaceArr[0]) ? $replaceArr[0] : null);
	    if (!$newReplace) return (array) $url;
	    
	    if ($type == 'int' || $type == 'num') {
	        $newReplace    = NumberHelper::toIntArray($newReplace);
	    } else {
	        $newReplace = (array) $newReplace;
	    }
	    
	    foreach ($newReplace as $rv) {
	        $u = str_replace($matches[0], $rv, $url);
	        
	        // 递归替换参数
	        $temps = static::pregReplaceUrl($u, $replaceArr);
	        $newUrl = array_merge($newUrl, $temps);
	    }
	    
	    return $newUrl;
	}
	
	/**
	 * format url to http[s]://xxx/xx.xx
	 * @param  string $url
	 * @param  string $protocal
	 * @return string
	 */
	static public function formatUrl($url, $protocal='http')
	{
	    if (strpos($url, 'http') === false) {
	        if (strpos(':', $protocal)) {
	            $protocal = explode(':', $protocal, 2)[0];
	        }
	        if (!in_array($protocal, ['http', 'https'])) {
	            $protocal = 'http';
	        }
	        $url = $protocal . '://' . $url;
	        $url = str_replace(["////", "///"], "//", $url);
	    }
	    return rtrim($url, '/');
	}
	
}
