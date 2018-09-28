<?php
/**
 * Created by PhpStorm.
 * Date: 18.9.28
 * Time: 9:59
 */

namespace App\Classes;


class CurlRequest {

    // 用 file_get_contents( ) 读取网络资源不方便设置超时时间
    // 一旦服务端无响应，会导致 CPU 资源占用异常、进程崩溃等问题
    // 此方法为解决这个问题而出现
    public static function curl_get_contents($url, $timeout = 3) {
        $curl = curl_init();
        curl_setopt( $curl , CURLOPT_URL, $url );
        curl_setopt( $curl , CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl , CURLOPT_TIMEOUT, $timeout );
        $result = curl_exec( $curl );
        $error = curl_error($curl);
        curl_close( $curl );
        if($error) throw new \Exception("CURL ERROR: $error");
        return $result;
    }

    // 请求后立即断开，不等待服务端返回结果
    // 可用于触发 PHP 伪异步任务
    // 对 Windows 下 php-cgi.exe 方式无效，因为只有一个进程在接受请求，其余都是空闲状态
    public static function touch($url) {

        // print_r(parse_url($url));

        $url = parse_url($url);

        if(empty($url['port'])){
            switch ($url['scheme']){
                case 'http':
                    $url['port'] = 80;
                    break;
                case 'https':
                    $host = 'ssl://' . $url['host'];
                    $url['port'] = 443;
                    break;
                default:
                    throw new \Exception("不支持{$url['scheme']}协议");
            }
        }

        if(!isset($url['query'])){
            $url['query'] = '';
        }

        // $handle = stream_socket_client("tcp://{$url['host']}:{$url['port']}", $errno, $error, 3);
        // stream_set_blocking($handle, 0);
        $handle = fsockopen(isset($host)?$host:$url['host'], $url['port'], $errno, $error, 3);

        if (!$handle) throw new \Exception($error);

        $header = "GET {$url['path']}?{$url['query']} HTTP/1.1\r\nHost: {$url['host']}\r\nConnection: close\r\n\r\n";

        fwrite($handle, $header);

        // while(!feof($handle)){
        //     echo fgets($handle, 10);
        // }

        fclose($handle);
    }
}
