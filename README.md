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
	require_once __DIR__ . "/vendor/autoload.php";
	use NovaStar\Api\Dispatch;
	use Doctrine\Common\Cache\FilesystemCache;
	use NovaStar\Api\Exceptions\HttpException;
	$apiDispatch = new Dispatch(
		[
			"app_id" => "3f6deaafb426e72dc88addda6423190ca18efd7287a1e311417da7ee",
			"app_secret" => "E478IXfjQ5Exf6kCCMEIuyvFKSkRdimWxSmQvByV",
			"scope" => "",
			"cache" => new FilesystemCache(dirname(__DIR__ . '/runtime/cache')),
			"log" => [
				"level" => "ERROR",
				"path" => __DIR__ . "/runtime/log/novastar.log"
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
	* getScreenList(["status"=>2,"label"=>222,"search"=>"测试屏"])
	* 参数说明

		|参数名|必选|类型|默认值|说明|
		|:----    |:---|:----- |:--- |:-----   |
		|status |false  |int |无 |屏体状态1：在线2：离线3：告警4：故障 |
		|label |false  |int |无 |标签ID |
		|search |false |string |无 |搜索关键字,支持屏体名称和地址查找 |
* 获取单个屏体点检信息
	* getSpotChecks(["sid"=>"1111"])
	* 参数说明

		|参数名|必选|类型|默认值|说明|
		|:----    |:---|:----- |:--- |:-----   |
		|sid |true  |string |无 |屏体ID |
* 获取单个屏体监控数据
	* getMonitors(["sid"=>"1111"])
* 获取屏体监控图片-原始图
	* getImages(["sid"=>["1111"]])
* 获取屏体监控图片-缩略图
	* getThumbnails(["sid"=>["1111"]])

#单元测试
* 进入tests目录
* 执行以下命令
	* phpunit -c phpunit.xml ./Units/DispatchTest.php
