<?php
include("./includes/common.php");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

if ($is_fenzhan == true) {
    $price_obj = new Price($siterow['zid'], $siterow);
}
if ($conf['cjmsg'] != '') {
    $cjmsg = $conf['cjmsg'];
} else {
    $cjmsg = '您今天的抽奖次数已经达到上限！';
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
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户购价 1=普及购价 2=专业购价 3=状态
        $data_2[4][1];
        //收集所有商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本
        $data_3[4][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型
        $data_4[4][1];
        $sign = 1;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
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

            $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
        } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
            //玖伍系统开始
            $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
            $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
            $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
            $result = king_Crawler($post, $url1, $url2);

            $re1 = '/单价为(\S+)元"/';
            $float1 = king_Regular($result, $re1);
            $data_3[3][0] = $float1 * $data_3[2][0];
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

        if ($data_3[3][0] != 0 || $data_3[3][0] != "0") {
            //先判断是否商品维护
            $total_1 = $data_3[3][0] * ($yh_pi - 1);   //客户管家值
            $total_2 = $data_3[3][0] * ($pj_pi - 1);   //普及管家值
            $total_3 = $data_3[3][0] * ($zy_pi - 1);   //专业管家值
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
            $msg = "设置成功";
        }

        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;

    case 'setguani':
        //代刷管家 - Core 遍历设置
        $code = 0;
        $tid = intval($_GET['tid']);

        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户购价 1=普及购价 2=专业购价 3=状态
        $data_2[4][1];
        //收集所有商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本
        $data_3[4][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型
        $data_4[4][1];
        $sign = 1;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
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

                $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
                $float1 = king_Regular($result, $re1);
                $data_3[3][0] = $float1 * $data_3[2][0];
            } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
                //玖伍系统开始
                $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
                $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
                $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
                $result = king_Crawler($post, $url1, $url2);

                $re1 = '/单价为(\S+)元"/';
                $float1 = king_Regular($result, $re1);
                $data_3[3][0] = $float1 * $data_3[2][0];
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

            if ($data_3[3][0] != 0 || $data_3[3][0] != "0") {
                //先判断是否商品维护
                $total_1 = $data_3[3][0] + $data_2[0][0];   //客户购价
                $total_2 = $data_3[3][0] + $data_2[1][0];   //普及购价
                $total_3 = $data_3[3][0] + $data_2[2][0];   //专业购价
                if ($total_1 != $data_1[0][0] || $total_2 != $data_1[1][0] || $total_3 != $data_1[2][0]) {
                    //判断是否符合管家价格线
                    $sql = "UPDATE `shua_tools` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_tools`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        $code = 1;
                        $msg = "设置成功";
                    } else {
                        $code = 0;
                        $msg = "设置失败，错误代码-eo1";
                    }
                } else {
                    $code = 1;
                    $msg = "设置成功";
                }
            } else {
                $code = 1;
                $msg = "设置成功";
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
        $tid = intval($_GET['tid']);
        $rs = $DB->query("SELECT * FROM shua_tools as a WHERE tid = '$tid'");
        while ($res = $DB->fetch($rs)) {
            $goods_id = $res['goods_id'];
            $price = $res['price'];
            $cost = $res['cost'];
            $cost2 = $res['cost2'];
            $value = $res['value'];
            $shequ = $res['shequ'];
        }
        $rs = $DB->query("SELECT * FROM shua_shequ WHERE id = '$shequ'");
        while ($res = $DB->fetch($rs)) {
            $shequ_url = $res['url'];
            $shequ_account = $res['username'];
            $shequ_pwd = $res['password'];
            $shequ_type = $res['type'];
        }
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid = '$tid'");
        while ($res = $DB->fetch($rs)) {
            $price_guanjia = $res['price'];
            $cost_guanjia = $res['cost'];
            $cost2_guanjia = $res['cost2'];
        }

        if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
            $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
            $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
            $result = king_Crawler($post, $url1, $url2);

            $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
            $float1 = king_Regular($result, $re1);
            $price_chengben = $float1 * $value;
        } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
            //玖伍系统开始
            $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
            $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
            $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
            $result = king_Crawler($post, $url1, $url2);

            $re1 = '/单价为(\S+)元"/';
            $float1 = king_Regular($result, $re1);
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
        $data[] = array('shequ' => $shequ_type, 'chengben' => $price_chengben, 'price' => $price, 'cost' => $cost, 'cost2' => $cost2, 'price_guanjia' => $price_guanjia, 'cost_guanjia' => $cost_guanjia, 'cost2_guanjia' => $cost2_guanjia);
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;

    case 'getcount':
        $strtotime = strtotime($conf['build']);//获取开始统计的日期的时间戳
        $now = time();//当前的时间戳
        $yxts = ceil(($now - $strtotime) / 86400);//取相差值然后除于24小时(86400秒)
        $time = date("Y-m-d") . ' 00:00:01';
        $count1 = $DB->count("SELECT count(*)*2+1214 from shua_orders");
        $count2 = $DB->count("SELECT count(*)*2+1210 from shua_orders where status>=1");
        $count3 = $DB->count("SELECT sum(money)*3+3056 from shua_pay where status=1");
        $count4 = round($count3, 2);
        $count5 = $DB->count("SELECT count(*)*2+138 from `shua_orders` WHERE  `addtime` > '$time'");
        $count6 = $DB->count("SELECT sum(money) FROM `shua_pay` WHERE `addtime` > '$time' AND `status` = 1");
        $count7 = round($count6 * 3 + 359, 2);

        $result = array("code" => 0, "yxts" => $yxts, "orders" => $count1, "orders1" => $count2, "orders2" => $count5, "money" => $count4, "money1" => $count7);
        exit(json_encode($result));
        break;
    case 'getclass':
        $rs = $DB->query("SELECT * FROM shua_class WHERE active=1 order by sort asc");
        $data = array();
        while ($res = $DB->fetch($rs)) {
            $data[] = $res;
        }
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;
    case 'gettool':
        if (isset($_POST['kw'])) {
            $kw = trim(daddslashes($_POST['kw']));
            $rs = $DB->query("SELECT * FROM shua_tools WHERE name LIKE '%{$kw}%' and active=1 order by sort asc");
        } else {
            $cid = intval($_GET['cid']);
            $rs = $DB->query("SELECT * FROM shua_tools WHERE cid='$cid' and active=1 order by sort asc");
        }
        $data = array();
        while ($res = $DB->fetch($rs)) {
            if ($is_fenzhan == true) {
                $price_obj->setToolInfo($res['tid'], $res);
                if ($price_obj->getToolDel($res['tid']) == 1) continue;
                $price = $price_obj->getToolPrice($res['tid']);
            } else $price = $res['price'];
            $data[] = array('tid' => $res['tid'], 'sort' => $res['sort'], 'name' => $res['name'], 'value' => $res['value'], 'price' => $price, 'input' => $res['input'], 'inputs' => $res['inputs'], 'alert' => $res['alert'], 'repeat' => $res['repeat'], 'multi' => $res['multi'], 'isfaka' => $res['is_curl'] == 4 ? 1 : 0);
        }
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;
    case 'getleftcount':
        $tid = intval($_POST['tid']);
        $count = $DB->count("SELECT count(*) FROM shua_faka WHERE tid='$tid' and orderid=0");
        $result = array("code" => 0, "count" => $count);
        exit(json_encode($result));
        break;
    case 'pay':
        $tid = intval($_POST['tid']);
        $inputvalue = trim(strip_tags(daddslashes($_POST['inputvalue'])));
        $inputvalue2 = trim(strip_tags(daddslashes($_POST['inputvalue2'])));
        $inputvalue3 = trim(strip_tags(daddslashes($_POST['inputvalue3'])));
        $inputvalue4 = trim(strip_tags(daddslashes($_POST['inputvalue4'])));
        $inputvalue5 = trim(strip_tags(daddslashes($_POST['inputvalue5'])));
        $num = isset($_POST['num']) ? intval($_POST['num']) : 1;
        $hashsalt = isset($_POST['hashsalt']) ? $_POST['hashsalt'] : null;
        $tool = $DB->get_row("select * from shua_tools where tid='$tid' limit 1");
        if ($tool && $tool['active'] == 1) {
            if (in_array($inputvalue, explode("|", $conf['blacklist']))) exit('{"code":-1,"msg":"你的下单账号已被拉黑，无法下单！"}');
            if ($tool['is_curl'] == 4) {
                if (!preg_match('/^[A-z0-9._-]+@[A-z0-9._-]+\.[A-z0-9._-]+$/', $inputvalue)) {
                    exit('{"code":-1,"msg":"邮箱格式不正确"}');
                }
                $count = $DB->count("SELECT count(*) FROM shua_faka WHERE tid='$tid' and orderid=0");
                if ($count == 0) exit('{"code":-1,"msg":"该商品库存卡密不足，请联系站长加卡！"}');
                if ($num > $count) exit('{"code":-1,"msg":"你所购买的数量超过库存数量！"}');
            } elseif ($tool['repeat'] == 0) {
                $thtime = date("Y-m-d") . ' 00:00:00';
                $row = $DB->get_row("select * from shua_orders where tid='$tid' and input='$inputvalue' order by id desc limit 1");
                if ($row['input'] && $row['status'] == 0)
                    exit('{"code":-1,"msg":"您今天添加的' . $tool['name'] . '正在排队中，请勿重复提交！"}');
                elseif ($row['addtime'] > $thtime)
                    exit('{"code":-1,"msg":"您今天已添加过' . $tool['name'] . '，请勿重复提交！"}');
            }
            if ($tool['validate'] == 1 && is_numeric($inputvalue)) {
                if (validate_qzone($inputvalue) == false)
                    exit('{"code":-1,"msg":"你的QQ空间设置了访问权限，无法下单！"}');
            }
            if ($tool['multi'] == 0 || $num < 1) $num = 1;
            if ($is_fenzhan == true) {
                $price_obj->setToolInfo($tid, $tool);
                $price = $price_obj->getToolPrice($tid);
            } else $price = $tool['price'];
            $need = $price * $num;
            if ($need == 0 && (empty($_SESSION['addsalt']) || $hashsalt != $_SESSION['addsalt'])) {
                exit('{"code":-1,"msg":"验证失败，请刷新页面重试"}');
            }
            unset($_SESSION['addsalt']);
            $trade_no = date("YmdHis") . rand(111, 999);
            $input = $inputvalue . ($inputvalue2 ? '|' . $inputvalue2 : null) . ($inputvalue3 ? '|' . $inputvalue3 : null) . ($inputvalue4 ? '|' . $inputvalue4 : null) . ($inputvalue5 ? '|' . $inputvalue5 : null);
            $sql = "insert into `shua_pay` (`trade_no`,`tid`,`zid`,`input`,`num`,`name`,`money`,`ip`,`userid`,`addtime`,`status`) values ('" . $trade_no . "','" . $tid . "','" . ($siterow['zid'] ? $siterow['zid'] : 1) . "','" . $input . "','" . $num . "','" . $tool['name'] . "','" . $need . "','" . $clientip . "','" . $cookiesid . "','" . $date . "','0')";
            if ($DB->query($sql)) {
                exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","need":"' . $need . '"}');
            } else {
                exit('{"code":-1,"msg":"提交订单失败！' . $DB->error() . '"}');
            }
        } else {
            exit('{"code":-2,"msg":"该商品不存在"}');
        }
        break;
    case 'checkkm':
        $km = daddslashes($_POST['km']);
        $myrow = $DB->get_row("select * from shua_kms where km='$km' limit 1");
        if (!$myrow) {
            exit('{"code":-1,"msg":"此卡密不存在！"}');
        } elseif ($myrow['usetime'] != null) {
            exit('{"code":-1,"msg":"此卡密已被使用！"}');
        }
        $tool = $DB->get_row("select * from shua_tools where tid='{$myrow['tid']}' limit 1");
        $result = array("code" => 0, "tid" => $tool['tid'], "cid" => $tool['cid'], "name" => $tool['name'], "alert" => $tool['alert'], "inputname" => $tool['input'], "inputsname" => $tool['inputs']);
        exit(json_encode($result));
        break;
    case 'card':
        if ($conf['iskami'] == 0) exit('{"code":-1,"msg":"当前站点未开启卡密下单"}');
        $km = daddslashes($_POST['km']);
        $inputvalue = trim(strip_tags(daddslashes($_POST['inputvalue'])));
        $inputvalue2 = trim(strip_tags(daddslashes($_POST['inputvalue2'])));
        $inputvalue3 = trim(strip_tags(daddslashes($_POST['inputvalue3'])));
        $inputvalue4 = trim(strip_tags(daddslashes($_POST['inputvalue4'])));
        $inputvalue5 = trim(strip_tags(daddslashes($_POST['inputvalue5'])));
        $myrow = $DB->get_row("select * from shua_kms where km='$km' limit 1");
        if (!$myrow) {
            exit('{"code":-1,"msg":"此卡密不存在！"}');
        } elseif ($myrow['usetime'] != null) {
            exit('{"code":-1,"msg":"此卡密已被使用！"}');
        } else {
            $tid = $myrow['tid'];
            $tool = $DB->get_row("select * from shua_tools where tid='$tid' limit 1");
            if ($tool && $tool['active'] == 1) {
                if (in_array($inputvalue, explode("|", $conf['blacklist']))) exit('{"code":-1,"msg":"你的下单账号已被拉黑，无法下单！"}');
                if ($tool['repeat'] == 0) {
                    $row = $DB->get_row("select * from shua_orders where tid='$tid' and input='$inputvalue' order by id desc limit 1");
                    $thtime = date("Y-m-d") . ' 00:00:00';
                    if ($row['input'] && $row['status'] == 0)
                        exit('{"code":-1,"msg":"您今天添加的' . $tool['name'] . '正在排队中，请勿重复提交！"}');
                    elseif ($row['addtime'] > $thtime)
                        exit('{"code":-1,"msg":"您今天已添加过' . $tool['name'] . '，请勿重复提交！"}');
                }
                if ($tool['validate'] && is_numeric($inputvalue)) {
                    if (validate_qzone($inputvalue) == false)
                        exit('{"code":-1,"msg":"你的QQ空间设置了访问权限，无法下单！"}');
                }
                $srow['tid'] = $tid;
                $srow['input'] = $inputvalue . ($inputvalue2 ? '|' . $inputvalue2 : null) . ($inputvalue3 ? '|' . $inputvalue3 : null) . ($inputvalue4 ? '|' . $inputvalue4 : null) . ($inputvalue5 ? '|' . $inputvalue5 : null);
                $srow['num'] = 1;
                $srow['zid'] = $siterow['zid'];
                $srow['userid'] = $cookiesid;
                $srow['trade_no'] = 'kid:' . $myrow['kid'];
                if (processOrder($srow)) {
                    $row = $DB->get_row("select * from shua_orders where tid='$tid' and input='$inputvalue' order by id desc limit 1");
                    $DB->query("update `shua_kms` set `user` ='$inputvalue',`usetime` ='" . $date . "' where `kid`='{$myrow['kid']}'");
                    exit('{"code":0,"msg":"' . $tool['name'] . ' 下单成功！你可以在进度查询中查看代刷进度","orderid":"' . $row['id'] . '"}');
                } else {
                    exit('{"code":-1,"msg":"' . $tool['name'] . ' 下单失败！' . $DB->error() . '"}');
                }
            } else {
                exit('{"code":-2,"msg":"该商品不存在"}');
            }
        }
        break;
    case 'query':
        $qq = trim(daddslashes($_POST['qq']));
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $rs = $DB->query("SELECT * FROM shua_tools WHERE 1 order by sort asc");
        while ($res = $DB->fetch($rs)) {
            $shua_func[$res['tid']] = $res['name'];
        }
        if (empty($qq)) $sql = " userid='{$cookiesid}'";
        else $sql = " input='{$qq}'";
        $rs = $DB->query("SELECT * FROM shua_orders WHERE{$sql} order by id desc limit $limit");
        $data = array();
        while ($res = $DB->fetch($rs)) {
            $data[] = array('id' => $res['id'], 'tid' => $res['tid'], 'input' => $res['input'], 'name' => $shua_func[$res['tid']], 'value' => $res['value'], 'addtime' => $res['addtime'], 'endtime' => $res['endtime'], 'result' => $res['result'], 'status' => $res['status'], 'skey' => md5($res['id'] . SYS_KEY . $res['id']));
        }
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;
    case 'order': //订单进度查询
        $id = intval($_POST['id']);
        if (md5($id . SYS_KEY . $id) !== $_POST['skey']) exit('{"code":-1,"msg":"验证失败"}');
        $row = $DB->get_row("select * from shua_orders where id='$id' limit 1");
        if (!$row)
            exit('{"code":-1,"msg":"当前订单不存在！"}');
        $tool = $DB->get_row("select * from shua_tools where tid='{$row['tid']}' limit 1");
        if ($tool['is_curl'] == 2) {
            $shequ = $DB->get_row("select * from shua_shequ where id='{$tool['shequ']}' limit 1");
            if ($shequ['type'] == 1) {
                $list = yile_chadan($shequ['url'], $tool['goods_id'], $row['input'], $row['djorder']);
            } elseif ($shequ['type'] == 0 || $shequ['type'] == 2) {
                $list = jiuwu_chadan($shequ['url'], $tool['goods_id'], $row['input'], $row['djorder']);
            } elseif ($shequ['type'] == 3 || $shequ['type'] == 5) {
                $list = xmsq_chadan($shequ['url'], $tool['goods_id'], $row['input'], $row['djorder']);
            }
        } elseif ($tool['is_curl'] == 4) {
            $count = $row['value'];
            $rs = $DB->query("SELECT * FROM shua_faka WHERE tid='{$row['tid']}' AND orderid='$id' LIMIT {$count}");
            $kmdata = '';
            while ($res = $DB->fetch($rs)) {
                if (!empty($res['pw'])) {
                    $kmdata .= '卡号：' . $res['km'] . ' 密码：' . $res['pw'] . '<br/>';
                } else {
                    $kmdata .= $res['km'] . '<br/>';
                }
            }
        }
        $input = $tool['input'] ? $tool['input'] : '下单QQ';
        if ($tool['is_curl'] == 4) $input = '联系方式';
        $inputs = explode('|', $tool['inputs']);
        $result = array('code' => 0, 'msg' => 'succ', 'name' => $tool['name'], 'money' => $row['money'], 'date' => $row['addtime'], 'inputs' => showInputs($row, $input, $inputs), 'list' => $list, 'kminfo' => $kmdata, 'alert' => $tool['alert']);
        exit(json_encode($result));
        break;
    case 'fill':
        $orderid = daddslashes($_POST['orderid']);
        if (md5($orderid . SYS_KEY . $orderid) !== $_POST['skey']) exit('{"code":-1,"msg":"验证失败"}');
        $row = $DB->get_row("select * from shua_orders where id='$orderid' limit 1");
        if ($row) {
            if ($row['status'] == 3) {
                $DB->query("update `shua_orders` set `status` ='0',result=NULL where `id`='{$orderid}'");
                $result = array("code" => 0, "msg" => "已成功补交订单");
            } else {
                $result = array("code" => 0, "msg" => "该订单不符合补交条件");
            }
        } else {
            $result = array("code" => -1, "msg" => "订单不存在");
        }
        exit(json_encode($result));
        break;
    case 'lqq':
        $qq = daddslashes($_POST['qq']);
        if (empty($qq) || empty($_SESSION['addsalt']) || $_POST['salt'] != $_SESSION['addsalt']) exit('{"code":-5,"msg":"非法请求"}');
        get_curl($conf['lqqapi'] . $qq);
        $result = array("code" => 0, "msg" => "succ");
        exit(json_encode($result));
        break;
    case 'getshuoshuo':
        $uin = daddslashes($_GET['uin']);
        $page = intval($_GET['page']);
        if (empty($uin)) exit('{"code":-5,"msg":"QQ号不能为空"}');
        $result = getshuoshuo($uin, $page);
        exit(json_encode($result));
        break;
    case 'getrizhi':
        $uin = daddslashes($_GET['uin']);
        $page = intval($_GET['page']);
        if (empty($uin)) exit('{"code":-5,"msg":"QQ号不能为空"}');
        $result = getrizhi($uin, $page);
        exit(json_encode($result));
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}