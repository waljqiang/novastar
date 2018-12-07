<?php
namespace NovaStar\Api\Core;

use \Illuminate\Support\Collection;

class Config extends Collection{

    public function __construct($config){
        $file = __DIR__ . "/env-" . $config["node"] . ".php";
        if(!file_exists($file)){
            die("The node is not exsist!");
        }
        $node = require_once $file;
        $config['node'] = $config["node"];
    	$config['auth_server'] = $node["AUTH_SERVER"];
    	$config['api_server'] = $node["API_SERVER"];
    	parent::__construct($config);
    }
}