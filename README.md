NovaStar SDK
=============

Open API SDK
-------------
#安装
* composer require novastar/auth-api

#使用方法
* 在项目中引入SDK自动加载文件
* 实例NovaStar\Api\Dispatch类
* 通过Dispatch类调用各API方法
* 比如获取用户标签:
```PHP
	require_once dirname(__DIR__) . "/vendor/autoload.php";
	use NovaStar\Api\Dispatch;
	use Doctrine\Common\Cache\FilesystemCache;
	use NovaStar\Api\Exceptions\HttpException;
	$apiDispatch = new Dispatch(
		[
			"app_id" => "3f6deaafb426e72dc88addda6423190ca18efd7287a1e311417da7ee",
			"app_secret" => "E478IXfjQ5Exf6kCCMEIuyvFKSkRdimWxSmQvByV",
			"scope" => "",
			"cache" => new FilesystemCache(dirname(dirname(__FILE__)) . '/runtime/cache'),
			"log" => [
				"level" => "ERROR",
				"path" => dirname(dirname(__FILE__)) . "/runtime/log/novastar.log"
			]
		]
	);

	try{
		$tags = $apiDispatch->getTags();
		echo '<pre>';
		print_r($tags);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
```
* 具体请参考Demon中test.php

#目前提供的API方法
* 获取用户标签列表
	* getTags()
* 获取显示屏列表
	* getScreenList()
* 获取单个屏体点检信息
	* getSpotChecks()
* 获取单个屏体监控数据
	* getMonitors()
* 获取屏体监控图片-原始图
	* getImages()
* 获取屏体监控图片-缩略图
	* getThumbnails()

#单元测试
* 进入tests目录
* 执行以下命令
	* phpunit -c phpunit.xml ./Units/DispatchTest.php
