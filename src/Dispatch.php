<?php
/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-17
 * Time: 上午11:56
 */

namespace NovaStar\Api;

use NovaStar\Api\Requests\ApiRequest;
use NovaStar\Api\Core\Http;
use NovaStar\Api\Core\AccessToken;
use NovaStar\Api\Core\Foundation;

class Dispatch extends Foundation{
    private $ApiRequest;

    public function __construct($config){
        parent::__construct($config);
        $http = new Http($this);
        $accessToken = new AccessToken($http);
        $this->ApiRequest = new ApiRequest($accessToken,$http);
    }

    public function __call($name,$arguments){
        return $this->ApiRequest->{$name}(...$arguments);
    }
}