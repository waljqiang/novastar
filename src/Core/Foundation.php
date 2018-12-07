<?php
namespace NovaStar\Api\Core;
use \Doctrine\Common\Cache\Cache;
use \Pimple\Container;
use \Doctrine\Common\Cache\FilesystemCache;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Foundation extends Container{
	/**
	 * an array of service providers.
	 *
	 * @var
	 */
    protected $providers = [];

    public function __construct($config){
    	parent::__construct();
    	$this['config'] = function () use ($config) {
            return new Config($config);
        };
        $this->registerBase();
        $this->registerProviders();
    }

    private function registerBase(){
    	if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof Cache) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(dirname(dirname(dirname(__FILE__))) . '/runtime/cache');
            };
        }
    }

    private function registerProviders(){
        $this->registerLoggerProvider();
    }

    private function registerLoggerProvider(){
        if(!isset($this['config']['log'])){
            $logFile = dirname(dirname(dirname(__FILE__))) . '/runtime/log/novastar.log';
            $logLevel = 'ERROR';
        }else{
            $logFile = $this['config']['log']['path'];
            $logLevel = $this['config']['log']['level'];
        }
        $log = new Logger('novastar');
        $log->pushHandler(new StreamHandler($logFile,$logLevel));
        $this['logger'] = $log;
    }

    public function getCache(){
    	return $this['cache'];
    }

    public function getLogger(){
        return $this['logger'];
    }

    public function getNode(){
        return $this['config']['node'];
    }

    public function getConfig(){
        return $this['config'];
    }

}