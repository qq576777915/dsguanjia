<?php

$ver = "2.31";


//代刷管家公共自定义函数

function gbk_to_utf8($data)
{
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = gbk_to_utf8($v);
            } else {
                $data[$k] = iconv('gbk', 'utf-8', $v);
            }
        }
        return $data;
    } else {
        $data = iconv('gbk', 'utf-8', $data);
        return $data;
    }
}

function king_get_yile($token, $key, $shequ_url, $tid)
{
    $params['api_token'] = $token;
    $params['timestamp'] = time();
    $params['gid'] = $tid;
    $sign = getSign($params, $key);
    return king_Crawler_2("http://" . $shequ_url . ".api.94sq.cn/api/goods/info", "", "api_token=" . $token . "&timestamp=" . time() . "&sign=" . $sign . "&gid=" . $tid, "");
}

function getSign($param, $key)
{
    $signPars = "";
    ksort($param);
    foreach ($param as $k => $v) {
        if ("sign" != $k && "" != $v) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars = trim($signPars, '&');
    $signPars .= $key;
    $sign = md5($signPars);
    return $sign;
}

function king_get_Date()
{
    $time = time(); //当前时间戳
    $date = date('Y-m-d H:i:s', strtotime("+365 day -8 hour"));//一年后日期
    $time = strtotime($date);
    $time = new DateTime($date, new DateTimeZone("GMT"));
    $word = $time->format("D, d M Y H:i:s T");
    $word_1 = substr($word, 0, intval(intval($word . ob_get_length()) - 4));
    $word_1 = $word_1 . " GMT";
    return $word_1;
}

function midstr($str, $str1, $str2)
{
    $result = '';
    $l = strpos($str, $str1);
    if (is_numeric($l)) {
        $str = substr($str, $l + mb_strlen($str1));
        $l = strpos($str, $str2);
        if (is_numeric($l)) $result = substr($str, 0, $l);
    }
    return $result;
}

function king_Crawler_2($url, $head, $body, $cookie)
{
//    POST BODY 需要数组传入
//    $curlPost = array(
//        'a'=>123,
//        'b'=>456,
//        'c'=>789
//    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function king_Crawler_1($url, $url2, $head, $body, $cookie)
{
//    POST BODY 需要数组传入
//    $curlPost = array(
//        'a'=>123,
//        'b'=>456,
//        'c'=>789
//    );
    $cookie_file = dirname(__FILE__) . '/cookie.txt';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $encode = urlencode($body);
    $encode = str_replace("%3D", "=", $encode);
    $encode = str_replace("%26", "&", $encode);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    $result = curl_exec($ch);
    $body = "";
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_HEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/***
 * 正则取购价
 *
 * @param $result 页面源码
 * @param $re1 正则条件
 * @return null 匹配内容
 */
function king_Regular($result, $re1)
{
    $float1 = null;
    if ($c = preg_match_all($re1, $result, $matches)) {
        $float1 = $matches[1][0];
    }
    return $float1;
}

/***
 * 取社区商品页面源码
 *
 * @param $post 表单信息带帐号密码
 * @param $url1 登录页面地址
 * @param $url2 指定页面地址
 * @return mixed 页面源码
 */
function king_Crawler($post, $url1, $url2)
{
//    $cookie_jar = null;
    $cookie_file = dirname(__FILE__) . '/cookie.txt';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $encode = urlencode($post);
    $encode = str_replace("%3D", "=", $encode);
    $encode = str_replace("%26", "&", $encode);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    $result = curl_exec($ch);
    $post = "";
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

?>