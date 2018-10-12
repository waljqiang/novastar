<?php
/**
 * Created by PhpStorm.
 * User: emmanuel
 * Date: 17-8-15
 * Time: 下午1:43
 */

namespace NovaStar\Api\Utils;

class Signature{
    public static function sign(array $query, $time, $nonce, $appSecret)
    {
        $queryStr = '';
        ksort($query);
        $query = array_merge($query, compact('time','nonce','appSecret'));
        foreach ($query as $key => $value) {
            $queryStr .= $key . ':' . $value . ',';
        }
        return md5(substr($queryStr, 0, -1));
    }
}