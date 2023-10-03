<?php
/**
 * Created by PhpStorm.
 * User: Bruin
 * Date: 2016/11/26
 * Time: 12:50
 */

namespace Bruin\BaiduTongji;

use Bruin\BaiduTongji\Login;
use Cache;

class BaiduTongji
{
    const API_URL = 'https://api.baidu.com/json/tongji/v1/ReportService';

    private $config;

    private $login;

    private $header, $post_header;

    public function __construct()
    {
        $this->config = config('baidu_tongji');
        $login = $this->login();

        $this->header = [
            'UUID:' . $this->uuid,
            'USERID:' . $login['ucid'],
            'Content-Type:data/json;charset=UTF-8'
        ];

        $this->post_header = [
            'username' => $this->username,
            'password' => $login['st'],
            'token' => $this->token,
            'account_type' => $this->account_type
        ];
    }

    private function login()
    {
        return Cache::remember('baiduTongji-key', 30, function () {
            $this->login = new Login($this->config);
            $this->login->preLogin();
            $this->login->doLogin();

            return [
                'ucid' => $this->login->ucid,
                'st' => $this->login->st
            ];
        });
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

    public function getSiteLists($is_concise = false)
    {
        $result = Cache::remember('baiduTongji-siteLists', 30, function () {
            return $this->request('getSiteList', null);
        });

        if (empty($result['list'])) {
            throw new \Exception('没有站点');
        }

        $list = $result['list'];

        if ($is_concise) {
            $list = collect($list)->pluck('domain', 'site_id')->toArray();
        }

        return $list;
    }

    public function getData($param = array())
    {
        if (!isset($param['site_id'])) {
            $list = $this->getSiteLists();
            $param['site_id'] = $list[0]['site_id'];
        }

        $result = $this->request('getData', $param);

        return $result['result'];
    }

    private function request($type, $post_data)
    {
        $post_data = [
            'header' => $this->post_header,
            'body' => $post_data
        ];

        $result = curl_post(self::API_URL . '/' . $type, json_encode($post_data), $this->header);
        $result = json_decode($result, true);

        if ($result['header']['status'] != 0) {
            $failure = $result['header']['failures'][0];
            $message = 'level:' . $result['header']['desc'] . ';code:' . $failure['code'] . ';message:' . $failure['message'];
            throw new \Exception($message);
        }


        return $result['body']['data'][0];
    }
}