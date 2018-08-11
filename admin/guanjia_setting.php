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


$ver = "2.0";

$title = '代刷管家 - 设置';
include './head.php';

$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'isAutoGrounding'");
while ($res = $DB->fetch($rs)) {
    $auto_sjx = $res['v'];
}

?>
<link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">
<script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>
<div class="container" style="padding-top:100px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
        <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title"> 代刷管家 - 设置</h3></div>
            <div class="panel-body">
                <div class="alert alert-info">
                    自动上下架是根据社区商品是否维护决定，且只有在每次监控时才会运作。
                </div>
                <div class="form-group">
                    <input id="atuo_sxj" class="<?php echo $auto_sjx ?>" type="checkbox" <?php if ($auto_sjx == "1") {
                        echo "checked";
                    } elseif ($auto_sjx == "0") echo "" ?>> 自动上下架
                </div>
                <div class="alert alert-info">
                    待定设置，敬请期待
                </div>
                <div class="form-group">
                    <input type="checkbox" disabled> 待定设置
                </div>
                <div class="alert alert-info">
                    待定设置，敬请期待
                </div>
                <div class="form-group">
                    <input type="checkbox" disabled> 待定设置
                </div>
            </div>
        </div>
        <center>
            代刷管家 - 在线版&nbsp;&nbsp;&nbsp;作者：<a
                    href="http://wpa.qq.com/msgrd?v=3&amp;uin=1776885812&amp;site=qq&amp;menu=yes">KING</a><br>
            本程序由<a href="http://www.idcyun.wang"><img src="http://gj.dkfirst.cn/images/jmyidc.png" style="width: 70px;"></a>提供服务引擎
            当前版本：<?php echo $ver ?> 历史版本：<a target="_blank" href="http://zeink.cn/?p=255">【点击查看】</a>
        </center>
    </div>
</div>
</body>


<script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
<script>
    $("#atuo_sxj").click(function () {
        if ($('#atuo_sxj').is(':checked')) {
            var ii = layer.load(2, {shade: [0.1, '#fff']});
            $.ajax({
                type: "GET",
                url: "../guanjia_ajax.php?act=change_atuo_sjx&lock=" + 1,
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    location.replace(document.referrer);
                },
                error: function (data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        } else {
            $.ajax({
                type: "GET",
                url: "../guanjia_ajax.php?act=change_atuo_sjx&lock=" + 0,
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    location.replace(document.referrer);
                },
                error: function (data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        }
    })
</script>
</html>