<?php
namespace Wslim\Util;

/**
 * HttpRequest
 * 
 * @author 28136957@qq.com
 * @link   wslim.cn
 */
class HttpRequest
{
    const METHODS       = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'PATCH'];
    const DATA_TYPES    = ['text', 'array', 'json', 'xml'];
    
    /**********************************************************
     * static mathods
     **********************************************************/
    /**
     * http request, return HttpRequest object. 
     * Then can call getErrorString(), getResponse(), getResponseBody(), getResponseInfo(), getResponseText(), getResponseHeader()
     * 
     * @param  string|array $method
     * @param  string|array $url
     * @param  string|array $data
     * @param  array        $options
     * @return static
     */
    static public function request($method, $url=null, $data = false, $options = false)
    {
        $instance = new static($method, $url, $data, $options);
        return $instance->execute();
    }
    
    /**
     * GET request
     *
     * @param  string|array $url
     * @param  string|array $data
     * @param  array        $options
     * 
     * @return mixed false if failure and mixed if success, if set $options['dataType'] then return convert type
     * 
     * @example
     * ```
     * HttpRequest::get('http://api.example.com/?a=123&b=456');
     * ```
     */
    static public function get($url, $data=null, $options=null)
    {
        $instance = new static('GET', $url, $data, $options);
        return $instance->execute()->getResponseBody();
    }
    
    /**
     * POST request
     *
     * @param  string|array $url
     * @param  string|array $data
     * @param  array        $options
     *
     * @return mixed false if failure and mixed if success
     * 
     * @example
     * ```
     * HttpRequest::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'));
     * HttpRequest::post('http://api.example.com/', 'a=123&b=bbb');
     * HttpRequest::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg')); //for post upload file
     * ```
     */
    static public function post($url, $data, $options=null)
    {
        $instance = new static('POST', $url, $data, $options);
        return $instance->execute()->getResponseBody();
    }
    
    /**
     * download, support multi url and multi save_path, return array
     * 用于下载请求内容；返回header信息和body合并组成的数组，可根据 content_type 判断mime类型来确定内容类型
     * 支持多个同时下载，save_path 可传递数组
     * 
     * @param  string|array $url
     * @param  string|array $save_path
     * @param  string|array $data
     * @param  array        $options
     * 
     * @return array  [
     *      'errcode'   => 0,       // 0 for success and negative number for failure
     *      'body'      => '...',   // false for failed or string for success
     *      'save_path' => 'save_path',
     *      ...
     * ]
     *
     * -- 返回结果示例
     * {
     "url": "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=My4oqLEyFVrgFF-XOZagdvbTt9XywYjGwMg_GxkPwql7-f0BpnvXFCOKBUyAf0agmZfMChW5ECSyTAgAoaoU2WMyj7aVHmB17ce4HzLRZ3XFTbm2vpKt_9gYA29xrwIKpnvH-BYmNFSddt7re5ZrIg&media_id=QQ9nj-7ctrqA8t3WKU3dQN24IuFV_516MfZRZNnQ0c-BFVkk66jUkPXF49QE9L1l",
     "content_type": "image/jpeg",
     "http_code": 200,
     "header_size": 308,
     "request_size": 316,
     "filetime": -1,
     "ssl_verify_result": 0,
     "redirect_count": 0,
     "total_time": 1.36,
     "namelookup_time": 1.016,
     "connect_time": 1.078,
     "pretransfer_time": 1.078,
     "size_upload": 0,
     "size_download": 105542,
     "speed_download": 77604,
     "speed_upload": 0,
     "download_content_length": 105542,
     "upload_content_length": 0,
     "starttransfer_time": 1.141,
     "redirect_time": 0,
     "body": ...        // if not set $save_path then return this, it is response body
     "save_path": ...   // if set $save_path then return this, it is real save path
     }
     */
    static public function download($url, $save_path=null, $data=null, $options=null)
    {
        $urls = [];
        $save_paths = [];
        if (is_array($url)) {
            foreach ($url as $k => $v) {
                if (isset($v['save_path']) && isset($v['url'])) {
                    $urls[]     = $v['url'];
                    $save_paths[$v['url']] = $v['save_path'];
                } else {
                    $urls[]     = $v;
                }
            }
        } else {
            $urls = (array) $url;
        }
        
        $save_dir = null;
        if (!$save_paths) {
            if (is_string($save_path)) {
                $save_dir = str_replace("\\", "/", $save_path);
                $save_path = null;
            } else {
                if (count($save_path) !== count($urls)) {
                    return ['errcode'=>-1, 'errmsg'=>'url/save_path setting error.'];
                }
                foreach ($save_path as $k => $v) {
                    $save_paths[$urls[$k]] = $v;
                }
            }
        }
        
        $http = new static('GET', $urls, $data, $options);
        $multiRes = $http->execute()->getMultiResponse();
        
        $items = [];
        foreach ($multiRes as $k => $res) {
            $item = [];
            if (isset($res['info'])) {
                $item = $res['info'];
            }
            $item['url'] = $res['url'];
            $item['errcode'] = 0;
            
            if (isset($res['error']) && $res['error']) {
                $items[$k] = array_merge($item, $res['error']);
                continue;
            }
            
            if ($res['status'] != '200') {
                $items[$k] = array_merge($item, ['errcode'=>-1, 'errmsg'=>'Http request failure:' . $res['status']]);
                continue;
            }
            
            if ($save_dir || $save_paths) {
                if ($save_dir) {
                    if (count($urls) > 1 || substr($save_dir, count($save_dir)-1) == "/") {
                        $real_path = explode('?', $res['url'], 2);
                        $real_path = $save_dir . pathinfo($real_path[0], PATHINFO_BASENAME);
                    } else {
                        $real_path = $save_dir;
                    }
                } else {
                    foreach ($save_paths as $u => $p) {
                        if (stripos($res['url'], $u) !== false) {
                            $real_path = $p;
                            break;
                        }
                    }
                    if (!$real_path) {
                        $real_path = $save_paths[0];
                        $real_path = pathinfo($real_path, PATHINFO_DIRNAME) . '/'. pathinfo($res['url'], PATHINFO_BASENAME);
                    }
                }
                
                if (isset($res['info']) && isset($res['info']['content_type'])) {
                    if ($content_type = explode(';', $res['info']['content_type'], 1)) {
                        $content_type = explode('/', $content_type[0], 2);
                        $rFileExt = $content_type[count($content_type) - 1];
                        $fielExt = pathinfo($real_path, PATHINFO_EXTENSION);
                        if (!strpos($real_path, '.') && $fielExt !== $rFileExt && strlen($rFileExt) < 5) {
                            $real_path .= '.' . $rFileExt;
                        }
                    }
                }
                
                // mkdir
                $real_path = str_replace("\\", "/", $real_path);
                $dir = pathinfo($real_path, PATHINFO_DIRNAME);
                $dir = DataHelper::toLocalePath($dir);
                if (!file_exists($dir)) {
                    @mkdir($dir, '0755', true);
                    if (!file_exists($dir)) {
                        $items[$k] = array_merge($item, ['errcode'=>-1, 'errmsg'=>'dir create failure:' . $dir]);
                        continue;
                    }
                }
                
                // if filename has chinese string, convert to GBK
                $len = file_put_contents(DataHelper::toLocalePath($real_path), $res['body']);
                if ($len) {
                    $item['save_path'] = $real_path;
                } else {
                    $item['errcode']    = -1;
                    $item['errmsg']     = 'save failure:' . $real_path;
                }
            } else {
                $item['body']    = $res['body'];
            }
            
            $items[$k] = $item;
        }
        
        return count($urls) > 1 ? ['errcode'=>0, 'items'=>$items] : $items[0];
    }
    
    /**********************************************************
     * instance mathods
     **********************************************************/
    /**
     * definition
     * @var array
     */
    private $def = [
        'method'    => 'GET',
        'url'       => null,
        'data'      => null,
        'dataType'  => 'text',  // text/json/xml/array
        'header'    => [
            'Accept'            => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language'   => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding'   => 'gzip, deflate, br',
            'Accept-Charset'    => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'User-Agent'        => 'Mozilla/5.0 Firefox/56.0',
            'Connection'        => 'close',
        ],
        'cookie'    => null,
        'success'   => null,
        'failed'    => null,
        'timeout'   => 0,
    ];
    
    /**
     * options, name is upper, contain curl options with prefix CURLOPT_
     * @var array
     */
    private $options = [
        'HTTP_VERSION'  => '1.1',
        'USE_CURL'      => true,
        'RETURN_HEADERS'=> 1,
    ];
    
    /**
     * return results, [0 => $result], each result is array, contain header/body/info/error 
     * @var array
     */
    private $results;
    
    /**
     * consturct, use multi param or array params, params key is [method, url, data, dataType, timeout, options]
     * 
     * 
     * @param string       $method
     * @param array|string $url
     * @param array|string $data
     * @param array        $options
     */
    function __construct($method, $url=null, $data = false, $options = false) 
    {
        if (is_array($method)) {
            if (!isset($method[0])) {
                $this->setOptions($method);
            } else {
                $options = $data;
                $data   = $url;
                $url    = $method;
                $method = 'GET';
            }
        } else {
            if (strlen($method) > 5) {
                $options = $data;
                $data   = $url;
                $url    = $method;
                $method = 'GET';
            }
        }
        
        $this->setMethod($method);
        if ($url) {
            $this->setUrl($url);
        }
        
        if ($data) {
            if (is_string($data) && in_array($data, static::DATA_TYPES)) {
                $this->setDataType($data);
            } else {
                $this->setData($data);
            }
        }
        
        if ($options) {
            if (is_string($options) && in_array($options, static::DATA_TYPES)) {
                $this->setDataType($options);
            } else {
                $this->setOptions($options);
            }
        }
    }
    
    public function setMethod($method)
    {
        $method = strtoupper($method);
        if (in_array($method, static::METHODS)) {
            $method = 'GET';
        }
        $this->def['method'] = $method;
        
        return $this;
    }
    
    public function setUrl($url)
    {
        $url = (array) $url;
        foreach ($url as $k => $v) {
            if (strpos($v, 'http') === false) {
                $url[$k] = 'http://' . $v;
            }
        }
        
        $this->def['url'] = $url;
        return $this;
    }
    
    public function setData($data) {
        $this->def['data'] = $data;
        return $this;
    }
    
    public function setDataType($dataType) {
        $this->def['dataType'] = $dataType;
        return $this;
    }
    
    public function setTimeout($timeout)
    {
        $this->def['timeout'] = $timeout;
        return $this;
    }
    
    public function setHeader($name, $value=null) 
    {
        if (is_array($name)) {
            $this->def['header'] = array_merge($this->def['header'], $name);
        } else {
            $this->def['header'][$name] = $value;
        }
        return $this;
    }
    
    public function setCookie($name, $value=null) 
    {
        $str = '';
        if (is_array($name)) {
            $arr = [];
            foreach ($name as $k=>$v) {
                $arr[] = $k . '=' . $v;
            }
            $str = implode(';', $arr);
        } elseif ($value) {
            $str = $name . '=' . $value;
        } else {
            $str = $name;
        }
        
        $this->def['cookie'] = $this->def['cookie'] ? $this->def['cookie'].';'.$str : $str;

        return $this;
    }
    
    public function setSuccess($func)
    {
        $this->def['success'] = $func;
    }
    
    public function setFailed($func)
    {
        $this->def['failed'] = $func;
    }
    
    public function setOptions($option, $value=null) 
    {
        if (is_array($option)) {
            foreach ($option as $k=>$v) {
                $this->setOptions($k, $v);
            }
        } else {
            // synonymies convert
            if ($option === 'cookies') {
                $option = 'cookie';
            } elseif ($option === 'headers') {
                $option = 'header';
            }
            
            if (array_key_exists($option, $this->def)) {
                $call = 'set' . ucfirst($option);
                if (method_exists($this, $call)) {
                    $this->$call($value);
                }
            } else {
                $this->options[strtoupper($option)] = $value;
            }
        }
        return $this;
    }
    
    /**
     * execute
     * @return static
     */
    public function execute() 
    {
        if ($this->options['USE_CURL'] && function_exists('curl_init')) {
            $this->curlExecute();
        } else {
            $this->fsockgetExecute();
        }
        return $this;
    }
    
    /**
     * convert @ prefixed file names to CurlFile class, since @ prefix is deprecated as of PHP 5.6
     * @param mixed $data
     * @param mixed
     */
    protected function parseData($data=null)
    {
        if (!$data) $data = $this->def['data'];
        
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (strpos($v, '@') === 0 && class_exists('\CURLFile')) {
                    $v = ltrim($v, '@');
                    $data[$k] = new \CURLFile($v);
                }
            }
        }
        
        return $data;
    }
    
    protected function parseQuery($data=null)
    {
        if (!$data) $data = $this->def['data'];
        
        if (is_array($data)) {
            $data_array = array();
            foreach ($this->def['data'] as $key => $val) {
                if (!is_string($val)) {
                    $val = json_encode($val);
                }
                $data_array[] = urlencode($key).'='.urlencode($val);
            }
            return implode('&', $data_array);
        } else {
            return $data;
        }
    }
    
    private function parseCallback($func)
    {
        if (is_callable($func)) {
            return $func;
        } elseif (is_string($func)) {
            return [&$this, $func];
        } else {
            return null;
        }
    }
    
    /**
     * parse header to array
     * @param  $str
     * @return array
     */
    private function header2Array($str)
    {
        if (is_array($str)) return $str;
        
        $result = [];
        $array = explode("\n", trim(str_replace("\r\n", "\n", $str), "\n"));
        foreach($array as $i => $line) {
            if ($i === 0) {
                $result['Http-Status'] = $line; // HTTP/1.1 200 OK
            } else {
                $header = explode(': ', $line);
                if (!$header[0]) continue;
                
                if (isset($header[1])) {
                    $result[$header[0]] = trim($header[1]);
                } else {
                    $result[] = trim($header[0]);
                }
            }
        }
        return $result;
    }
    
    /**
     * parse header to string
     * @param  mixed  $headers
     * @return string
     */
    private function header2String($header)
    {
        $str = '';
        if (is_array($header)) foreach ($header as $k=>$v) {
            if (is_numeric($k)) continue;
            $str .= $k . ': ' . $v . "\r\n";
        } else {
            $str = $header;
        }
        return $str;
    }
    
    private function curlExecute() 
    {
        // check
        if (!$this->def['url']) {
            return $this->setError('url is not set');
        }
        
        $urls   = (array) $this->def['url'];
        $method = $this->def['method'];
        $returnHeaders = (bool) $this->options['RETURN_HEADERS'];
        
        $mh     = curl_multi_init();
        $conn   = [];
        
        foreach ($urls as $url) {
            $ch = curl_init($url);
            
            // method and data
            if ($method === 'GET') {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                
                if ($this->def['data']) {
                    $url .= (strpos($url, '?') ? "&" : '?') . $this->parseQuery($this->def['data']);
                }
            } elseif ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($this->def['data']) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->parseData($this->def['data']));
                }
            } elseif ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_PUT, true);
            } else {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
            
            // url and base
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );   // return result
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // allow redirect
            if ($this->def['timeout']) {
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->def['timeout']);
            }
            
            // header, require set array
            if ($this->def['header']) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header2Array($this->def['header']));
            }
            
            // receive header
            curl_setopt($ch, CURLOPT_HEADER, $returnHeaders);       // 设为 TRUE 获取responseHeader，curl_exec()返回结果是 header和body的组合文本，需要手动分离
            curl_setopt($ch, CURLINFO_HEADER_OUT, $returnHeaders);  // 设为 TRUE 时curl_getinfo()返回结果包含 request_header 信息，从 PHP 5.1.3 开始可用。
            
            // register callback which process the headers
            if (isset($this->def['headerCallback']) && $this->def['headerCallback']) {
                if (!is_callable($this->def['headerCallback']) && is_string($this->def['headerCallback'])) {
                    $this->def['headerCallback'] = array(&$this, $this->def['headerCallback']);
                }
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, $this->def['headerCallback']);
            }
            
            // cookie
            if ($this->def['cookie']) {
                curl_setopt($ch, CURLOPT_COOKIE, $this->def['cookie']);
            }
            
            // ssl
            if (strpos($url, 'https') === 0) {
                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
                //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // 1的值不再支持，请使用2或0
                
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // 1的值不再支持，请使用2或0
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
            }
            
            // Authentication
            if (isset($this->def['authUsername']) && isset($this->def['authPassword']) && $this->def['authUsername']) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $this->def['authUsername'] . ':' . $this->def['authPassword']);
            }
            
            // custom option
            try {
                foreach ($this->options as $k => $v) {
                    if (strpos($k, 'CURLOPT_') !== false) {
                        curl_setopt($ch, get_defined_constants()[$k], $v);
                    }
                }
            } catch (\Exception $e) {
                //
            }
            
            $conn[$url] = $ch;
            curl_multi_add_handle($mh, $conn[$url]);
        }
        
//         do {
//             $mrc = curl_multi_exec($mh, $active);
//         } while ($mrc === CURLM_CALL_MULTI_PERFORM);
        
//         while ($active && $mrc === CURLM_OK) {
//             if (curl_multi_select($mh) != -1) {
//                 do {
//                     $mrc = curl_multi_exec($mh, $active);
//                 } while ($mrc === CURLM_CALL_MULTI_PERFORM);
//             }
//         }
        
        do {
            if (($status = curl_multi_exec($mh, $running)) != CURLM_CALL_MULTI_PERFORM ) {
                if ($status != CURLM_OK) {
                    break;
                }
                //echo $status, '|', $running, PHP_EOL;
                // 一旦有一个请求完成，找出来，处理,因为curl底层是select，所以最大受限于1024
                while ($done = curl_multi_info_read($mh)){
                    // $info = curl_getinfo($done['handle']);
                    // $output = curl_multi_getcontent($done['handle']);
                    // $error = curl_error($done['handle']);
                    // 把请求已经完成了得 curl handle 删除
                    curl_multi_remove_handle($mh, $done['handle']);
                }
                // 当没有数据的时候进行堵塞，把 CPU 使用权交出来，避免上面 do 死循环空跑数据导致 CPU 100%
                if(curl_multi_select($mh, 1) == -1){
                    usleep(500000);
                }
            }
        } while($running > 0);
        
        // result
        foreach ($conn as $url => $ch) {
            //$this->responseText     = curl_exec($ch);    // 如果设置了 CURLOPT_HEADER, 返回结果是 header和body的组合文本，需要手动分离
            //$this->status           = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //$this->responseInfo     = curl_getinfo($ch);
            
            $res = [];
            $res['url']     = $url;
            $res['status']  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $res['info']    = curl_getinfo($ch);
            
            if ($errno = curl_errno($ch)) {
                $res['error'] = $this->buildError(curl_errno($ch), curl_error($ch));
            } else {
                $res['body']    = curl_multi_getcontent($ch);
                if ($returnHeaders) {
                    // 如果有302,返回文本会有多个headers内容，需要去掉
                    $txtArr = explode("\r\n\r\n", $res['body'], 3);
                    $header = array_shift($txtArr);
                    if (isset($res['info']['header_size']) && count($txtArr)>1 && (strlen($header)+4) != $res['info']['header_size']) {
                        $header = array_shift($txtArr);
                    }
                    $res['header']  = $this->header2Array($header);
                    $res['body']    = implode("\r\n\r\n", $txtArr);
                }
                if (isset($res['info']['request_header'])) {
                    $res['request_header'] = $res['info']['request_header'];
                }
            }
            
            curl_close($ch);
            
            $this->results[] = $res;
            
            // handle success/failed callback
            static::callback($res);
        }
        
        curl_multi_close($mh);
    }
    
    protected function fsockgetExecute($url=null, $item=0) 
    {
        $url        = $url ? $url : $this->def['url'];
        if (is_array($url)) {
            foreach ($url as $k => $iurl) {
                static::fsockgetExecute($iurl, $k);
            }
            return ;
        }
        
        $method     = $this->def['method'];
        $httpVersion = $this->options['HTTP_VERSION'];
        $data       = $this->def['data'];
        $crlf = "\r\n";
        
        $rsp = '';
        
        // parse host, port
        preg_match('/(http\\s?):\/\/([^\:\/]+)(:\d+)?/', $url, $matches);
        $isSSL = isset($matches[1]) && $matches[1]=='https' ? true : false;
        $host = isset($matches[2]) ? $matches[2] : null;
        $port = isset($matches[3]) ? str_replace(':', '', $matches[3]) : null;
        if (!$host) {
            $this->setError('Host set error');
            return false;
        }
        $port = $port ? : ($isSSL ? 443 : 80);
        
        // Deal with the data first.
        if ($data && $method === 'POST') {
            $data = $this->parseQuery($data);
        } else if ($data && $method === 'GET') {
            $url .= (strpos($url, '?') ? "&" : '?') . $this->parseQuery($data);
            $data = $crlf;
        } else {
            $data = $crlf;
        }
        
        // Then add
        if ($method === 'POST') {
            $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $this->setHeader('Content-Length', strlen($data));
        } else {
            $this->setHeader('Content-Type', 'text/plain');
            $this->setHeader('Content-Length', strlen($crlf));
        }
        if (isset($this->def['authUsername']) && isset($this->def['authPassword']) && $this->def['authUsername'] && $this->def['authPassword']) {
            $this->setHeader('Authorization', 'Basic '.base64_encode($this->def['authUsername'].':'.$this->def['authPassword']));
        }
        
        $headers = $this->def['header'];
        $req = '';
        $req .= $method.' '.$url.' HTTP/'.$httpVersion.$crlf;
        $req .= "Host: ".$host.$crlf;
        foreach ($headers as $header => $content) {
            if (is_numeric($header)) continue;  // 跳过无效值
            $req .= $header.': '.$content.$crlf;
        }
        $req .= $crlf;
        if ($method === 'POST') {
            $req .= $data;
        } else {
            $req .= $crlf;
        }
        
        // Construct hostname.
        $fsock_host = ($isSSL ? 'ssl://' : '').$host;
        
        // Open socket.
        $httpreq = @fsockopen($fsock_host, $port, $errno, $errstr, 30);
        
        // Handle an error.
        if (!$httpreq) {
            $this->setError($errno, $errstr);
            return false;
        }
        
        // Send the request.
        fputs($httpreq, $req);
        
        // Receive the response.
        /*
        while ($line = fgets($httpreq)) {
            $rsp .= $line;
        }
        */
        while (!feof($httpreq)) {
            $rsp .= fgets($httpreq);
        }
        
        // Extract the headers and the responseText.
        list($headers, $responseText) = explode($crlf.$crlf, $rsp, 2);
        
        // Store the finalized response.
        // HTTP/1.1 下过滤掉分块的标志符
        if ($httpVersion == '1.1') {
            $responseText = static::unchunkHttp11($responseText);
        }
        
        // Store the response headers.
        $headers = explode($crlf, $headers);
        
        $res = [];
        $res['url']     = $url;
        $res['body']    = $responseText;
        $res['status']  = array_shift($headers);  // HTTP/1.1 200 OK
        $res['status']  = explode(' ', $this->status)[1];
        $res['header']  = array();
        foreach ($headers as $header) {
            list($key, $val) = explode(': ', $header);
            $res['header'][$key] = $val;
        }
        
        fclose($httpreq);
        
        // handle success/failed callback
        static::callback($res);
        
        $this->results[$item] = $res;
    }
    
    private function callback($res)
    {
        if (!isset($res['error']) || !$res['error']) {
            if ($this->def['success']) {
                $callback = static::parseCallback($this->def['success']);
                $callback($res);
            }
        } else {
            if ($this->def['failed']) {
                $callback = static::parseCallback($this->def['success']);
                $callback($res);
            }
        }
    }
    
    private function buildError($errno, $errmsg=null)
    {
        $error = is_numeric($errno) ? ['errcode' => $errno, 'errmsg' => $errmsg] : ['errcode' => -1, 'errmsg' => $errno];
        return $error;
    }
    
    /**
     * get multi response for multi urls.
     * each item is array, ['url'=>, 'error'=>, 'header'=>, 'body'=>, 'info'=>, 'status'=>, 'request_header'=>]
     * 
     * @return array
     */
    public function getMultiResponse()
    {
        return $this->results;
    }
    
    /**
     * return ['url'=>, 'error'=>, 'header'=>, 'body'=>, 'info'=>, 'status'=>, 'request_header'=>]
     * @param  string $name
     * @param  number $item
     * @return array
     */
    public function getResponse($name=null, $item=0)
    {
        $res = isset($this->results[$item]) ? $this->results[$item] : [];
        if ($name) {
            return isset($res[$name]) ? $res[$name] : null;
        }
        return $res;
    }
    
    /**
     * get error, ['errcode'=>, 'errmsg'=>]
     * @return array
     */
    public function getError() 
    {
        return $this->getResponse('error');
    }
    
    public function getErrorString()
    {
        $error = $this->getError();
        return $error ? $error['errcode'] . ':' . $error['errmsg'] : null;
    }
    
    private function setError($errno, $errmsg=null)
    {
        $error = is_numeric($errno) ? ['errcode' => $errno, 'errmsg' => $errmsg] : ['errcode' => -1, 'errmsg' => $errno];
        $this->results[0]['error'] = $error;
        
        return $this;
    }
    
    /**
     * get resposne body, return type can be text|json|array|xml
     * @return mixed false if failure
     */
    public function getResponseBody()
    {
        $res = $this->getResponse();
        if (isset($res['error'])) {
            return false;
        }
        switch ($this->def['dataType']) {
            case 'array':
                $result = DataHelper::json_decode($res['body']);
                break;
            case 'xml':
                $result = DataHelper::asXml($res['body']);
                break;
            case 'json':
                $result = DataHelper::toObject($res['body']);
                break;
            default:
                $result = $res['body'];
        }
        return $result;
    }
    
    /**
     * get response text
     * @return false|string false if failure
     */
    public function getResponseText()
    {
        $res = $this->getResponse();
        
        if ($res['error']) {
            return false;
        }
        
        return $res['body'];
    }
    
    /**
     * get resposne status code: 200|xxx
     * @return int
     */
    public function getStatus()
    {
        $res = $this->getResponse();
        return $res['status'];
    }
    
    /**
     * get response info
     * @return array
     */
    public function getResponseInfo()
    {
        return $this->getResponse('info');
    }
    
    /**
     * get response headers
     * @return array
     */
    public function getResponseHeaders() 
    {
        return $this->getResponse('header');
    }
    
    public function getResponseHeadersString()
    {
        return static::header2String($this->getResponseHeaders());
    }
    
    public function getResponseHeader($header) {
        $headers = $this->getResponseHeaders();
        if (array_key_exists($header, $headers)) {
            return $headers[$header];
        }
        return null;
    }
    
    public function getResponseCookie($cookie = false)
    {
        $headers = $this->getResponseHeaders();
        if($cookie !== false) {
            return isset($headers["Set-Cookie"][$cookie]) ? $headers["Set-Cookie"][$cookie] : null;
        }
        return isset($headers["Set-Cookie"]) ? $headers["Set-Cookie"] : null;
    }
    
    public function getRequestString()
    {
        return $this->parseQuery();
    }
    
    public function getRequestHeaders()
    {
        $header = $this->getResponse('request_header');
        return $header ? $this->header2Array($header) : null;
    }
    
    public function getRequestHeadersString()
    {
        return $this->getResponse('request_header');
    }
    
    /**
     * fsockopen 读取因为使用了 Transfer-Encoding: chunked, 会多出分块时的数字字符，需要去掉。方法一，会用如下，方法二，使用 HTTP/1.0
     * @param  string $data
     * @return string
     */
    function unchunkHttp11($data) {
        /*
        $fp = 0;
        $outData = "";
        while ($fp < strlen($data)) {
            $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
            $num = hexdec(trim($rawnum));
            $fp += strlen($rawnum);
            $chunk = substr($data, $fp, $num);
            $outData .= $chunk;
            $fp += strlen($chunk);
        }
        return $outData;
        */
        
        return preg_replace_callback(
            '/(?:(?:\r\n|\n)|^)([0-9A-F]+)(?:\r\n|\n){1,2}(.*?)'.
            '((?:\r\n|\n)(?:[0-9A-F]+(?:\r\n|\n))|$)/si',
            create_function(
                '$matches',
                'return hexdec($matches[1]) == strlen($matches[2]) ? $matches[2] : $matches[0];'
                ),
            $data
        );
    }
    
    
    
    
}