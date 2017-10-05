<?php

namespace App\Framework;

use Illuminate\Support\Facades\Cache;

class Curl {

    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:27.0) Gecko/20100101 Firefox/27.0';

    private $_cookies = [];
    private $_headers = [];
    private $_options = [];

    private $_multi_parent = false;
    private $_multi_child = false;
    private $_before_send = null;
    private $_success = null;
    private $_error = null;
    private $_complete = null;
    private $_postfields = null;

    public $curl;
    public $curls;

    public $error = false;
    public $error_code = 0;
    public $error_message = null;

    public $curl_error = false;
    public $curl_error_code = 0;
    public $curl_error_message = null;

    public $http_error = false;
    public $http_status_code = 0;
    public $http_error_message = null;

    public $request_headers = null;
    public $response_headers = null;
    public $response = null;

    public function __construct() {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        $this->curl = curl_init();
        $this->setOpt(CURLOPT_HEADER,         true);
        $this->setOpt(CURLINFO_HEADER_OUT,    true);
        $this->setOpt(CURLINFO_SIZE_DOWNLOAD, true);
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
    }

    public function setDefaults() {
        $this->setUserAgent(self::USER_AGENT);
    }

    public function get($url_mixed, $data=array()) {
        if (is_array($url_mixed)) {
            $curl_multi = curl_multi_init();
            $this->_multi_parent = true;

            $this->curls = array();

            foreach ($url_mixed as $url) {
                $curl = new Curl();
                $curl->_multi_child = true;
                $curl->setOpt(CURLOPT_URL, $this->_buildURL($url, $data), $curl->curl);
                $curl->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
                $curl->setOpt(CURLOPT_HTTPGET, true);
                $this->_call($this->_before_send, $curl);
                $this->curls[] = $curl;

                $curlm_error_code = curl_multi_add_handle($curl_multi, $curl->curl);
                if (!($curlm_error_code === CURLM_OK)) {
                    throw new ErrorException('cURL multi add handle error: ' .
                        curl_multi_strerror($curlm_error_code));
                }
            }

            foreach ($this->curls as $ch) {
                foreach ($this->_options as $key => $value) {
                    $ch->setOpt($key, $value);
                }
            }

            do {
                $status = curl_multi_exec($curl_multi, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

            foreach ($this->curls as $ch) {
                $this->exec($ch);
            }
        }
        else {
            $this->setopt(CURLOPT_URL, $this->_buildURL($url_mixed, $data));
            //$this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
            //$this->setopt(CURLOPT_HTTPGET, true);
            return $this->exec();
        }
    }

    public function post($url, $data=array(), $getdata=array()) {
        $this->setOpt(CURLOPT_URL, $this->_buildURL($url, $getdata));
        //$this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $this->_postfields($data));
        return $this->exec();
    }

    public function put($url, $data=array()) {
        $this->setOpt(CURLOPT_URL, $url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->setOpt(CURLOPT_POSTFIELDS, http_build_query($data));
        return $this->exec();
    }

    public function patch($url, $data=array()) {
        $this->setOpt(CURLOPT_URL, $this->_buildURL($url));
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
        $this->setOpt(CURLOPT_POSTFIELDS, $data);
        return $this->exec();
    }

    public function delete($url, $data=array()) {
        $this->setOpt(CURLOPT_URL, $this->_buildURL($url, $data));
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
        return $this->exec();
    }

    public function setBasicAuthentication($username, $password) {
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }

    public function setHeader($key, $value) {
        $this->_headers[$key] = $key . ': ' . $value;
        $this->setOpt(CURLOPT_HTTPHEADER, array_values($this->_headers));
    }

    public function setUserAgent($user_agent) {
        $this->setOpt(CURLOPT_USERAGENT, $user_agent);
    }

    public function setReferrer($referrer) {
        $this->setOpt(CURLOPT_REFERER, $referrer);
    }

    public function setCookie($key, $value) {
        $this->_cookies[$key] = $value;
        $this->setOpt(CURLOPT_COOKIE, http_build_query($this->_cookies, '', '; '));
    }

    public function setCookieFile($cookie_file) {
        $this->setOpt(CURLOPT_COOKIEFILE, $cookie_file);
    }

    public function setCookieJar($cookie_jar) {
        $this->setOpt(CURLOPT_COOKIEJAR, $cookie_jar);
    }

    public function setOpt($option, $value, $_ch=null) {
        $ch = is_null($_ch) ? $this->curl : $_ch;

        $required_options = array(
            CURLINFO_HEADER_OUT    => 'CURLINFO_HEADER_OUT',
            CURLOPT_HEADER         => 'CURLOPT_HEADER',
            CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER',
        );

        if (in_array($option, array_keys($required_options), true) && !($value === true)) {
            trigger_error($required_options[$option] . ' is a required option', E_USER_WARNING);
        }

        $this->_options[$option] = $value;
        return curl_setopt($ch, $option, $value);
    }

    public function verbose($on=true) {
        $this->setOpt(CURLOPT_VERBOSE, $on);
    }

    public function close() {
        if ($this->_multi_parent) {
            foreach ($this->curls as $curl) {
                $curl->close();
            }
        }

        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    public function beforeSend($function) {
        $this->_before_send = $function;
    }

    public function success($callback) {
        $this->_success = $callback;
    }

    public function error($callback) {
        $this->_error = $callback;
    }

    public function complete($callback) {
        $this->_complete = $callback;
    }

    private function _buildURL($url, $data=array()) {
        return $url . (is_array($data) && $data ? ('?' . http_build_query($data)) : '');
    }

    private function _postfields($data) {

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                // Fix "Notice: Array to string conversion" when $value in
                // curl_setopt($ch, CURLOPT_POSTFIELDS, $value) is an array
                // that contains an empty array.
                if (is_array($value) && empty($value)) {
                    $data[$key] = '';
                }
                // Fix "curl_setopt(): The usage of the @filename API for
                // file uploading is deprecated. Please use the CURLFile
                // class instead".
                else if (is_string($value) && strpos($value, '@') === 0) {
                    $file = substr($value, 1);
                    if (class_exists('CURLFile')) {
                        $data[$key] = new \CURLFile($file);
                    } elseif(function_exists('curl_file_create')) {
                        $finfo = new \finfo(FILEINFO_MIME);
                        $data[$key] = curl_file_create($file, $finfo->file($file));
                    } else {
                        $data[$key] = '@' . $file;
                    }
                }
            }
            $data = self::http_build_multi_query($data);
        }
	    $this->_postfields = $data;
        return $data;
    }

    protected function exec($_ch=null) {
        $ch = is_null($_ch) ? $this : $_ch;

        if ($ch->_multi_child) {
            $ch->response = curl_multi_getcontent($ch->curl);
        }
        else {
            $ch->response = curl_exec($ch->curl);
        }

        $ch->curl_error_code = curl_errno($ch->curl);
        $ch->curl_error_message = curl_error($ch->curl);
        $ch->curl_error = !($ch->curl_error_code === 0);
        $ch->http_status_code = curl_getinfo($ch->curl, CURLINFO_HTTP_CODE);
        $ch->http_error = in_array(floor($ch->http_status_code / 100), array(4, 5));
        $ch->error = $ch->curl_error || $ch->http_error;
        $ch->error_code = $ch->error ? ($ch->curl_error ? $ch->curl_error_code : $ch->http_status_code) : 0;

        $ch->request_headers  = preg_split('/\r\n/', curl_getinfo($ch->curl, CURLINFO_HEADER_OUT), null, PREG_SPLIT_NO_EMPTY);
        $ch->response_headers = '';

        if (!(strpos($ch->response, "\r\n\r\n") === false)) {
            list($response_header, $ch->response) = explode("\r\n\r\n", $ch->response, 2);
            if (
                strpos($response_header, 'HTTP/1.1 100 Continue') === 0
                || (
                    strpos($response_header, 'HTTP/1.1 301 Moved Permanently') === 0
                    && strpos($ch->response, 'HTTP/1.1') === 0)
                || (
                    strpos($response_header, 'HTTP/1.0 200 Connection established') === 0
                    && strpos($ch->response, 'HTTP/1.1') === 0)
                || (
                    strpos($response_header, 'HTTP/1.1 200 OK') === 0
                    && strpos($ch->response, 'HTTP/1.1') === 0)
                ) {
                list($response_header, $ch->response) = explode("\r\n\r\n", $ch->response, 2);
            }
            $ch->response_headers = preg_split('/\r\n/', $response_header, null, PREG_SPLIT_NO_EMPTY);
        }

        $ch->http_error_message = $ch->error ? (isset($ch->response_headers['0']) ? $ch->response_headers['0'] : '') : '';
        $ch->error_message = $ch->curl_error ? $ch->curl_error_message : $ch->http_error_message;

        if (!$ch->error) {
            $ch->_call($this->_success, $ch);
        }
        else {
            $ch->_call($this->_error, $ch);
        }

        $ch->_call($this->_complete, $ch);

        return $ch->error_code;
    }

    private function _call($function) {
        if (is_callable($function)) {
            $args = func_get_args();
            array_shift($args);
            call_user_func_array($function, $args);
        }
    }

    public function __destruct() {
        $this->close();
    }


    private static function is_array_assoc($array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    private static function is_array_multidim($array) {
        if (!is_array($array)) {
            return false;
        }

        return !(count($array) === count($array, COUNT_RECURSIVE));
    }

    private static function http_build_multi_query($data, $key=null) {
        $query = [];

        if (empty($data)) {
            return $key . '=';
        }

        $is_array_assoc = self::is_array_assoc($data);

        foreach ($data as $k => $value) {
            if (is_string($value) || is_numeric($value)) {
                $brackets = $is_array_assoc ? '[' . $k . ']' : '[]';
                $query[] = urlencode(is_null($key) ? $k : $key . $brackets) . '=' . rawurlencode($value);
            }
            else if (is_array($value)) {
                $nested = is_null($key) ? $k : $key . '[' . $k . ']';
                $query[] = self::http_build_multi_query($value, $nested);
            }
        }

        return implode('&', $query);
    }

    private static function _fixURL($Url)
    {
        $Url = parse_url($Url);
        $Url['path'] = isset($Url['path']) ? $Url['path'] : null;

        $Protocols = [
            'https', 'http', 'ftp',
        ];

        if(!isset($Url['scheme']) || !$Url['scheme'] || !in_array($Url['scheme'], $Protocols)) {
            $Url['scheme'] = 'http';
        }

        $Path = preg_split('|[\/]+|', $Url['path'], -1, PREG_SPLIT_NO_EMPTY);
        if((!isset($Url['host']) || !$Url['host']) && $Url['path']) {
            $Url['host'] = array_shift($Path);
        }

        if(!$Url['host']) {
            return null;
        }

        return $Url['scheme'] . '://'
            . (isset($Url['user']) ? $Url['user'] . (isset($Url['pass']) ? ':' . $Url['pass'] : '') . '@' : '')
            . $Url['host']
            . ((isset($Url['port']) && $Url['port']) ? (':' . $Url['port']) : '')
            . $Url['path']
            . (isset($Url['query']) ? '?' . $Url['query'] : '')
            . (isset($Url['fragment']) ? $Url['fragment'] : '');
    }

    static function cachedRequest($time, $Url, $GET=[], $POST=[], $COOKIE=[], $HEADER=[], $Opt=[], $retCurl=false)
    {
        $args = func_get_args();
        $time = array_shift($args);

        $id = 'app:curl:' . md5(json_encode($args));

        $cache = Cache::get($id);
        if ($cache) {
            return $cache;
        }

        $data  = call_user_func_array([__CLASS__, 'request'], $args);
        if ($data) {
            Cache::put($id, $data, $time);
        }
        return $data;
    }

    static function request($Url, $GET=[], $POST=[], $COOKIE=[], $HEADER=[], $Opt=[], $retCurl=false)
    {

        $Url = self::_fixURL($Url);
        if(!$Url) {
            return null;
        }

        $Curl = new self();

        $Curl->setCookieFile(__DIR__ . '/../../storage/app/curl.cookie');
        $Curl->setCookieJar(__DIR__ . '/../../storage/app/curl.cookie');

        $Curl->setUserAgent(self::USER_AGENT);
        $Curl->setReferrer($Url);

        $Curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $Curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $Curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
        $Curl->setOpt(CURLOPT_CONNECTTIMEOUT, 4);
        $Curl->setOpt(CURLOPT_MAXREDIRS, 5);

        if($HEADER && is_array($HEADER)) {
            foreach ($HEADER as $h => $v) {
                $Curl->setHeader($h, $v);
            }
        }

        if($COOKIE && is_array($COOKIE)) {
            foreach ($COOKIE as $h => $v) {
                $Curl->setCookie($h, $v);
            }
        }

        if($Opt) {
            foreach($Opt AS $C=>$V) {
                $C      = strtoupper($C);
                $Const  = 'CURLOPT_' . strtoupper($C);
                if(intval($C)) {
                    $Curl->setOpt($C, $V);
                } elseif (defined($C)) {
                    $Curl->setOpt(constant($C), $V);
                } elseif (defined($Const)) {
                    $Curl->setOpt(constant($Const), $V);
                }
            }
        }

        if($POST) {
            $Curl->post($Url, $POST, $GET);
        } else {
            $Curl->get($Url, $GET);
        }

        return $retCurl
            ? $Curl
            : $Curl->response;
    }

}
