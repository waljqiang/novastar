<?php
/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-15
 * Time: 上午11:24
 */


namespace NovaStar\Api\Core;

use \Carbon\Carbon;
use \NovaStar\Api\Exceptions\HttpException;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Client as HttpClient;
use \NovaStar\Api\Utils\Signature;
use \Psr\Http\Message\ResponseInterface;
use \NovaStar\Api\Core\Config;
use \NovaStar\Api\Dispatch;

/**
 * Class Http.
 */
class Http
{
    /**
     * Used to identify handler defined by client code
     * Maybe useful in the future.
     */
    const USER_DEFINED_HANDLER = 'userDefined';
    /**
     * API Version
     * Current version is 1.0
     *
     * @var string
     */
    const VER = '1.0';
    /**
     * Http client.
     *
     * @var HttpClient
     */
    protected $client;
    /**
     * The middlewares.
     *
     * @var array
     */
    protected $middlewares = [];
    /**
     * Guzzle client default settings.
     *
     * @var array
     */
    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    /**
     * base uri
     * @var [type]
     */
    protected $base_uri;

    /**
     * NovaStar\Api\Dispatch
     * @var [type]
     */
    private $dispatch;
    private $appId;
    private $appSecret;
    private $scope = '';

    /**
     * Http constructor.
     * @param $config
     */
    public function __construct(Dispatch $dispatch){
        $this->dispatch = $dispatch;
        $this->appId = $dispatch['config']['app_id'];
        $this->appSecret = $dispatch['config']['app_secret'];
        if(isset($dispatch['config']['scope']))
            $this->scope = $dispatch['config']['scope'];
    }


    /**
     * Set guzzle default settings.
     *
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    /**
     * Return current guzzle default settings.
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaults;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function get($url, array $options = []){
        return $this->request($url, 'GET', ['query' => $options]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array|string $options
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function post($url, $options = []){
        $key = is_array($options) ? 'form_params' : 'body';
        $query = array_slice($options, -1,1,true);
        array_pop($options);
        return $this->request($url, 'POST', ['query'=>$query,$key => $options]);
    }

    /**
     * JSON request.
     *
     * @param string $url
     * @param string|array $options
     * @param array $queries
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function json($url, $options = [], $queries = []){
        $time = Carbon::now('UTC')->timestamp;
        $nonce = md5($time);
        $appSecret = $this->appSecret;
        $params = $options;
        $sign = Signature::sign($options, $time, $nonce, $appSecret);
        $system = [
            'ver' => self::VER,
            'sign' => $sign,
            'appId' => $this->appId,
            'time' => $time,
            'nonce' => $nonce
        ];
        $options = compact('system');
        $options = array_merge($params,$options);
        return $this->request($url, 'POST', ['query' => $queries, 'json' => $options]);
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array $files
     * @param array $form
     *
     * @param array $queries
     * @return ResponseInterface
     */
    public function upload($url, array $files = [], array $form = [], array $queries = [])
    {
        $multipart = [];
        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }
        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }
        return $this->request($url, 'POST', ['query' => $queries, 'multipart' => $multipart]);
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param \GuzzleHttp\Client $client
     *
     * @return Http
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient();
        }
        return $this->client;
    }

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     *
     * @return $this
     */
    public function addMiddleware(callable $middleware)
    {
        array_push($this->middlewares, $middleware);
        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Make a request.
     *
     * @param string $url
     * @param string $method
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws HttpException
     */
    public function request($url, $method = 'GET', $options = []){
        $method = strtoupper($method);
        $options = array_merge(self::$defaults, $options);
        $options['handler'] = $this->getHandler();
        $response = $this->getClient()->request($method, $url, $options);
        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface|string $body
     *
     * @return mixed
     *
     * @throws \HttpException
     */
    public function parseJSON($body)
    {
        if ($body instanceof ResponseInterface) {
            $body = $body->getBody();
        } 
        // XXX: json maybe contains special chars. So, let's FUCK the WeChat API developers ...
        $body = $this->fuckInvalidJSON($body);
        if (empty($body)) {
            return false;
        }
        $contents = json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new HttpException('Failed to parse JSON: ' . json_last_error_msg());
        }
        return $contents;
    }

    /**
     * Filter the invalid JSON string.
     *
     * @param \Psr\Http\Message\StreamInterface|string $invalidJSON
     *
     * @return string
     */
    protected function fuckInvalidJSON($invalidJSON)
    {
        return preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($invalidJSON));
    }

    /**
     * Build a handler.
     *
     * @return HandlerStack
     */
    protected function getHandler()
    {
        $stack = HandlerStack::create();
        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }
        if (isset(static::$defaults['handler']) && is_callable(static::$defaults['handler'])) {
            $stack->push(static::$defaults['handler'], self::USER_DEFINED_HANDLER);
        }
        return $stack;
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
        return $this->appSecret;
    }

    public function getScope(){
        return $this->scope;
    }

    public function getDispatch(){
        return $this->dispatch;
    }

    public function getAuthServer(){
        return $this->dispatch['config']['auth_server'];
    }

    public function getApiServer(){
        return $this->dispatch['config']['api_server'];
    }
}