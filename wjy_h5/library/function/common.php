<?php
/**
 * Date: 2018\2\17 0017 23:36
 */
/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

/**
 * 清空目录
 * @param string $dir [存储目录]
 */
function clean_dir($dir)
{
    if (!is_dir($dir)) {
        return true;
    }
    $files = scandir($dir);
    unset($files[0], $files[1]);
    $result = 0;
    foreach ($files as &$f) {
        $result += @unlink($dir . $f);
    }
    unset($files);
    return $result;
}

/**
 * 输出一个人字符串 多少位 长度；
 **/
function str_strlen($str)
{
    $i = 0;
    $count = 0;
    $len = strlen($str);
    while ($i < $len) {
        $chr = ord($str[$i]);
        $count++;
        $i++;
        if ($i >= $len) break;
        if ($chr & 0x80) {
            $chr <<= 1;
            while ($chr & 0x80) {
                $i++;
                $chr <<= 1;
            }
        }
    }
    return $count;
}


/**
 * 记录日志
 * @param $content
 * @param string $filename
 * @param string $Separator
 * @return bool|int
 */
function logs($content, $filename = '', $sdir = '', $Separator = ",")
{
    if (is_array($content)) {
        $content = var_export($content,true);
    }
    $dir = Config::get('log_path');
    if (!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    if (!empty($sdir)) {
        $dir = $dir . '/' . $sdir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
    }
    if (empty($filename)) {
        $filename = date('Y_m_d', time());
    }
    $result = file_put_contents($dir . '/' . $filename . '.log', (date('Y-m-d h:i:s', time())) . ' ： ' . $content . "\r\n", FILE_APPEND | LOCK_EX);
    return $result;
}

function secret($string,$code,$operation=false){
    $code = md5($code);
    $iv = substr($code,0,16);
    $key = substr($code,16);
    if($operation){
        return openssl_decrypt(base64_decode($string),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
    }
    return base64_encode(openssl_encrypt($string,"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
}


/** 创建订单号 */
function create_order_no($uid='') {
    $order_no = date('Ymd').substr(time(), -5) .$uid. substr(microtime(), 2, 5) . sprintf('%02d', rand(1000, 9999));
    return $order_no;
}

/**
 * 获取用户信息
 */
function getUserName()
{
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $username = "";
    for ( $i = 0; $i < 6; $i++ )
    {
        $username .= $chars[mt_rand(0, strlen($chars))];
    }
    return strtoupper(base_convert(time() - 1420070400, 10, 36)).$username;
}

/** 获取财富树 第n级的支付金额 */
function getTreeLevelMoeny($level = 1, $money, $ratio)
{
    for ($i = 1; $i < $level; $i++)
    {
        $addMoney = bcmul($ratio, $money, 2) / 100;
        $money = bcadd($money, $addMoney, 2);
    }

    return $money;
}

/** 从第n级升级到第m级的金额 */
function getTreeUpgradeMoney($level, $tolevel, $money, $ratio)
{
    $total = 0;
    $level++;
    for ($level; $level <= $tolevel; $level++)
    {
        
        if ($level > 0)
        {
            $total =  bcadd($total, getTreeLevelMoeny($level, $money, $ratio), 2);
        }
    }

    return $total;
}


function error($message = '') {
    return array(
        'errno' => 1,
        'message' => $message,
    );
}


function is_error($data) {
    if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] == 0)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 获取ip
 * @return string
 */
function getip() {
    static $ip = '';
    $ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
        $ip = $_SERVER['HTTP_CDN_SRC_IP'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

/** 补全网址 */
function tomedia($src)
{

    global $_SERVER;
    $host = $_SERVER['HTTP_HOST'];
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

    if (empty($src)) {
        return '';
    }

    if ((substr($src, 0, 7) == 'http://') || (substr($src, 0, 8) == 'https://') || (substr($src, 0, 2) == '//'))
    {
        return $src;
    }

    $src =  str_replace("\\", '/', $src);
    $src =  $http_type.$host.'/public/uploads/'.$src;
    return $src;
}
