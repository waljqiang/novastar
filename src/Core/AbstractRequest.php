<?php

namespace NovaStar\Api\Core;

use \GuzzleHttp\Middleware;
use \GuzzleHttp\Psr7\Uri;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \NovaStar\Api\Exceptions\HttpException;
use \GuzzleHttp\Exception\ConnectException;
use \GuzzleHttp\Exception\ClientException;
/**
 * Class AbstractAPI.
 */
abstract class AbstractRequest
{
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;
    /**
     * The request token.
     *
     * @var AccessToken
     */
    protected $accessToken;
    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';
    /**
     * @var int
     */
    protected static $maxRetries = 3;

    protected $logger;

    /**
     * Constructor.
     *
     * @param Http $http
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken, Http $http){
        $this->logger = $http->getDispatch()->getLogger();
        $this->setAccessToken($accessToken);
        $this->setHttp($http);
    }
    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp(){
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (count($this->http->getMiddlewares()) === 0) {
            $this->registerHttpMiddlewares();
        }
        return $this->http;
    }
    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;
        return $this;
    }
    /**
     * Return the current accessToken.
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
    /**
     * Set the request token.
     *
     * @param AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }
    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }
    /**
     * Parse JSON from response and check error.
     *
     * @param string $method
     * @param array  $args
     *
     * @return array
     */
    public function parseJSON($method, array $args){
        $this->method = $method;
        $this->args = $args;
        $http = $this->getHttp();
        $field = $this->accessToken->getQueryName();
        try{
            $token = $this->accessToken->getToken();
            $this->logger->debug('access_token is . [' . $token . ']');
        }catch(\Exception $e){
            $this->tansHttpException($e);
        }
        $args[0] = $http->getApiServer() . $args[0];
        $args[1][$field] = $token;
        try{
            $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));
        }catch(\Exception $e){
            $this->tansHttpException($e);
        }
        $this->checkAndThrow($contents);
        return $contents;
    }
    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares(){
        $this->http->addMiddleware($this->retryMiddleware());
    }

    /**
     * retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware(){
        return function(callable $handler){
            return function (RequestInterface $request,array $options) use ($handler){
                $promise = $handler($request,$options);
                return $promise->then(function(ResponseInterface $response) use ($request,$handler,$options){
                    $body = $response->getBody();
                    $retries = 0;
                    if($retries < self::$maxRetries && stripos($body, 'errorCode') && stripos($body, '400109')){
                        $retries += 1;
                        $field = $this->accessToken->getQueryName();
                        $token = $this->accessToken->getToken(true);
                        $this->logger->debug('new access_token is [' . $token . ']');
                        $request = $request->withUri($newUri = Uri::withQueryValue($request->getUri(), $field, $token));
                        $this->logger->debug('newUri is [' . var_export($request,true) . ']');
                        return $handler($request,$options);
                    }
                    
                    return $response;
                });
            };
        };
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     *
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents){
        $this->logger->debug('response content is [' .var_export($contents,true) . ']');
        if (100000 !== $contents['errorCode']) {    
            if (!isset($contents['message'])) {
                $contents['message'] = 'Unknown';
            }
            if(!isset($contents['hint'])){
                $contents['hint'] = 'Unknown';
            }
            $message = $contents['message'] . ',' . $contents['hint'];
            $this->logger->error('response error is [' . var_export($contents,true) . ']');
            throw new HttpException($message, $contents['errorCode']);
        }
    }

    protected function tansHttpException($e){
        if($e instanceof ConnectException){
            throw new HttpException('The server of this code is breakdown',HttpException::NODEBAD);
        }elseif($e instanceof ClientException){
            $body = json_decode($e->getResponse()->getBody(),true);
            $message = $body['error'] . ',' . $body['message'];
            switch(strtoupper($body['error'])){
                case 'INVALID_CLIENT':
                    $code = HttpException::INVALID_CLIENT;
                    break;
                case 'INVALID_SCOPE':
                    $code = HttpException::INVALID_SCOPE;
                    break;
                default:
                    $code = HttpException::UNKNOWN;
            }
            throw new HttpException($message, $code);
        }else{
            $body = json_decode($e->getResponse()->getBody(),true);
            $message = $body['error'] . ',' . $body['message'];
            throw new HttpException($message,$e->getCode());
        }
    }
}