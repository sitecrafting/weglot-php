<?php
/**
 * Created by PhpStorm.
 * User: bleduc
 * Date: 10/04/2018
 * Time: 11:05
 */

namespace Weglot\Parser\Util;

class Server
{
    /**
     * @param array $server
     * @param bool $use_forwarded_host
     * @return string
     */
    public static function fullUrl(array $server, $use_forwarded_host = false)
    {
        return self::urlOrigin($server, $use_forwarded_host) . $server['REQUEST_URI'];
    }

    /**
     * @param array $server
     * @param bool $use_forwarded_host
     * @return string
     */
    protected static function urlOrigin(array $server, $use_forwarded_host = false)
    {
        $ssl = (!empty($server['HTTPS']) && $server['HTTPS'] == 'on') ? true : false;
        $sp = strtolower($server['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $server['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($use_forwarded_host && isset($server['HTTP_X_FORWARDED_HOST'])) ? $server['HTTP_X_FORWARDED_HOST'] : (isset($server['HTTP_HOST']) ? $server['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $server['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    /**
     * @param array $server
     * @return int
     */
    public static function detectBot(array $server)
    {
        if (isset($server['HTTP_USER_AGENT'])) {
            $ua = $server['HTTP_USER_AGENT'];
        }
        if (isset($ua)) {
            if (preg_match('/bot|favicon|crawl|facebook|slurp|spider/i', $ua)) {
                if (strpos($ua, 'Google') !== false || strpos($ua, 'facebook') !== false || strpos(
                        $ua,
                        'wprocketbot'
                    ) !== false || strpos($ua, 'SemrushBot') !== false) {
                    return 2;
                } elseif (strpos($ua, 'bing') !== false) {
                    return 3;
                } elseif (strpos($ua, 'yahoo') !== false) {
                    return 4;
                } elseif (strpos($ua, 'Baidu') !== false) {
                    return 5;
                } elseif (strpos($ua, 'Yandex') !== false) {
                    return 6;
                } else {
                    return 1;
                }
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }
}
