<?php
	//http://www.novaicare.local:8060/Demon/test.php
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
	//获取用户标签
	try{
		$tags = $apiDispatch->getTags();
		echo '-------------------------------------用户标签-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($tags);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
	//获取显示屏列表
	try{
		$screenList = $apiDispatch->getScreenList(["status"=>2,"label"=>222,"search"=>"测试屏"]);
		echo '-------------------------------------显示屏列表-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($screenList);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
	//获取单个屏体点检信息
	try{
		$spotInfos = $apiDispatch->getSpotChecks(["sid"=>"eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn"]);
		echo '-------------------------------------单个屏体点检信息-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($spotInfos);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
	//获取单个屏体监控数据
	try{
		$monitors = $apiDispatch->getMonitors(["sid"=>"eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn"]);
		echo '-------------------------------------获取单个屏体监控数据-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($monitors);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
	//获取屏体监控图片-原始图
	try{
		$images = $apiDispatch->getImages(["sid"=>["eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn"]]);
		echo '-------------------------------------获取单个屏体监控图片-原始图-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($images);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}
	//获取屏体监控图片-缩略图
	try{
		$thumbnails = $apiDispatch->getThumbnails(["sid"=>["eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn"],"width"=>200,"height"=>200]);
		echo '-------------------------------------获取屏体监控图片-缩略图-------------------------------------------------' . PHP_EOL;
		echo '<pre>';
		print_r($thumbnails);
		echo '</pre>';
	}catch(HttpException $e){
		var_dump($e->getMessage());
	}