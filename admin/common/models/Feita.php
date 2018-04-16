<?php
/**
 * Created by PhpStorm.
 * User: qk
 * Date: 15-12-22
 * Time: 上午10:07
 */

namespace common\models;

use Yii;


class Feita {

    public function getToken($url, $username, $secretKey, $method = 'POST'){
        $data = [
            'username' => $username,
            'secretKey' => $secretKey
        ];
        //生成url-encode后的请求字符串，将数组转换为字符串
        $data = http_build_query($data);
        $opts = array (
            'http' => array (
                'method' => $method,
                'header'=> "Content-type: application/x-www-form-urlencodedrn" .
                "Content-Length: " . strlen($data) . "rn",
                'content' => $data
            )
        );
        //生成请求的句柄文件
        $context = stream_context_create($opts);
        file_get_contents($url, false, $context);
        $responseInfo = $http_response_header;
        //var_dump($responseInfo);exit;
        $cookie_aps = str_replace('Set-Cookie:', '',$responseInfo[2]);
        $cookie_aps = str_replace('path=/; HttpOnly', '',$cookie_aps);
        $cookie_aps = trim(str_replace(';', '',$cookie_aps));

        $cookie_csrf = str_replace('Set-Cookie:', '',$responseInfo[3]);
        $cookie_csrf = str_replace('path=/', '',$cookie_csrf);
        $cookie_csrf = trim(str_replace(';', '',$cookie_csrf));
        $token = trim(str_replace('ccsrftoken=', '', $cookie_csrf),'""');
        $cookie = [$cookie_csrf,$cookie_aps];
        return [$token, $cookie];
    }

    public function addObject($user_name, $ip, $url, $token, $cookie){
        $data = '{
               "vdom" : "root",
               "json" : {
                  "name" : "'. $user_name .'",
                  "subnet" : "'. $ip .'"
               }
            }';
        $header = [
            'Content-Type: application/json',
            'X-CSRFTOKEN: '.$token
        ];
        //$res = $this->postData($url, $header, $cookie, $data);
        $res = $this->postApi($url, $token, $cookie, $data);
        return $res;
    }

    /**
     * 开启飞塔路由接口
     * @param $user_id
     * @param $user_name
     * @param $url
     * @param $token
     * @param $cookie
     * @return array
     */
    public function routeApiOpen($user_id, $user_name, $url, $token, $cookie){
        $policyid = intval($user_id)+1000;
        $data = '{
                    "json" : {
                      "schedule" : "always",
                      "status" : "enable",
                      "srcintf" : [
                         {
                            "name" : "any"
                         }
                      ],
                      "action" : "accept",
                      "srcaddr" : [
                         {
                            "name" : "'.$user_name.'"
                         }
                      ],
                      "dstaddr" : [
                         {
                            "name" : "all"
                         }
                      ],
                      "dstintf" : [
                         {
                            "name" : "any"
                         }
                      ],
                      "policyid" : '.$policyid.',
                      "service" : [
                         {
                            "name" : "ALL"
                         }
                      ],
                      "logtraffic" : "all",
                      "nat" : "enable"
                    }
                }';
        $header = [
            'Content-Type: application/json',
            'X-CSRFTOKEN: '.$token
        ];
        //$res = $this->postData($url, $header, $cookie, $data);
        $res = $this->postApi($url, $token, $cookie, $data);
        return $res;
    }

    public function routeApiClose($url, $token, $cookie){
        return $this->postApi($url, $token, $cookie,'','DELETE');
    }

    private function getResult($res){
        $id = 0;
        $msg = '';
        if($res){
            if($msg = json_decode($res, true)){
                $id = 200;
                $msg = isset($msg['status']) ? $msg['status'] : '';
            }
        }
        return ['id' => $id, 'msg' => $msg];
    }
    public function postData($url, $header, $cookie, $data){
        $cookies_string = '';
        foreach($cookie as $value){
            $cookies_string .= $value.'; ';
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($cookies_string){
            curl_setopt($ch, CURLOPT_COOKIE, $cookies_string);
        }
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        //$this->curl_redir_exec($ch);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $this->getResult($httpCode, $data);
    }
    function postApi($url, $token, $cookie, $data, $method='POST'){
        $cookies_string = '';
        if($cookie){
            foreach($cookie as $value){
                $cookies_string .= $value.'; ';
            }
        }

        $opts = array();
        $opts['http'] = array();
        $headers = array(
            "method" => $method,
        );
        $headers['header'] = array();
        $headers['header'][]= 'Content-Type: application/json;charset=utf-8';
        $headers['header'][]= 'X-CSRFTOKEN: '.$token.'';
        if($cookies_string){
            $headers['header'][]= 'Cookie: '.$cookies_string;
        }

        if(!empty($data)) {
            $headers['header'][]= 'Content-Length:'.strlen($data);
            $headers['content']= $data;
        }
        $opts['http'] = $headers;
        $res = file_get_contents($url, false, stream_context_create($opts));
        return $this->getResult($res);
    }
    public function udp_send($json) //发认证报文到UDP认证端口
    {
        $api_ip = Yii::$app->params['online_ip'] ? Yii::$app->params['online_ip'] : '127.0.0.1';
        $buf="";
        $from="";
        $port=0;
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $len = strlen($json);
        socket_sendto($sock, $json, $len, 0, $api_ip, 3359);
        socket_recvfrom($sock, $buf, 1024, 0, $from, $port);
        socket_close($sock);
        return $buf;
    }

    function get_domain($condition)
    {
        if(preg_match('/domain=([^[:space:]]+)/', $condition, $arr))
            return $arr[1];
    }

    function curl_redir_exec($ch,$debug="")
    {
        static $curl_loops = 0;
        static $curl_max_loops = 20;

        if ($curl_loops++ >= $curl_max_loops)
        {
            $curl_loops = 0;
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $debbbb = $data;
        list($header, $data) = explode("\n\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            //print_r($url);
            if (!$url)
            {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
            curl_setopt($ch, CURLOPT_URL, $new_url);
            //    debug('Redirecting to', $new_url);

            return curl_redir_exec($ch);
        } else {
            $curl_loops=0;
            return $debbbb;
        }
    }
}