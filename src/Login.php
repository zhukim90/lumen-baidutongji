<?php

namespace Bruin\BaiduTongji;

class Login
{
    const LOGIN_URL = 'https://api.baidu.com/sem/common/HolmesLoginService';

    const PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
EH6YWHP4reFfpMpLzwIDAQAB
-----END PUBLIC KEY-----';

    private $public_key_resource;

    private $config;

    private $header;

    public $ucid, $st;

    private $error_code = [
        2 => 'INVALID_ENCODING: 请求数据的编码错误，非UTF-8',
        3 => 'DAMAGED_DATA: 请求数据损坏',
        4 => 'DATA_TOO_LARGE: 请求数据过大',
        6 => 'INVALID_REQUEST: 请求数据不符合规范',
        7 => 'FUNCTION_NOT_SUPPORTED: 未知的functionName',
        8 => 'DAMAGED_RESPONSE : 响应数据损坏',
        9 => 'INVALID_TOKEN: token无效',
        10 => 'INVALID_USER: 用户无效',
        11 => 'ERROR_PROCESSING: 登录请求处理异常',
        12 => 'INVALID_ACCOUNTTYPE: 账户类型无效'
    ];

    public function __construct($config)
    {
        $this->config = $config;
        $this->header = [
            'UUID:' . $this->uuid,
            'account_type:' . $this->account_type,
            'Content-Type:data/gzencode and rsa public encrypt;charset=UTF-8'
        ];

        $this->public_key_resource = openssl_pkey_get_public(self::PUBLIC_KEY);
    }

    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }

    public function __set($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    public function preLogin()
    {
        $post_data = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'preLogin',
            'uuid' => $this->uuid,
            'request' => [
                'osVersion' => 'windows',
                'deviceType' => 'pc',
                'clientVersion' => '1.0',
            ]
        ];

        $post_data = $this->encry($post_data);

        $result = curl_post(self::LOGIN_URL, $post_data, $this->header);
        $result = $this->responseHandle($result);

        if ($result['code'] != 0) {
            $error_msg = array_key_exists($result['code'], $this->error_code) ? $this->error_code[$result['code']] : '未知错误';
            throw new \Exception($error_msg);
        }
        return true;
    }

    public function doLogin()
    {
        $post_data = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogin',
            'uuid' => $this->uuid,
            'request' => [
                'password' => $this->password
            ]
        ];

        $post_data = $this->encry($post_data);

        $result = curl_post(self::LOGIN_URL, $post_data, $this->header);
        $result = $this->responseHandle($result);

        if ($result['code'] != 0) {
            $error_msg = array_key_exists($result['code'], $this->error_code) ? $this->error_code[$result['code']] : '未知错误';
            throw new \Exception($error_msg);
        }

        if ($result['data']['retcode'] != 0) {
            throw new \Exception($result['data']['retmsg']);
        }

        $this->ucid = $result['data']['ucid'];
        $this->st = $result['data']['st'];

        return true;
    }

    public function doLogout()
    {
        $post_data = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogout',
            'uuid' => $this->uuid,
            'request' => [
                'ucid' => $this->ucid,
                'st' => $this->st
            ]
        ];

        $post_data = $this->encry($post_data);

        $result = curl_post(self::LOGIN_URL, $post_data, $this->header);
        $result = $this->responseHandle($result);

        if ($result['code'] != 0) {
            $error_msg = array_key_exists($result['code'], $this->error_code) ? $this->error_code[$result['code']] : '未知错误';
            throw new \Exception($error_msg);
        }

        return true;
    }

    public function encry($data)
    {
        $post_data = '';

        $data = gzencode(json_encode($data), 9);

        $len = strlen($data);

        for ($i = 0; $i < $len; $i += 117) {
            $ret = openssl_public_encrypt(substr($data, $i, 117), $encrypted, $this->public_key_resource);
            if ($ret) {
                $post_data .= $encrypted;
            } else {
                throw new \Exception('秘钥错误');
            }
        }

        return $post_data;
    }

    public function responseHandle($data)
    {
        $result['data'] = '';
        $result['code'] = ord($data[0]) * 64 + ord($data[1]);

        if ($result['code'] === 0) {
            $result['data'] = substr($data, 8);

            $result['data'] = json_decode(gzinflate(substr($result['data'], 10, -8)), true);
        }

        return $result;
    }
}