<?php
/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-15
 * Time: 上午11:19
 */

namespace NovaStar\Api\Core;

use NovaStar\Auth\Exceptions\HttpException;

/**
 * Class AccessToken.
 */
class AccessToken{
    /**
     * App ID.
     *
     * @var string
     */
    protected $appId;
    /**
     * App secret.
     *
     * @var string
     */
    protected $secret;
    protected $scope;
    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;
    /**
     * Cache Key.
     *
     * @var string
     */
    protected $cacheKey;
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;
    /**
     * Query name.
     *
     * @var string
     */
    protected $queryName = 'access_token';
    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';
    /**
     * Cache key prefix.
     *
     * @var string
     */
    protected $prefix = 'novaauth.access_token';
    // API
    const API_TOKEN = 'oauth/token';

    const TOKEN_EXPIRE_PRE = '200';

    /**
     * Constructor.
     * @param Http $http
     */
    public function __construct(Http $http){
        $this->appId = $http->getAppId();
        $this->secret = $http->getSecret();
        $this->scope = $http->getScope();
        $this->cache = $http->getDispatch()->getCache();
        $this->setHttp($http);
    }

    /**
     * Get token from WeChat API.
     *
     * @param bool $forceRefresh
     *
     * @return string
     */
    public function getToken($forceRefresh = false){
        $cacheKey = $this->getCacheKey();
        $cached = $this->getCache()->fetch($cacheKey);
        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();
            // XXX: T_T... 7200 - 1500
            $this->getCache()->save($cacheKey, $token['access_token'],$token['expires_in']-self::TOKEN_EXPIRE_PRE);
            return $token['access_token'];
        }
        return $cached;
    }

    /**
     * 设置自定义 token.
     *
     * @param string $token
     * @param int $expires
     *
     * @return $this
     */
    public function setToken($token,$expire=7200){
        $this->getCache()->save($this->getCacheKey(), $token, $expire-self::TOKEN_EXPIRE_PRE);
        return $this;
    }

    /**
     * Return the app id.
     *
     * @return string
     */
    public function getAppId(){
        return $this->appId;
    }

    /**
     * Return the secret.
     *
     * @return string
     */
    public function getSecret(){
        return $this->secret;
    }

    /**
     * Set cache instance.
     *
     * @param Cache $cache
     *
     * @return AccessToken
     */
    public function setCache(Cache $cache){
        $this->cache = $cache;
        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return Cache
     */
    public function getCache(){
        return $this->cache;
    }

    /**
     * Set the query name.
     *
     * @param string $queryName
     *
     * @return $this
     */
    public function setQueryName($queryName){
        $this->queryName = $queryName;
        return $this;
    }

    /**
     * Return the query name.
     *
     * @return string
     */
    public function getQueryName(){
        return $this->queryName;
    }

    /**
     * Return the API request queries.
     *
     * @return array
     */
    public function getQueryFields(){
        return [$this->queryName => $this->getToken()];
    }

    /**
     * Get the access token from WeChat server.
     *
     * @throws HttpException
     *
     * @return array
     */
    public function getTokenFromServer(){
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->appId,
            'client_secret' => $this->secret,
            'scope' => $this->scope
        ];
        $http = $this->getHttp();
        $token = $http->parseJSON($http->json($http->getAuthServer() . self::API_TOKEN, $params));
        if (isset($token['error'])) {
            throw new HttpException('Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }
        return $token;
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp(){
        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http){
        $this->http = $http;
        return $this;
    }

    /**
     * Set the access token prefix.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix){
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Set access token cache key.
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey($cacheKey){
        $this->cacheKey = $cacheKey;
        return $this;
    }

    /**
     * Get access token cache key.
     *
     * @return string $this->cacheKey
     */
    public function getCacheKey(){
        if (is_null($this->cacheKey)) {
            return $this->prefix . $this->appId;
        }
        return $this->cacheKey;
    }
}