<?php
include("./includes/common.php");
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

if ($is_fenzhan == true) {
    $price_obj = new Price($siterow['zid'], $siterow);
}
switch ($act) {

    case 'getgoodtid':
        $goods_id;
        $price;
        $cost;
        $cost2;
        $value;
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


        if ($shequ_type == 1 || $shequ_type == "1") {
//            亿乐社区开始
            $post = "user=" . $shequ_account . "&pwd=" . $shequ_pwd . "";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://" . $shequ_url . "/index/index_ajax/user/action/login.html");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
            $result = curl_exec($ch);
            $post = "";
            curl_setopt($ch, CURLOPT_URL, "http://" . $shequ_url . "/index/home/order/id/" . $goods_id . ".html");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
            $result = curl_exec($ch);
            curl_close($ch);
            $re1 = '/Number\(\"([0-9]+\.\S+)\"/';
            $float1;
            if ($c = preg_match_all($re1, $result, $matches)) {
                $float1 = $matches[1][0];
            }
            
            $data[] = array('chengben' => $float1 * $value, 'price' => $price, 'cost' => $cost, 'cost2' => $cost2);
            $result = array("code" => 0, "msg" => "succ", "data" => $data);
            exit(json_encode($result));
        }
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