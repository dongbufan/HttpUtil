<?php
namespace Http\HttpUtil;

class HttpUtil
{
    const DEFAULT_COOKIE_PATH = '/';
    /**
     * 发送json post
     * @param  [type]  $url    [description]
     * @param  array   $parame [description]
     * @param  boolean $isjosn [description]
     * @return [type]          [description]
     */
    public static function toPost($url, $parame = array(), $isjosn = false){
        $optArr=array();
        if($isjosn){
            $optArr['request'] = json_encode($parame);
            $optArr['header'] = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($optArr['request'])
            );
        }else{
            $optArr['request'] = http_build_query($parame);
        }
        $data = self::doPost($url,$optArr);
        return $data;
    }
    /**
     * post请求
     * @param string $url
     * @param array $optArr (
     *              'proxy'=>"http://proxy.xxx:80"
     *              'cookie'=>''
     *              'request'=>'a=11&b=22'
     *              'header'=>array
     *              )
     * @return string
     * @exception Exception cur
     */
    public static function doPost($url, $optArr = array(), $needThrow = true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
        if (isset($optArr['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optArr['referer']);
        }
        if (isset($optArr['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optArr['header']);
        }
        //内容过长header
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $timeOut = 3;
        if (isset($optArr['timeout'])) {
            $timeOut = intval($optArr['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        //    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optArr['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $optArr['cookie']);
        }
        if (isset($optArr['request'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $optArr['request']);
        }
        if (isset($optArr['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optArr['proxy']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 3) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    if ($errno == 28){
                        return ['data'=>[],'code'=>503];
                    }
                    throw new \Exception($message, $errno); //超时 code 28
                }
            } else {
                $success = true;
            }
        } while (!$success && $tryCnt++ < 3);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new \Exception($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['data'=>$data,'code'=>$httpCode];
    }

    /**
     * get请求
     * @param string $url
     * @param array $optionArray (
     *              'proxy'=>"http://proxy.xxx:80"
     *              )
     * @return string
     * @exception Exception cur
     */
    public static function doGet($url, $optionArray = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); //CURLOPT_HEADER
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
        if (isset($optionArray['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optionArray['referer']);
        }
        $timeOut = 3;
        if (isset($optionArray['timeout'])) {
            $timeOut = intval($optionArray['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optionArray['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optionArray['proxy']);
        }
        if (isset($optionArray['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optionArray['header']);
        }
        if (isset($optionArray['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $optionArray['cookie']);
        }
        if (isset($optionArray['raw'])) {
            curl_setopt($ch, CURLOPT_HEADER, $optionArray['raw']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 1) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    if ($errno == 28){
                        return ['data'=>[],'code'=>503];
                    }
                    throw new \Exception($message, $errno); //超时 code 28
                }
            } else {
                $success = true;
            }
        } while (!$success && $tryCnt++ < 1);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new \Exception($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['data'=>$data,'code'=>$httpCode];
    }

    public static function doGetCode($url, $optionArray = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); //CURLOPT_HEADER
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
        if (isset($optionArray['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optionArray['referer']);
        }
        $timeOut = 3;
        if (isset($optionArray['timeout'])) {
            $timeOut = intval($optionArray['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optionArray['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optionArray['proxy']);
        }
        if (isset($optionArray['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optionArray['header']);
        }
        if (isset($optionArray['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $optionArray['cookie']);
        }
        if (isset($optionArray['raw'])) {
            curl_setopt($ch, CURLOPT_HEADER, $optionArray['raw']);
        }

        if (isset($optionArray['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optionArray['referer']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 1) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    if ($errno == 28){
                        return ['data'=>[],'code'=>503];
                    }
                    throw new \Exception($message, $errno); //超时 code 28
                }
            } else {
                $success = true;
            }
        } while (!$success && $tryCnt++ < 1);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new \Exception($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $httpCode;
    }

    public static function doGetWithHeader($url, $optionArray=array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true); //CURLOPT_HEADER
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        $timeOut = 1;
        if (isset($optionArray['timeout'])) {
            $timeOut = intval($optionArray['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optionArray['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optionArray['proxy']);
        }
        if (isset($optionArray['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optionArray['header']);
        }
        if ($optionArray['cookie']) {
            curl_setopt($ch, CURLOPT_COOKIE, $optionArray['cookie']);
        }
        if ($optionArray['raw']) {
            curl_setopt($ch, CURLOPT_HEADER, $optionArray['raw']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 1) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    throw new Exception($message, $errno);
                }
            } else {
                $success = true;
            }
        } while (!$success && $tryCnt++ < 1);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new Exception($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($httpCode != 200) {
            $message = "http code:{$httpCode},url:{$url}";
            curl_close($ch);
            throw new Exception($message, $httpCode);
        }
        curl_close($ch);
        $result['header'] = substr($data, 0, $headerSize);
        $result['content'] = substr($data, $headerSize);
        return $result;
    }

    public static function getContent($url, $optionArray = array('timeout'=>10)) {
        return self::doGet($url, $optionArray);
    }

    public static function parseRawData($data) {
        $cookie = '';
        $start = 0;
        $index = strpos($data, "\r\n\r\n");
        $header = substr($data, 0, $index);
        $strData = substr($data, $index + 4);

        do {
            $index = strpos($header, "\r\n", $start);
            if ($index !== false) {
                $line = substr($header, $start, $index - $start);
                $start = $index + 2;
            } else {
                $line = substr($header, $start);
            }
            if (0 === strncasecmp($line, "Set-Cookie: ", strlen("Set-Cookie: "))) {
                $pos = strpos($line, ";");
                $oneCookie = substr($line, strlen("Set-Cookie: "), $pos + 1 - strlen("Set-Cookie: "));
                if (strpos($oneCookie, '=EXPIRED;') === false) {
                    $cookie .= $oneCookie;
                }
            }
        } while ($index && !empty($line));
        return array('data' => $strData, 'cookie' => $cookie);
    }

    /**
     * 获得当前请求的地址
     * @return type
     */
    public static function getThisUrl()
    {
        return "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
    }

    /**
     * 是否是微信
     */
    public static function isWX()
    {
        $isWX = (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'micromessenger') !== false ? true : false);
        return $isWX;
    }


    /**
     * 根据浏览器referer判断来加上跨域请求头
     */
    public static function setACAO()
    {
        if(!empty($_SERVER["HTTP_REFERER"])) {
            $origin = $_SERVER["HTTP_REFERER"];
        } else if(!empty($_SERVER["HTTP_ORIGIN"])) {
            $origin = $_SERVER["HTTP_ORIGIN"];
        } else {
            $origin = "";
        }

        if(preg_match('/^(.*?\.kezhanwang\.cn).*/', $origin, $math)) {
            header("Access-Control-Allow-Origin: ".$math[1]);
            header("Access-Control-Allow-Credentials: true");
        }
    }

    /**
     * curl多线程
     * @param  array  $urls url数组
     * @param  array  $params 参数
     * array(
     *   'delay' => 时间延时 毫秒 ,
     *   'gzip' => gzip压缩 ,
     *   'header' => 请求头,
     *   'proxy_ip' => 代理ip ,
     *   'proxy_port' => 代理ip端口号,
     *   'proxy_userpwd' =>  代理ip账号密码 格式 user:pwd,
     * )
     * @param int $timeout 超时设置
     */
    public static function curl_multi($urls, $params = array(), $timeout = 60)
    {
        $queue = curl_multi_init();
        $map = array();
        foreach($urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面
            curl_setopt($ch, CURLOPT_HEADER, 0); //是否取得头信息
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); //设置超时 秒
            //curl_setopt($ch, CURLOPT_NOSIGNAL, true);

            if($params['gzip']) {
                curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); //针对已gzip压缩过的进行解压，不然返回内容会是乱码
            }

            if($params['header']) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $params['header']); //设置http请求头信息
            }

            if($params['proxy_ip'] && $params['proxy_port']) {
                //curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
                curl_setopt($ch, CURLOPT_PROXY, $params['proxy_ip']); //设置代理ip
                curl_setopt($ch, CURLOPT_PROXYPORT, $params['proxy_port']); //设置代理端口号

                if($params['proxy_userpwd']) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $params['proxy_userpwd']); //设置代理密码
                }
            }

            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $url;
        }
        $responses = array();
        do{
            while(($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);
            if($code != CURLM_OK) {
                break;
            }
            while($done = curl_multi_info_read($queue)) {
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);
                $responses[$map[(string) $done['handle']]] = compact('error', 'results');
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);

                if($params['delay']) {
                    usleep($params['delay'] * 1000);
                }
            }
            if($active > 0) {
                curl_multi_select($queue, 0.5);
                //usleep(1000);
            }
        }while($active);

        curl_multi_close($queue);
        return $responses;
    }
}