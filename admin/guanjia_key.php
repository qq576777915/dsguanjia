<?php

//监控密匙
$key_c = "123456";


//代刷管家公共自定义函数
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
 * 爬取社区商品页面源码
 *
 * @param $post 表单信息带帐号密码
 * @param $url1 登录页面地址
 * @param $url2 指定页面地址
 * @return mixed 页面源码
 */
function king_Crawler($post, $url1, $url2)
{
    $cookie_jar = null;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url1);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    $result = curl_exec($ch);
    $post = "";
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

?>