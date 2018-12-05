<?php
namespace NovaStar\Api\Core;

use \Illuminate\Support\Collection;

class Config extends Collection{
	/**
     * 认证服务地址
     */
    const AUTH_SERVER = 'http://192.168.33.11:8022/';
    /**
     * api服务地址
     */
    const API_SERVER = 'http://www.novaicare.local/';

    public function __construct($config){
    	$config['auth_server'] = self::AUTH_SERVER;
    	$config['api_server'] = self::API_SERVER;
    	parent::__construct($config);
    }
}