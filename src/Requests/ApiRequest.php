<?php

namespace NovaStar\Api\Requests;

use NovaStar\Api\Core\AbstractRequest;

/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-15
 * Time: 下午4:32
 */
class ApiRequest extends AbstractRequest{

    const API_USER_TAGS = 'new/backend/api/user/tags';
    const API_SCREEN_LIST = 'new/backend/api/screen/lists';
    const API_SCREEN_SPOTCHECKS = 'new/backend/api/screen/spotcheck';
    const API_SCREEN_MONITORS = 'new/backend/api/screen/monitor';
    const API_SCREEN_IMAGES = 'new/backend/api/screen/images';
    const API_SCREEN_THUMBNAILS = 'new/backend/api/screen/thumbnails';

    public function getTags(){
        return $this->parseJSON('get',[self::API_USER_TAGS]);
    }

    public function getScreenList($params = []){
        return $this->parseJSON('post',[self::API_SCREEN_LIST,$params]);
    }

    public function getSpotChecks($params){
        return $this->parseJSON('get',[self::API_SCREEN_SPOTCHECKS,$params]);
    }

    public function getMonitors($params){
        return $this->parseJSON('get',[self::API_SCREEN_MONITORS,$params]);
    }

    public function getImages($params){
        return $this->parseJSON('post',[self::API_SCREEN_IMAGES,$params]);
    }

    public function getThumbnails($params){
        return $this->parseJSON('post',[self::API_SCREEN_THUMBNAILS,$params]);
    }

}