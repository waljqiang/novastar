<?php
namespace NovaStar\Test\Units;

use NovaStar\Test\TestCase;
use NovaStar\Api\Dispatch;
use NovaStar\Api\Core\Http;
use NovaStar\Api\Exceptions\HttpException;
use Doctrine\Common\Cache\FilesystemCache;

class DispatchTest extends TestCase{
	const APP_ID = '3f6deaafb426e72dc88addda6423190ca18efd7287a1e311417da7ee';
	const SECRET = 'E478IXfjQ5Exf6kCCMEIuyvFKSkRdimWxSmQvByV';

	public static $instance;

	/**
	 * Function:setUpBeforeClass
	 * @return  void
	 */
	public static function setUpBeforeClass(){
		self::$instance = new Dispatch(
			[	'node' => 'local',
				'app_id' => self::APP_ID,
				'app_secret' => self::SECRET,
				'cache' => new FilesystemCache(dirname(dirname(__FILE__)) . '/runtime/cache'),
				'scope' => '',
				'log' => [
					'level' => 'DEBUG',
					'path' => dirname(dirname(__FILE__)) . '/runtime/log/novastar.log'
				]
			]
		); 
	}

	/**
	 * Function: tearDownAfterClass
	 * @return void
	 */
	public static function tearDownAfterClass(){
		self::$instance = null;
	}

	/**
	 * Function: testBadClient
	 * @return void
	 */
	public function testBadClient(){
		$dispatch = new Dispatch(
			[
				'app_id' => '3f6deaafb426e72dc88addda6423190ca18efd7287a1e311417da7ee1',
				'app_secret' => 'E478IXfjQ5Exf6kCCMEIuyvFKSkRdimWxSmQvByV',
				'cache' => new FilesystemCache(dirname(dirname(__FILE__)) . '/runtime/cache'),
				'scope' => '',
				'log' => [
					'level' => 'DEBUG',
					'path' => dirname(dirname(__FILE__)) . '/runtime/log/novastar.log'
				]
			]
		);
		try{
			$tags = $dispatch->getTags();
			$this->assertEquals(200,$tags['status']);
			$this->assertEquals(100000,$tags['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testDefaultConfig
	 * @return void
	 */
	public function testDefaultConfig(){
		$dispatch = new Dispatch(
			[
				'app_id' => self::APP_ID,
				'app_secret' => self::SECRET
			]
		);
		try{
			$tags = $dispatch->getTags();
			$this->assertEquals(200,$tags['status']);
			$this->assertEquals(100000,$tags['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetTags
	 * 获取用户标签列表
	 */
	public function testGetTags(){
		try{
			$tags = self::$instance->getTags();
			$this->assertEquals(200,$tags['status']);
			$this->assertEquals(100000,$tags['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetScreenList
	 * 获取用户显示屏列表
	 * @dataProvider getScreenListProvider
	 */
	public function testGetScreenList($data){
		try{
			$screenList = self::$instance->getScreenList($data);
			$this->assertEquals(200,$screenList['status']);
			$this->assertEquals(100000,$screenList['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetSpotChecks
	 * 获取单个屏体点检信息
	 * @dataProvider getSpotChecksProvider
	 */
	public function testGetSpotChecks($sid){
		$data = [
			'sid' => $sid
		];
		try{
			$spotChecks = self::$instance->getSpotChecks($data);
			$this->assertEquals(200,$spotChecks['status']);
			$this->assertEquals(100000,$spotChecks['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetMonitors
	 * 获取单个屏体监控信息
	 * @dataProvider getMonitorsProvider
	 */
	public function testGetMonitors($sid){
		$data = [
			'sid' => $sid
		];
		try{
			$monitors = self::$instance->getMonitors($data);
			$this->assertEquals(200,$monitors['status']);
			$this->assertEquals(100000,$monitors['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetImages
	 * 获取屏体监控图片-原始图
	 * @dataProvider getImagesProvider
	 */
	public function testGetImages($sid){
		$data = [
			'sid' => [
				$sid
			]
		];
		try{
			$images = self::$instance->getImages($data);
			$this->assertEquals(200,$images['status']);
			$this->assertEquals(100000,$images['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testGetThumbnails
	 * 获取屏体监控图片-缩略图
	 * @dataProvider getThumbnailsProvider
	 */
	public function testGetThumbnails($sid){
		$data = [
			'sid' => [
				$sid
			]
		];
		try{
			$thumbnails = self::$instance->getThumbnails($data);
			$this->assertEquals(200,$thumbnails['status']);
			$this->assertEquals(100000,$thumbnails['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: testRetry
	 * 测试token失败后从新获取token
	 */
	public function testRetry(){
		self::$instance->getCache()->save('novaauth.access_token'.self::APP_ID,'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjE5ZDEzYzgyYWNiMDNiN2FlNDA4MWIxZDA1NWY1MzdkNTIzNTE4YzQ2NzVjMjA1OWU4N2RmMDE1ZGRlNWNhMDRhYjkyMzgyNTAyNGZhM2U4In0.eyJhdWQiOiIxNiIsImp0aSI6IjE5ZDEzYzgyYWNiMDNiN2FlNDA4MWIxZDA1NWY1MzdkNTIzNTE4YzQ2NzVjMjA1OWU4N2RmMDE1ZGRlNWNhMDRhYjkyMzgyNTAyNGZhM2U4IiwiaWF0IjoxNTM4MTE2MDAzLCJuYmYiOjE1MzgxMTYwMDMsImV4cCI6MTUzODEyMzIwMywic3ViIjoiIiwic2NvcGVzIjpbInVzZXItdGFncyIsInNjcmVlbi1saXN0cyIsInNjcmVlbi1zcG90Y2hlY2siLCJzY3JlZW4tbW9uaXRvciIsInNjcmVlbi1pbWFnZSIsInNjcmVlbi10aHVtYm5haWwiLCJzY3JlZW4taW1hZ2VzIiwic2NyZWVuLXRodW1ibmFpbHMiXX0.gaIxI_P2lvPm4_r-iujfb4NmImMApPGkiR5ECKcCd3zFC57DUNIFdV_aNbLo7owFh-FL7xxbjxTQL7CSKMIm3LPFrjjHSIw-NCYQX2Mw8tihoFTPSR2ccmGDbMnanGOpW0L-OKE3N8iCkcNywhIn_aONO_Ye-VD5cznlWTNzdvwoWUUwtLbPUo8PDQzK2vbdxdA8f9mp7RSLP_SLXv4uVCgYkIQj_QKSHnIwGjGVVyhlkhjX7eQP1E3a-TBNK4eM7MlI_eKt2PJVPnhFZ1ZUKRs932DhlROdjNK9wO-bTB5huMbySnLwg67hJwihRaVkwIUX5PZsSdQGhFZ-4h4aYyDp2nT0UUGl6WtKx8jb597Ze9OerDRi4R6atZ0bFoZq3g7HTaIXF9YbYPbv7tAJveybKI6UJF1wDAhzuxVTkcicBr42lqki7QjMIz2D7uaeLj5zRu5_UACMyaZSaae1c45j6WIfltMiUM2F7rQXYxC8dUn8fEeoh6Zd4-fPnhJzH-n4rcnyFAqwoe1e9N2rLw16vTDyU95S20pyM-tsiGgfhNc-JrfwrgtR9euG2S76cVIAWoA96bDWlgFaPaYoevj1ZQXPW3a2eyQhnY7T3ExIVFWhbns2IJonFt1M95OJ75S2P8ydo2Iw_nKN9PN2puenXWrAP9whbPuU6XrTaKU',7200);
		try{
			$tags = self::$instance->getTags();
			$this->assertEquals(200,$tags['status']);
			$this->assertEquals(100000,$tags['errorCode']);
		}catch(\Exception $e){
			$this->assertInstanceOf(HttpException::class,$e);
		}
	}

	/**
	 * Function: getScreenListProvider
	 * @return array
	 */
	public function getScreenListProvider(){
		return [
			'no' => [[]],
			'status' => [
				['status' => 2]
			],
			'status_and_label' => [
				['status' => 2,'label' => 222]
			],
			'all' => [
				['status' => 2,'label' => 222,'search' => '测试屏']
			]
		];
	}

	/**
	 * Function: getSpotChecksProvider
	 * testGetSpotChecks数据供给器
	 * @return array
	 */
	public function getSpotChecksProvider(){
		return [
			'no_sid' => [''],
			'error_sid' => ['aa'],
			'sid' => ['eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn']
		];
	}

	/**
	 * Function: getMonitorsProvider
	 * @return array
	 */
	public function getMonitorsProvider(){
		return [
			'no_sid' => [''],
			'error_sid' => ['aa'],
			'sid' => ['eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn']
		];
	}

	/**
	 * Function: getImagesProvider
	 * @return array
	 */
	public function getImagesProvider(){
		return [
			'no_sid' => [''],
			'error_sid' => ['aa'],
			'sid' => ['eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn']
		];
	}

	/**
	 * getThumbnailsProvider
	 * @return array
	 */
	public function getThumbnailsProvider(){
		return [
			'no_sid' => [''],
			'error_sid' => ['aa'],
			'sid' => ['eracavontramshH5e0xDp9x2013xyccx2013xXQx2016x1HtPjLnBYzV3XQ18zxC38Gn']
		];
	}

}