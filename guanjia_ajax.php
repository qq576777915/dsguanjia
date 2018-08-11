<?php
include("./includes/common.php");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

if ($is_fenzhan == true) {
    $price_obj = new Price($siterow['zid'], $siterow);
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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    $body = "";
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_HEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
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

switch ($act) {

    case 'setguantime':
        //代刷管家 - 设置最后监控时间
        $code = 0;
        $showtime = date("Y-m-d H:i:s");
        $sql = "UPDATE `shua_guanjia` SET `date` = '" . $showtime . "' WHERE `shua_guanjia`.`tid` = 0;";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "时间设置失败";
        }

        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;

    case 'setguani_pl_fl_count':
        //代刷管家 - 分类数量获取
        $code = 0;
        $count_fl = 0;
        $multi = $_GET['multi'];
        $multi_text = explode(' ', $multi);
        for ($i = 0; $i < count($multi_text); $i++) {
            $count1 = $DB->count("SELECT count(*) from shua_tools WHERE active  = 1 AND cid = " . $multi_text[$i]);
            $code = 1;
            $count_fl = $count_fl + $count1;
        }

        $result = array("code" => $code, "msg" => $count_fl);
        exit(json_encode($result));

    case 'setguani_pl_fl':
        //代刷管家 - 批量设置
        $code = 0;
        $tid = intval($_GET['tid']);
        $yh_pi = intval($_GET['yh_pi']);
        $pj_pi = intval($_GET['pj_pi']);
        $zy_pi = intval($_GET['zy_pi']);
        $yh_pi = $yh_pi / 100;
        $pj_pi = $pj_pi / 100;
        $zy_pi = $zy_pi / 100;
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        if ($res = $DB->fetch($rs)) {

        } else {
            $result = array("code" => 1, "msg" => "无此商品");
            exit(json_encode($result));
            break;
        }
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户购价 1=普及购价 2=专业购价 3=状态
        $data_2[4][1];
        //收集所有商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本 4=商品类型
        $data_3[5][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型 4=paytype(九五时 点数下单0 余额下单1)
        $data_4[5][1];
        //收集上个商品信息 0=社区ID 1=商品ID 2=商品数量 3=成本_用户 4=成本_普及 5=成本_专业
        $data_5[4][1];

        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
            $data_3[4][0] = $res['is_curl'];
        }

        if ($data_3[4][0] != "2") {
            //如果是自营商品 直接跳出
            $code = 1;
            $msg = "设置成功，自营商品";

            $result = array("code" => $code, "msg" => $msg);
            exit(json_encode($result));
            break;
        }

        $last_tid = $tid - 1;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
        if ($res = $DB->fetch($rs)) {
            $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
            while ($res = $DB->fetch($rs)) {
                $data_5[0][0] = $res['shequ'];
                $data_5[1][0] = $res['goods_id'];
                $data_5[2][0] = $res['value'];
            }
            if ($data_5[0][0] == $data_3[0][0] && $data_5[1][0] == $data_3[1][0]) {
                //如果是与上个商品同一款
                $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $last_tid);
                while ($res = $DB->fetch($rs)) {
                    $data_5[3][0] = $res['price'];
                    $data_5[4][0] = $res['cost'];
                    $data_5[5][0] = $res['cost2'];
                }
                if ($data_5[0][0] != 0 && $data_5[1][0] != 0 && $data_5[2][0] != 0 && $data_5[0][0] != null && $data_5[1][0] != null && $data_5[2][0] != null) {
                    //上款商品不为0不为空
                    $goods_bl = $data_3[2][0] / $data_5[2][0];
                    $total_1 = $data_5[3][0] * $goods_bl;
                    $total_2 = $data_5[4][0] * $goods_bl;
                    $total_3 = $data_5[5][0] * $goods_bl;
                    $total_1 = $total_1 * ($yh_pi - 1);
                    $total_2 = $total_1 * ($pj_pi - 1);
                    $total_3 = $total_1 * ($zy_pi - 1);
                    $sql = "UPDATE `shua_guanjia` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        $code = 1;
                        $msg = "设置成功";
                    } else {
                        $code = 0;
                        $msg = "设置失败，错误代码-fo1";
                    }
                    $result = array("code" => $code, "msg" => $msg . "跳过流程");
                    exit(json_encode($result));
                    break;
                } else {
                    $code = 1;
                    $msg = "设置成功，商品维护/不支持的商品类型";
                }
            }
        }

        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_2[0][0] = $res['price'];
            $data_2[1][0] = $res['cost'];
            $data_2[2][0] = $res['cost2'];
            $data_2[3][0] = $res['status'];
        }
        $rs = $DB->query("SELECT * FROM shua_shequ WHERE id =" . $data_3[0][0]);
        while ($res = $DB->fetch($rs)) {
            $data_4[0][0] = $res['url'];
            $data_4[1][0] = $res['username'];
            $data_4[2][0] = $res['password'];
            $data_4[3][0] = $res['type'];
            $data_4[4][0] = $res['paytype'];
        }

        $shequ_type = $data_4[3][0];  //社区类型
        $shequ_url = $data_4[0][0];   //社区URL
        $goods_id = $data_3[1][0];             //商品ID
        $shequ_account = $data_4[1][0];   //社区帐号
        $shequ_pwd = $data_4[2][0];    //社区密码
        if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
            $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
            $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
            $result = king_Crawler($post, $url1, $url2);

            $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
            if ($sign > 0) {
            } else {
                $test = king_Crawler_2($url, "", "", "");

                $data_sign = midstr($test, "'cookie' : \"", "\",");
                $data_date = king_get_Date();
                $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
                $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
            }

            $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
        } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
            //玖伍系统开始
            $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
            $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
            $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
            $result = king_Crawler($post, $url1, $url2);

//            $re1 = '/单价为(\S+)元"/';
//            $float1 = king_Regular($result, $re1);

            $float1 = midstr($result, "单价为", "元\">");
            $data_3[3][0] = $float1 * $data_3[2][0];

            sleep(2);
        } else if ($shequ_type == 3 || $shequ_type == "3" || $shequ_type == 5 || $shequ_type == "5") {
            //星墨社区开始
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&id=" . $goods_id;
            $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
            $url2 = "http://" . $shequ_url . "/form.html";
            $result = king_Crawler($post, $url1, $url2);

            $re1 = '/money_dian\"\>(\S+)\<\/span\>/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
        } else {
            $data_3[3][0] = "0";
        }

        if ($data_3[3][0] != "0") {
            //先判断是否商品维护
            $total_1 = $data_3[3][0] * ($yh_pi - 1);   //客户管家值
            if ($total_1 < 0.01) {
                $total_1 = 0.01;
            }
            $total_2 = $data_3[3][0] * ($pj_pi - 1);   //普及管家值
            if ($total_2 < 0.01) {
                $total_2 = 0.01;
            }
            $total_3 = $data_3[3][0] * ($zy_pi - 1);   //专业管家值
            if ($total_3 < 0.01) {
                $total_3 = 0.01;
            }
            $sql = "UPDATE `shua_guanjia` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
            if ($DB->query($sql)) {
                $code = 1;
                $msg = "设置成功";
            } else {
                $code = 0;
                $msg = "设置失败，错误代码-fo1";
            }
        } else {
            $code = 1;
            $msg = "设置成功，商品维护/不支持的商品类型";
        }

        $result = array("code" => $code, "msg" => $msg . "正常流程");
        exit(json_encode($result));
        break;

    case 'setguani_pl':
        //代刷管家 - 批量设置
        $code = 0;
        $tid = intval($_GET['tid']);
        $yh_pi = intval($_GET['yh_pi']);
        $pj_pi = intval($_GET['pj_pi']);
        $zy_pi = intval($_GET['zy_pi']);
        $yh_pi = $yh_pi / 100;
        $pj_pi = $pj_pi / 100;
        $zy_pi = $zy_pi / 100;
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        if ($res = $DB->fetch($rs)) {

        } else {
            $result = array("code" => 1, "msg" => "无此商品");
            exit(json_encode($result));
            break;
        }
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户购价 1=普及购价 2=专业购价 3=状态
        $data_2[4][1];
        //收集所有商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本 4=商品类型
        $data_3[5][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型 4=paytype(九五时 点数下单0 余额下单1)
        $data_4[5][1];
        //收集上个商品信息 0=社区ID 1=商品ID 2=商品数量 3=成本_用户 4=成本_普及 5=成本_专业
        $data_5[4][1];

        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
            $data_3[4][0] = $res['is_curl'];
        }

        if ($data_3[4][0] != "2") {
            //如果是自营商品 直接跳出
            $code = 1;
            $msg = "设置成功，自营商品";

            $result = array("code" => $code, "msg" => $msg);
            exit(json_encode($result));
            break;
        }

        $last_tid = $tid - 1;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
        if ($res = $DB->fetch($rs)) {
            $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
            while ($res = $DB->fetch($rs)) {
                $data_5[0][0] = $res['shequ'];
                $data_5[1][0] = $res['goods_id'];
                $data_5[2][0] = $res['value'];
            }
            if ($data_5[0][0] == $data_3[0][0] && $data_5[1][0] == $data_3[1][0]) {
                //如果是与上个商品同一款
                $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $last_tid);
                while ($res = $DB->fetch($rs)) {
                    $data_5[3][0] = $res['price'];
                    $data_5[4][0] = $res['cost'];
                    $data_5[5][0] = $res['cost2'];
                }
                if ($data_5[0][0] != 0 && $data_5[1][0] != 0 && $data_5[2][0] != 0 && $data_5[0][0] != null && $data_5[1][0] != null && $data_5[2][0] != null) {
                    //上款商品不为0不为空
                    $goods_bl = $data_3[2][0] / $data_5[2][0];
                    $total_1 = $data_5[3][0] * $goods_bl;
                    $total_2 = $data_5[4][0] * $goods_bl;
                    $total_3 = $data_5[5][0] * $goods_bl;
                    $total_1 = $total_1 * ($yh_pi - 1);
                    $total_2 = $total_1 * ($pj_pi - 1);
                    $total_3 = $total_1 * ($zy_pi - 1);
                    $sql = "UPDATE `shua_guanjia` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        $code = 1;
                        $msg = "设置成功";
                    } else {
                        $code = 0;
                        $msg = "设置失败，错误代码-fo1";
                    }
                    $result = array("code" => $code, "msg" => $msg . "跳过流程");
                    exit(json_encode($result));
                    break;
                } else {
                    $code = 1;
                    $msg = "设置成功，商品维护/不支持的商品类型";
                }
            }
        }

        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_2[0][0] = $res['price'];
            $data_2[1][0] = $res['cost'];
            $data_2[2][0] = $res['cost2'];
            $data_2[3][0] = $res['status'];
        }
        $rs = $DB->query("SELECT * FROM shua_shequ WHERE id =" . $data_3[0][0]);
        while ($res = $DB->fetch($rs)) {
            $data_4[0][0] = $res['url'];
            $data_4[1][0] = $res['username'];
            $data_4[2][0] = $res['password'];
            $data_4[3][0] = $res['type'];
            $data_4[4][0] = $res['paytype'];
        }

        $shequ_type = $data_4[3][0];  //社区类型
        $shequ_url = $data_4[0][0];   //社区URL
        $goods_id = $data_3[1][0];             //商品ID
        $shequ_account = $data_4[1][0];   //社区帐号
        $shequ_pwd = $data_4[2][0];    //社区密码
        if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
            $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
            $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
            $result = king_Crawler($post, $url1, $url2);

            $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
            if ($sign > 0) {
            } else {
                $test = king_Crawler_2($url, "", "", "");

                $data_sign = midstr($test, "'cookie' : \"", "\",");
                $data_date = king_get_Date();
                $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
                $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
            }

            $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
        } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
            //玖伍系统开始
            $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
            $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
            $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
            $result = king_Crawler($post, $url1, $url2);

//            $re1 = '/单价为(\S+)元"/';
//            $float1 = king_Regular($result, $re1);

            $float1 = midstr($result, "单价为", "元\">");
            $data_3[3][0] = $float1 * $data_3[2][0];

            sleep(2);
        } else if ($shequ_type == 3 || $shequ_type == "3" || $shequ_type == 5 || $shequ_type == "5") {
            //星墨社区开始
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&id=" . $goods_id;
            $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
            $url2 = "http://" . $shequ_url . "/form.html";
            $result = king_Crawler($post, $url1, $url2);

            $re1 = '/money_dian\"\>(\S+)\<\/span\>/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
        } else {
            $data_3[3][0] = "0";
        }

        if ($data_3[3][0] != "0") {
            //先判断是否商品维护
            $total_1 = $data_3[3][0] * ($yh_pi - 1);   //客户管家值
            if ($total_1 < 0.01) {
                $total_1 = 0.01;
            }
            $total_2 = $data_3[3][0] * ($pj_pi - 1);   //普及管家值
            if ($total_2 < 0.01) {
                $total_2 = 0.01;
            }
            $total_3 = $data_3[3][0] * ($zy_pi - 1);   //专业管家值
            if ($total_3 < 0.01) {
                $total_3 = 0.01;
            }
            $sql = "UPDATE `shua_guanjia` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
            if ($DB->query($sql)) {
                $code = 1;
                $msg = "设置成功";
            } else {
                $code = 0;
                $msg = "设置失败，错误代码-fo1";
            }
        } else {
            $code = 1;
            $msg = "设置成功，商品维护/不支持的商品类型";
        }

        $result = array("code" => $code, "msg" => $msg . "正常流程");
        exit(json_encode($result));
        break;

    case 'setguani':
        //代刷管家 - Core 遍历设置
        $code = 0;
        $tid = intval($_GET['tid']);
        $mh = intval($_GET['mh']);
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户购价 1=普及购价 2=专业购价 3=状态
        $data_2[4][1];
        //收集商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本 4=商品类型
        $data_3[4][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型 4=paytype(九五时 点数下单0 余额下单1)
        $data_4[5][1];
        //收集上个商品信息 0=社区ID 1=商品ID 2=商品数量 3=成本_用户 4=成本_普及 5=成本_专业
        $data_5[4][1];
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
            $data_3[4][0] = $res['is_curl'];
        }
        if ($data_3[4][0] != "2") {
            //如果是自营商品 直接跳出
            $code = 1;
            $msg = "设置成功，自营商品";
            $result = array("code" => $code, "msg" => $msg);
            exit(json_encode($result));
            break;
        }
        $last_tid = $tid - 1;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
        if ($res = $DB->fetch($rs)) {
            $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
            while ($res = $DB->fetch($rs)) {
                $data_5[0][0] = $res['shequ'];
                $data_5[1][0] = $res['goods_id'];
                $data_5[2][0] = $res['value'];
            }
            if ($data_5[0][0] == $data_3[0][0] && $data_5[1][0] == $data_3[1][0]) {
                //如果是与上个商品同一款
                $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
                while ($res = $DB->fetch($rs)) {
                    $data_5[3][0] = $res['price'];
                    $data_5[4][0] = $res['cost'];
                    $data_5[5][0] = $res['cost2'];
                }
                if ($data_5[3][0] > 0.01 && $data_5[4][0] > 0.01 && $data_5[5][0] > 0.01) {
                    //上款商品不为0不为空不为0.01
                    $goods_bl = $data_3[2][0] / $data_5[2][0];
                    $total_1 = $data_5[3][0] * $goods_bl;
                    $total_2 = $data_5[4][0] * $goods_bl;
                    $total_3 = $data_5[5][0] * $goods_bl;
                    $sql = "UPDATE `shua_tools` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_tools`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        $code = 1;
                        $msg = "设置成功";
                    } else {
                        $code = 0;
                        $msg = "设置失败，错误代码-ffo1";
                    }
                    $result = array("code" => $code, "msg" => $msg . "跳过流程" . $sql);
                    exit(json_encode($result));
                    break;
                } else {
                }
            }
        }
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_2[0][0] = $res['price'];
            $data_2[1][0] = $res['cost'];
            $data_2[2][0] = $res['cost2'];
            $data_2[3][0] = $res['status'];
        }
        $rs = $DB->query("SELECT * FROM shua_shequ WHERE id =" . $data_3[0][0]);
        while ($res = $DB->fetch($rs)) {
            $data_4[0][0] = $res['url'];
            $data_4[1][0] = $res['username'];
            $data_4[2][0] = $res['password'];
            $data_4[3][0] = $res['type'];
            $data_4[4][0] = $res['paytype'];
        }
        if ($data_2[0][0] . ob_get_length() != 0 || $data_2[1][0] . ob_get_length() != 0 || $data_2[2][0] . ob_get_length() != 0) {
            //判断用户是否设置了管家值
            $shequ_type = $data_4[3][0];  //社区类型
            $shequ_url = $data_4[0][0];   //社区URL
            $goods_id = $data_3[1][0];             //商品ID
            $shequ_account = $data_4[1][0];   //社区帐号
            $shequ_pwd = $data_4[2][0];    //社区密码
            if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
                $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
                $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
                $result = king_Crawler($post, $url1, $url2);

                $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
                if ($sign > 0) {
                } else {
                    $test = king_Crawler_2($url, "", "", "");

                    $data_sign = midstr($test, "'cookie' : \"", "\",");
                    $data_date = king_get_Date();
                    $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
                    $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
                }

                $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
                $float1 = king_Regular($result, $re1);
                $data_3[3][0] = $float1 * $data_3[2][0];
            } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
                //玖伍系统开始
                $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
                $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
                $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
                $result = king_Crawler($post, $url1, $url2);

//                $re1 = '/单价为(\S+)元"/';
//                $float1 = king_Regular($result, $re1);

                $float1 = midstr($result, "单价为", "元\">");
                $data_3[3][0] = $float1 * $data_3[2][0];
                sleep(2);
            } else if ($shequ_type == 3 || $shequ_type == "3" || $shequ_type == 5 || $shequ_type == "5") {
                //星墨社区开始
                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&id=" . $goods_id;
                $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
                $url2 = "http://" . $shequ_url . "/form.html";
                $result = king_Crawler($post, $url1, $url2);
                $re1 = '/money_dian\"\>(\S+)\<\/span\>/';
                $float1 = king_Regular($result, $re1);
                $data_3[3][0] = $float1 * $data_3[2][0];
            } else {
                $data_3[3][0] = "0";
            }
            if ($data_3[3][0] != "0") {
                //先判断是否商品维护
                if ($data_3[0][0] <= 0.01) {
                    $total_1 = 0.01;
                    $total_2 = 0.01;
                    $total_3 = 0.01;
                } else {
                    $total_1 = $data_3[3][0] + $data_2[0][0];     //客户购价
                    $total_2 = $data_3[3][0] + $data_2[1][0];     //普及购价
                    $total_3 = $data_3[3][0] + $data_2[2][0];    //专业购价
                }
                if ($total_1 != $data_1[0][0] || $total_2 != $data_1[1][0] || $total_3 != $data_1[2][0]) {
                    //判断是否符合管家价格线
                    if ($mh == 1 || $mh == '1') {
                        //美化
                        if ($total_1 < 0.1) {
                        } else {
                            $total_1 = round($total_1, 1);
                            $total_2 = round($total_2, 1);
                            $total_3 = round($total_3, 1);
                        }
                    }
                    $sql = "UPDATE `shua_tools` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_tools`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        $code = 1;
                        $msg = "设置成功";
                        //商品价格正常设置 恢复上架
                        $sql = "UPDATE `shua_tools` SET `active` = '1' WHERE `shua_tools`.`tid` = " . $tid . ";";
                        $DB->query($sql);
                    } else {
                        $code = 0;
                        $msg = "设置失败，错误代码-eo1";
                    }
                } else {
                    $code = 1;
                    $msg = "设置成功";
                }
            } else {
                //如果商品维护 / 不存在 自动下架该商品
                $sql = "UPDATE `shua_tools` SET `active` = '0' WHERE `shua_tools`.`tid` = " . $tid . ";";
                $DB->query($sql);
                $code = 1;
                $msg = "设置成功，商品维护";
            }
        } else {
            $code = 1;
            $msg = "设置成功";
        }
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;

    case 'setguanjia':
        //代刷管家 - 变量用户设置
        $code = 0;
        $tid = intval($_GET['tid']);
        $price = intval($_GET['price']);
        $cost = intval($_GET['cost']);
        $cost_2 = intval($_GET['cost_2']);
        $price = $price / 100;
        $cost = $cost / 100;
        $cost_2 = $cost_2 / 100;

        $sql = "UPDATE `shua_guanjia` SET `price` = " . $price . ", `cost` = " . $cost . ", `cost2` = " . $cost_2 . ", `status` = 1 WHERE `shua_guanjia`.`tid` = " . $tid . ";";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "设置失败";
        }

        $result = array("code" => $code, "msg" => $tid);
        exit(json_encode($result));
        break;


    case 'getguanjia':
        //代刷管家 - 获取商品成本信息
        $goods_id;
        $price;
        $cost;
        $cost2;
        $price_guanjia;
        $cost_guanjia;
        $cost2_guanjia;
        $value;
        $price_chengben;
        $shequ;
        $shequ_url;
        $shequ_account;
        $shequ_pwd;
        $shequ_type;
        $shequ_paytype;
        //是否社区商品
        $is_curl;
        $tid = intval($_GET['tid']);
        $rs = $DB->query("SELECT * FROM shua_tools as a WHERE tid = '$tid'");
        while ($res = $DB->fetch($rs)) {
            $goods_id = $res['goods_id'];
            $price = $res['price'];
            $cost = $res['cost'];
            $cost2 = $res['cost2'];
            $value = $res['value'];
            $shequ = $res['shequ'];
            $is_curl = $res['is_curl'];
        }


        $rs = $DB->query("SELECT * FROM shua_shequ WHERE id = '$shequ'");
        while ($res = $DB->fetch($rs)) {
            $shequ_url = $res['url'];
            $shequ_account = $res['username'];
            $shequ_pwd = $res['password'];
            $shequ_type = $res['type'];
            $shequ_paytype = $res['paytype'];
        }
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid = '$tid'");
        while ($res = $DB->fetch($rs)) {
            $price_guanjia = $res['price'];
            $cost_guanjia = $res['cost'];
            $cost2_guanjia = $res['cost2'];
        }

        if ($is_curl == "2") {
            if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
                $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
                $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
                $result = king_Crawler($post, $url1, $url2);

                $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
                if ($sign > 0) {
                } else {
                    $test = king_Crawler_2($url, "", "", "");

                    $data_sign = midstr($test, "'cookie' : \"", "\",");
                    $data_date = king_get_Date();
                    $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
                    $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
                }

                $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
                $float1 = king_Regular($result, $re1);
                $price_chengben = $float1 * $value;
            } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
                //玖伍系统开始
                $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
                $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
                $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
                $result = king_Crawler($post, $url1, $url2);


//                $re1 = '/单价为(\S+)元"/';
//                $float1 = king_Regular($result, $re1);
                $float1 = midstr($result, "单价为", "元\">");
                $price_chengben = $float1 * $value;
            } else if ($shequ_type == 3 || $shequ_type == "3" || $shequ_type == 5 || $shequ_type == "5") {
                //星墨社区开始
                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&id=" . $goods_id;
                $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
                $url2 = "http://" . $shequ_url . "/form.html";
                $result = king_Crawler($post, $url1, $url2);

                $re1 = '/money_dian\"\>(\S+)\<\/span\>/';
                $float1 = king_Regular($result, $re1);
                $price_chengben = $float1 * $value;
            } else {
                $price_chengben = "暂不支持该社区的成本价格获取";
            }
            if ($price_chengben == "0") {
                $price_chengben = "商品维护";
            }
        }
        if ($shequ_type == "0" || $shequ_type == 0) {
            if ($shequ_paytype == 1 || $shequ_paytype == "1") {
                $shequ_type = "2";
            }
        }
        if ($is_curl != "2") {
            $price_chengben = "不支持的商品类型";
            if ($is_curl == "0") {
                $shequ_type = "11";
            } else if ($is_curl == "1") {
                $shequ_type = "12";
            } else if ($is_curl == "3") {
                $shequ_type = "13";
            } else if ($is_curl == "4") {
                $shequ_type = "14";
            }
        }
        $data[] = array('shequ' => $shequ_type, 'chengben' => $price_chengben, 'price' => $price, 'cost' => $cost, 'cost2' => $cost2, 'price_guanjia' => $price_guanjia, 'cost_guanjia' => $cost_guanjia, 'cost2_guanjia' => $cost2_guanjia);
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;

    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}
