<?php
include("./includes/common.php");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
@header('Content-Type: application/json; charset=UTF-8');
if ($is_fenzhan == true) {
    $price_obj = new Price($siterow['zid'], $siterow);
}

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

switch ($act) {
    case 'change_discount':
        //代刷管家 - 折扣率修改
        $code = 0;
        $lock = $_GET['lock'];
        $sql = "UPDATE `shua_guanjia_config` SET `v` = '" . $lock . "' WHERE `shua_guanjia_config`.`k` = 'Discont';";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "设置失败";
        }
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;
        break;
    case 'check_yile_config':
        //代刷管家 - 检测亿乐对接值
        $key = 0;
        $rs = $DB->query("SELECT * FROM `shua_shequ` WHERE type = 1");
        while ($res = $DB->fetch($rs)) {
            $yile_key = $res['v'];
            if (strlen($res['password']) != 32) {
                $key = 1;
            }
        }
        if ($key == 1) {
            exit("1");
        }
        exit("0");
        break;

    case 'change_gjkey':
        //代刷管家 - 密匙修改
        $code = 0;
        $lock = $_GET['lock'];
        $sql = "UPDATE `shua_guanjia_config` SET `v` = '" . $lock . "' WHERE `shua_guanjia_config`.`k` = 'gjKey';";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "设置失败";
        }
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;

    case 'change_is_mh':
        //代刷管家 - 价格美化开关
        $code = 0;
        $lock = $_GET['lock'];
        $sql = "UPDATE `shua_guanjia_config` SET `v` = '" . $lock . "' WHERE `shua_guanjia_config`.`k` = 'isMH';";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "设置失败";
        }
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;
    case 'db_return':
        //代刷管家 - 还原
        $code = 0;
        $_sql = file_get_contents("guanjia_db.sql");
        $_arr = explode(';', $_sql);
        $_mysqli = new mysqli($dbconfig['host'], $dbconfig['user'], $dbconfig['pwd'], $dbconfig['dbname']);//第一个参数为域名，第二个为用户名，第三个为密码，第四个为数据库名字
        if (mysqli_connect_errno()) {
            exit('连接数据库出错');
        } else {
            //执行sql语句
            $_mysqli->query('set names utf8;');
            foreach ($_arr as $_value) {
                $_mysqli->query($_value . ';');
            }
            $code = 1;
            $msg = "设置成功";
        }
        $_mysqli->close();
        $_mysqli = null;
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;
    case 'db_backup':
        //代刷管家 - 备份当前数据
        $code = 0;
        $showtime = date("Y-m-d H:i:s");
        //配置信息
        $cfg_dbhost = $dbconfig['host'];
        $cfg_dbname = $dbconfig['dbname'];
        $cfg_dbuser = $dbconfig['user'];
        $cfg_dbpwd = $dbconfig['pwd'];
        $cfg_db_language = 'utf8';
        $to_file_name = "guanjia_db.sql";
        // END 配置
        //链接数据库
        $link = mysql_connect($cfg_dbhost, $cfg_dbuser, $cfg_dbpwd);
        mysql_select_db($cfg_dbname);
        //选择编码
        mysql_query("set names " . $cfg_db_language);
        //数据库中有哪些表
        $tables = mysql_list_tables($cfg_dbname);
        //将这些表记录到一个数组
        $tabList = array();
        while ($row = mysql_fetch_row($tables)) {
            $tabList[] = $row[0];
        }
        $fp = fopen($to_file_name, "w+");
        fputs($fp, "");
        fclose($fp);
        $info = "-- ----------------------------\r\n";
        $info .= "-- 日期：" . date("Y-m-d H:i:s", time()) . "\r\n";
        $info .= "-- DsProtect DB BackUp 代刷管家数据备份\r\n";
        $info .= "-- ----------------------------\r\n\r\n";
        file_put_contents($to_file_name, $info, FILE_APPEND);
        foreach ($tabList as $val) {
            if ($val != $dbconfig['dbqz'] . "_tools") {
                continue;
            }
            $sql = "show create table " . $val;
            $res = mysql_query($sql, $link);
            $row = mysql_fetch_array($res);
            $info = "-- ----------------------------\r\n";
            $info .= "-- Table structure for `" . $val . "`\r\n";
            $info .= "-- ----------------------------\r\n";
            $info .= "DROP TABLE IF EXISTS `" . $val . "`;\r\n";
            $sqlStr = $info . $row[1] . ";\r\n\r\n";
            //追加到文件
            file_put_contents($to_file_name, $sqlStr, FILE_APPEND);
            //释放资源
            mysql_free_result($res);
        }
        foreach ($tabList as $val) {
            if ($val != $dbconfig['dbqz'] . "_tools") {
                continue;
            }
            $sql = "select * from " . $val;
            $res = mysql_query($sql, $link);
            if (mysql_num_rows($res) < 1) continue;
            //
            $info = "-- ----------------------------\r\n";
            $info .= "-- Records for `" . $val . "`\r\n";
            $info .= "-- ----------------------------\r\n";
            file_put_contents($to_file_name, $info, FILE_APPEND);
            while ($row = mysql_fetch_row($res)) {
                $sqlStr = "INSERT INTO `" . $val . "` VALUES (";
                foreach ($row as $zd) {
                    $sqlStr .= "'" . $zd . "', ";
                }
                $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 2);
                $sqlStr .= ");\r\n";
                file_put_contents($to_file_name, $sqlStr, FILE_APPEND);
            }
            mysql_free_result($res);
            file_put_contents($to_file_name, "\r\n", FILE_APPEND);
        }
        $sql = "UPDATE `shua_guanjia_config` SET `v` = '" . $showtime . "' WHERE `shua_guanjia_config`.`k` = 'dbBackUpTime';";
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
    case 'change_atuo_sjx':
        //代刷管家 - 自动上下架
        $code = 0;
        $lock = $_GET['lock'];
        $sql = "UPDATE `shua_guanjia_config` SET `v` = '" . $lock . "' WHERE `shua_guanjia_config`.`k` = 'isAutoGrounding';";
        if ($DB->query($sql)) {
            $code = 1;
            $msg = "设置成功";
        } else {
            $code = 0;
            $msg = "设置失败";
        }
        $result = array("code" => $code, "msg" => $msg);
        exit(json_encode($result));
        break;
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
        $status = intval($_GET['status']);
        $multi = $_GET['multi'];
        $yh_pi = intval($_GET['yh_pi']);
        $pj_pi = intval($_GET['pj_pi']);
        $zy_pi = intval($_GET['zy_pi']);
        $yh_pi = $yh_pi / 100;
        $pj_pi = $pj_pi / 100;
        $zy_pi = $zy_pi / 100;


        $multi_text = explode(' ', $multi);
        for ($i = 0; $i < count($multi_text); $i++) {
            $sql = "UPDATE shua_guanjia 
INNER JOIN shua_tools
ON shua_guanjia.tid = shua_tools.tid
SET shua_guanjia.price = " . $yh_pi . ",  shua_guanjia.status = " . $status . ",  shua_guanjia.cost = " . $pj_pi . ", shua_guanjia.cost2 = " . $zy_pi . "
WHERE shua_tools.cid = " . $multi_text[$i];
            if ($DB->query($sql)) {

            } else {
                $result = array("code" => "0", "msg" => $multi_text[$i] . "设置失败");
                exit(json_encode($result));
            }
        }
        $result = array("code" => "1", "msg" => $msg . "正常流程");
        exit(json_encode($result));
        break;
    case 'setguani_pl':
        //代刷管家 - 批量设置
        $code = 0;
        $status = intval($_GET['status']);
        $tid = intval($_GET['tid']);
        $yh_pi = intval($_GET['yh_pi']);
        $pj_pi = intval($_GET['pj_pi']);
        $zy_pi = intval($_GET['zy_pi']);
        $yh_pi = $yh_pi / 100;
        $pj_pi = $pj_pi / 100;
        $zy_pi = $zy_pi / 100;
        $sign_1 = 0;
        $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $tid);
        if ($res = $DB->fetch($rs)) {
        } else {
            $result = array("code" => 1, "msg" => "无此商品");
            exit(json_encode($result));
            break;
        }
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户倍率 1=普及倍率 2=专业倍率 3=状态
        $data_2[4][1];
        //收集所有商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本 4=商品类型
        $data_3[5][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型 4=paytype(九五时 点数下单0 余额下单1)
        $data_4[5][1];
        //收集上个商品信息 0=社区ID 1=商品ID 2=商品数量 3=成本_用户 4=成本_普及 5=成本_专业
        $data_5[6][1];
        //自动上下架  1=是 2=否
        $auto_sjx;
        $rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'isAutoGrounding'");
        while ($res = $DB->fetch($rs)) {
            $auto_sjx = $res['v'];
        }
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $sign_1++;
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
            $data_3[4][0] = $res['is_curl'];
        }
        if ($sign_1 == 0) {
            //不存在此商品 直接跳出
            $code = 1;
            $msg = "此ID商品已被删除，自动跳过";
            $result = array("code" => $code, "msg" => $msg, "name" => "无");
            exit(json_encode($result));
            break;
        }
        if ($data_3[4][0] != "2") {
            //如果是自营商品 直接跳出
            $code = 1;
            $msg = "设置成功，自营商品";
            $result = array("code" => $code, "msg" => $msg);
            exit(json_encode($result));
            break;
        }
//        $last_tid = $tid - 1;
//        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
//        if ($res = $DB->fetch($rs)) {
//            $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $last_tid);
//            while ($res = $DB->fetch($rs)) {
//                $data_5[0][0] = $res['shequ'];
//                $data_5[1][0] = $res['goods_id'];
//                $data_5[2][0] = $res['value'];
//            }
//            if ($data_5[0][0] == $data_3[0][0] && $data_5[1][0] == $data_3[1][0]) {
//                //如果是与上个商品同一款
//                $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $last_tid);
//                while ($res = $DB->fetch($rs)) {
//                    $data_5[3][0] = $res['price'];
//                    $data_5[4][0] = $res['cost'];
//                    $data_5[5][0] = $res['cost2'];
//                }
//                if ($data_5[0][0] != 0 && $data_5[1][0] != 0 && $data_5[2][0] != 0 && $data_5[0][0] != null && $data_5[1][0] != null && $data_5[2][0] != null) {
//                    //上款商品不为0不为空
//                    $goods_bl = $data_3[2][0] / $data_5[2][0];
//                    $total_1 = $data_5[3][0] * $goods_bl;
//                    $total_2 = $data_5[4][0] * $goods_bl;
//                    $total_3 = $data_5[5][0] * $goods_bl;
//                    $total_1 = $total_1 * ($yh_pi - 1);
//                    $total_2 = $total_1 * ($pj_pi - 1);
//                    $total_3 = $total_1 * ($zy_pi - 1);
//                    $sql = "UPDATE `shua_guanjia` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
//                    if ($DB->query($sql)) {
//                        $code = 1;
//                        $msg = "设置成功";
//                    } else {
//                        $code = 0;
//                        $msg = "设置失败，错误代码-fo1";
//                    }
//                    $result = array("code" => $code, "msg" => $msg . "跳过流程");
//                    exit(json_encode($result));
//                    break;
//                } else {
//                    $code = 1;
//                    $msg = "设置成功，商品维护/不支持的商品类型";
//                }
//            }
//        }
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
            $data_3[3][0] = "1";
        } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
            //玖伍系统开始
            $data_3[3][0] = "2";
        } else if ($shequ_type == 3 || $shequ_type == "3" || $shequ_type == 5 || $shequ_type == "5") {
            //星墨社区开始
            $data_3[3][0] = "3";
        } else {
            $data_3[3][0] = "0";
        }
        if ($data_3[3][0] != "0") {
            //先判断是否商品维护
//            $total_1 = $data_3[3][0] * ($yh_pi - 1);   //客户管家值
//            if ($total_1 < 0.01) {
//                $total_1 = 0.01;
//            }
//            $total_2 = $data_3[3][0] * ($pj_pi - 1);   //普及管家值
//            if ($total_2 < 0.01) {
//                $total_2 = 0.01;
//            }
//            $total_3 = $data_3[3][0] * ($zy_pi - 1);   //专业管家值
//            if ($total_3 < 0.01) {
//                $total_3 = 0.01;
//            }
            $sql = "UPDATE `shua_guanjia` SET `price` = " . $yh_pi . ",`status` = " . $status . ", `cost` = " . $pj_pi . ", `cost2` = " . $zy_pi . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
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
        //收集所有商品柜价 0=客户购价 1=普及购价 2=专业购价
        $data_1[3][1];
        //收集所有shua_guanjia 0=客户倍率 1=普及倍率 2=专业倍率 3=状态
        $data_2[4][1];
        //收集商品信息 0=社区ID 1=商品ID 2=数量 3=商品成本 4=商品类型 5=商品名称 6=上架状态
        $data_3[6][1];
        //收集所社区信息 0=社区URL 1=社区帐号 2=社区密码 3=社区类型 4=paytype(九五时 点数下单0 余额下单1)
        $data_4[5][1];
        //收集上个商品信息 0=社区ID 1=商品ID 2=商品数量 3=成本_用户 4=成本_普及 5=成本_专业 6=上架状态
        $data_5[6][1];
        //收集上个商品管家信息 0=price 1=cost 2=cost2
        $data_6[3][1];
        //自动上下架  1=是 2=否
        $auto_sjx;
        //折扣率
        $discount;
        //检测商品是否维护
        $yile_lock = 0;
        //美化lock
        $mh;
        //记录1
        $sign_1 = 0;
        $rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a");
        while ($res = $DB->fetch($rs)) {
            if ($res['k'] == "isAutoGrounding") {
                $auto_sjx = $res['v'];
            }
            if ($res['k'] == "isMH") {
                $mh = $res['v'];
            }
            if ($res['k'] == "Discont") {
                $discount = $res['v'];
            }
        }
        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid =" . $tid);
        while ($res = $DB->fetch($rs)) {
            $sign_1++;
            $data_1[0][0] = $res['price'];
            $data_1[1][0] = $res['cost'];
            $data_1[2][0] = $res['cost2'];
            $data_3[0][0] = $res['shequ'];
            $data_3[1][0] = $res['goods_id'];
            $data_3[2][0] = $res['value'];
            $data_3[4][0] = $res['is_curl'];
            $data_3[5][0] = $res['name'];
            $data_3[6][0] = $res['active'];
        }
        if ($sign_1 == 0) {
            //不存在此商品 直接跳出
            $code = 1;
            $msg = "此ID商品已被删除，自动跳过";
            $result = array("code" => $code, "msg" => $msg, "name" => "无");
            exit(json_encode($result));
            break;
        }
        if ($data_3[4][0] != "2") {
            //如果是自营商品 直接跳出
            $code = 1;
            $msg = "自营商品，自动跳过";
            $result = array("code" => $code, "msg" => $msg, "name" => $data_3[5][0]);
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
                    $data_5[6][0] = $res['active'];
                }
                $rs = $DB->query("SELECT * FROM shua_guanjia WHERE tid =" . $last_tid);
                while ($res = $DB->fetch($rs)) {
                    $data_6[0][0] = $res['price'];
                    $data_6[1][0] = $res['cost'];
                    $data_6[2][0] = $res['cost2'];
                }
                if ($data_5[3][0] > 1 && $data_5[4][0] > 1 && $data_5[5][0] > 1 && $data_6[0][0] != null && $data_6[1][0] != null && $data_6[2][0] != null) {
                    //上款商品不为0不为空不为0.01、且上个商品有管家值
                    //上个商品必须 不为美化之后的价格 不然继承美化后的价格 将出错
                    $goods_bl = $data_3[2][0] / $data_5[2][0];  // 这个商品：上个商品
                    if ($data_3[2][0] > $data_5[2][0] && $discount != 0) {
                        //开启了折扣率
                        $num = floatval($discount) / 100;
                        $total_1 = $data_5[3][0] * $goods_bl * $num;
                        $total_2 = $data_5[4][0] * $goods_bl * $num;
                        $total_3 = $data_5[5][0] * $goods_bl * $num;
                        if ($mh == 1 || $mh == "1") {
                            $total_1 = round($total_1, 1);
                            $total_2 = round($total_2, 1);
                            $total_3 = round($total_3, 1);
                        } else {
                            $total_1 = round($total_1, 2);
                            $total_2 = round($total_2, 2);
                            $total_3 = round($total_3, 2);
                        }
                        $msg = "折扣操作";
                    } else {
                        $total_1 = $data_5[3][0] * $goods_bl;
                        $total_2 = $data_5[4][0] * $goods_bl;
                        $total_3 = $data_5[5][0] * $goods_bl;
                    }
                    $sql = "UPDATE `shua_tools` SET `price` = " . $total_1 . ", `cost` = " . $total_2 . ", `cost2` = " . $total_3 . " WHERE `shua_tools`.`tid` = " . $tid . ";";
                    if ($DB->query($sql)) {
                        if ($data_5[6][0] == "0" || $data_5[6][0] == 0) {
                            //如果上个商品下架了 一起下架
                            $sql = "UPDATE `shua_tools` SET `active` = '0' WHERE `shua_tools`.`tid` = " . $tid . ";";
                            $DB->query($sql);
                        } else{
                            $sql = "UPDATE `shua_tools` SET `active` = '1' WHERE `shua_tools`.`tid` = " . $tid . ";";
                            $DB->query($sql);
                        }
                        $code = 1;
                        $msg = $msg . "设置成功";
                    } else {
                        $code = 0;
                        $msg = $msg . "设置失败，错误代码-ffo1";
                    }
                    $result = array("code" => $code, "msg" => $msg . "与上个商品同款，已进行同样设置。", "name" => $data_3[5][0]);
                    exit(json_encode($result));
                    break;
                } else {
                }
            }
        }
        //不是与上个商品同款 则进入爬取成本步骤
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
//                $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
//                $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
//                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
//                $result = king_Crawler($post, $url1, $url2);
//                sleep(2);
//                $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
//                if ($sign > 0) {
//                } else {
//                    sleep(2);
//                    $test = king_Crawler_2($url, "", "", "");
//                    $data_sign = midstr($test, "'cookie' : \"", "\",");
//                    $data_date = king_get_Date();
//                    $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
//                    sleep(2);
//                    $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
//                }
//
//                $yile_lock = stripos($result, "禁止下单，业务维护中！");
//                $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
//                $float1 = king_Regular($result, $re1);
//                $data_3[3][0] = $float1 * $data_3[2][0];
                $test = king_get_yile($shequ_account, $shequ_pwd, $shequ_url, $goods_id);
                $json = json_decode($test);
                $status = $json->status . "";
                $messag = $json->message;
                if ($status == "-105") {
                    $price_chengben = "该商品对接社区Token配置有误，跳过设置";
                    $result = array("code" => "1", "msg" => $price_chengben, "name" => $data_3[5][0]);
                    exit(json_encode($result));
                }

                if ($status == "-1") {
                    $price_chengben = "该商品ID在社区不存在，跳过设置并下架";
                    $sql = "UPDATE `shua_tools` SET `active` = '0' WHERE `shua_tools`.`tid` = " . $tid . ";";
                    $DB->query($sql);
                    $result = array("code" => "1", "msg" => $price_chengben, "name" => $data_3[5][0]);
                    exit(json_encode($result));
                }
                if ($status == "0") {
                    $close = $json->data->close . "";
                    if ($close == "1") {
                        $data_3[3][0] = 0;
                    } else {
                        $float1 = floatval($json->data->price . "");
                        $data_3[3][0] = $float1 * $data_3[2][0];
                    }
                }
            } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
                //玖伍系统开始
                $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
                $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
                $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
                $result = king_Crawler($post, $url1, $url2);
//                $re1 = '/单价为(\S+)元"/';
//                $float1 = king_Regular($result, $re1);
                $float1 = midstr($result, "display:none;\">", "</span>");
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
            } else if ($shequ_type == 11 || $shequ_type == "11") {
                //聚梦社区开始
                $i = 0;
                $result = king_Crawler_2("http://" . $shequ_url . "/Order/ApiGoods.html", "", "", "");
                $json = json_decode($result, true);
                $length = sizeof($json);
                for ($i = 0; $i < $length; $i++) {
                    if ($json[$i]['Id'] == $goods_id) {
//                        $float1 = $json[$i]['Money'];
                        break;
                    }
                }
                if ($json[$i]['MoneyStatus'] != 1) {
                    $data_3[3][0] = 0;
                } else {
                    $post = "id=" . $goods_id . "&user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&jz=0";
                    $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
                    $url2 = "http://" . $shequ_url . "/form.html?goodsid=" . $goods_id;
                    $result = king_Crawler($post, $url1, $url2);
                    sleep(1);
                    $float1 = midstr($result, "id=\"money_dian\">", "</span>");
                    if (strlen($float1) == 0) {
                        $float1 = midstr($result, "user_unit_rmb\" style=\"display:none;\">", "</span>");
                    }
                    $data_3[3][0] = $float1 * $data_3[2][0];
                }
            } else {
                $data_3[3][0] = "0";
            }
            if ($data_3[3][0] != "0" || $data_3[3][0] != 0) {
                //先判断是否商品维护
                if ($data_2[3][0] == "1" || $data_2[3][0] == 1) {
                    //如果是定值加价
                    $total_1 = $data_3[3][0] + $data_2[0][0];     //客户购价
                    $total_2 = $data_3[3][0] + $data_2[1][0];     //普及购价
                    $total_3 = $data_3[3][0] + $data_2[2][0];    //专业购价
                } else {
                    //倍率加价
                    if ($data_3[3][0] <= 0.01) {
                        $total_1 = 0.01;
                        $total_2 = 0.01;
                        $total_3 = 0.01;
                    } else {
                        $total_1 = $data_3[3][0] * $data_2[0][0];     //客户购价
                        $total_2 = $data_3[3][0] * $data_2[1][0];     //普及购价
                        $total_3 = $data_3[3][0] * $data_2[2][0];    //专业购价
                    }
                }
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
                    if ($auto_sjx == 1 || $auto_sjx == "1") {
                        //商品价格正常设置 恢复上架
                        $sql = "UPDATE `shua_tools` SET `active` = '1' WHERE `shua_tools`.`tid` = " . $tid . ";";
                        $DB->query($sql);
                    }
                } else {
                    $code = 0;
                    $msg = "设置失败，错误代码-eo1";
                }
            } else {
                $code = 1;
                $msg = "商品维护，直接跳过";
                if ($auto_sjx == 1 || $auto_sjx == "1") {
                    //如果商品维护 / 不存在 自动下架该商品
                    $sql = "UPDATE `shua_tools` SET `active` = '0' WHERE `shua_tools`.`tid` = " . $tid . ";";
                    $DB->query($sql);
                    $msg = "商品维护，直接跳过，并下架此商品";
                }
            }
        } else {
            $code = 1;
            $msg = "未设置管家倍率，直接跳过";
        }
        $result = array("code" => $code, "msg" => $msg, "name" => $data_3[5][0]);
        exit(json_encode($result));
        break;
    case
    'setguanjia':
        //代刷管家 - 变量用户设置
        $code = 0;
        $status = intval($_GET['status']);
        $tid = intval($_GET['tid']);
        $price = intval($_GET['price']);
        $cost = intval($_GET['cost']);
        $cost_2 = intval($_GET['cost_2']);
        $price = $price / 100;
        $cost = $cost / 100;
        $cost_2 = $cost_2 / 100;
        $sql = "UPDATE `shua_guanjia` SET `price` = " . $price . ", `cost` = " . $cost . ", `cost2` = " . $cost_2 . ", `status` = " . $status . " WHERE `shua_guanjia`.`tid` = " . $tid . ";";
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
        $status_guanjia;
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
        $json;
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
            $status_guanjia = $res['status'];
        }
        if ($is_curl == "2") {
            if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
//                $url1 = "http://" . $shequ_url . "/index/index_ajax/user/action/login.html";
//                $url2 = "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html";
//                $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
//                $result = king_Crawler($post, $url1, $url2);
//                $sign = stripos($result, "<title>");//根据有无<title>判断是否处于防护中
//                if ($sign > 0) {
//                } else {
//                    $test = king_Crawler_2($url, "", "", "");
//                    $data_sign = midstr($test, "'cookie' : \"", "\",");
//                    $data_date = king_get_Date();
//                    $yile_cookie = "verynginx_sign_javascript=" . $data_sign . "; path=/; expires=" . $data_date;
//                    $result = king_Crawler_1($url1, $url2, "", $post, $yile_cookie);
//                }
//                $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
//                $float1 = king_Regular($result, $re1);
                $test = king_get_yile($shequ_account, $shequ_pwd, $shequ_url, $goods_id);
                $json = json_decode($test);
                $status = $json->status . "";
                $messag = $json->message;
                if ($status == "-105") {
                    $price_chengben = "该对接社区Token配置有误";
                }

                if ($status == "-1") {
                    $price_chengben = "该商品ID不存在";
                }
                if ($status == "0") {
                    $close = $json->data->close . "";
                    if ($close == "1") {
                        $price_chengben = 0;
                    } else {
                        $float1 = floatval($json->data->price . "");
                        $price_chengben = $float1 * $value;
                    }
                }
            } else if ($shequ_type == 0 || $shequ_type == "0" || $shequ_type == 2 || $shequ_type == "2") {
                //玖伍系统开始
                $post = "username=" . $shequ_account . "&username_password=" . $shequ_pwd . "";
                $url1 = "http://" . $shequ_url . "/index.php?m=Home&c=User&a=login&id=&goods_type=";
                $url2 = "http://" . $shequ_url . "/index.php?m=home&c=goods&a=detail&id=" . $goods_id;
                $result = king_Crawler($post, $url1, $url2);
//                $re1 = '/单价为(\S+)元"/';
//                $float1 = king_Regular($result, $re1);
                $float1 = midstr($result, "display:none;\">", "</span>");
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
            } else if ($shequ_type == 11 || $shequ_type == "11") {
                //聚梦社区开始
                $i = 0;
                $result = king_Crawler_2("http://" . $shequ_url . "/Order/ApiGoods.html", "", "", "");
                $json = json_decode($result, true);
                $length = sizeof($json);
                for ($i = 0; $i < $length; $i++) {
                    if ($json[$i]['Id'] == $goods_id) {
//                        $float1 = $json[$i]['Money'];
                        break;
                    }
                }
                if ($json[$i]['MoneyStatus'] != 1) {
                    $price_chengben = "商品维护";
                } else {
                    $post = "id=" . $goods_id . "&user=" . $shequ_account . "&pwd=" . $shequ_pwd . "&jz=0";
                    $url1 = "http://" . $shequ_url . "/Login/UserLogin.html";
                    $url2 = "http://" . $shequ_url . "/form.html?goodsid=" . $goods_id;
                    $result = king_Crawler($post, $url1, $url2);
                    $float1 = midstr($result, "id=\"money_dian\">", "</span>");
                    if (strlen($float1) == 0) {
                        $float1 = midstr($result, "user_unit_rmb\" style=\"display:none;\">", "</span>");
                    }
                    $price_chengben = $float1 * $value;
                }
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
                $shequ_type = "12";
            } else if ($is_curl == "1") {
                $shequ_type = "13";
            } else if ($is_curl == "3") {
                $shequ_type = "14";
            } else if ($is_curl == "4") {
                $shequ_type = "15";
            }
        }
        $data[] = array('shequ' => $shequ_type, 'shequ_url' => $shequ_url, 'chengben' => $price_chengben, 'status_guanjia' => $status_guanjia, 'price' => $price, 'cost' => $cost, 'cost2' => $cost2, 'price_guanjia' => $price_guanjia, 'cost_guanjia' => $cost_guanjia, 'cost2_guanjia' => $cost2_guanjia);
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        exit(json_encode($result));
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}