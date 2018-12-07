<?php
/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-15
 * Time: 下午1:53
 */

namespace NovaStar\Api\Exceptions;

use \Exception;

class HttpException extends Exception
{
	/**
	 * Unknown error
	 */
	const UNKNOWN = 400400;
	/**
	 * The server of this code is breakdown
	 */
	const NODEBAD = 400200;

	/**
	 * The Client is invalid
	 */
	const INVALID_CLIENT = 400104;

	/**
	 * The requested scope is invalid
	 */
	const INVALID_SCOPE = 400105;

	/**
	 * Don't find this screens
	 */
	const NO_SCREEN = 160101;
    
}