<?php
/**
 * Created by KingLee.
 * QQ: 1776885812
 * Date: 2018/4/7
 * Time: 16:37
 */

include("../includes/common.php");
$title = '代刷管家';
//监控密匙
$key_c = "654123";
$cron_key = $_GET['key'];


$rs = $DB->query("SELECT * FROM `shua_guanjia` AS a WHERE tid = 0");
while ($res = $DB->fetch($rs)) {
    $last_cron = $res['date'];
}

if ($cron_key . ob_get_length() == 0) {

} else if ($cron_key != $key_c) {
    exit("代刷管家监控密钥不正确");
} else if ($cron_key == $key_c) {
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


//    检测有无shua_guanjia表
    $sql = "SELECT * FROM shua_guanjia";
    if ($DB->query($sql)) {

    } else {
        $sql = 'CREATE TABLE IF NOT EXISTS `shua_guanjia` (
  `tid` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `cost2` decimal(10,2) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;';
        if ($DB->query($sql)) {
            $sql = "ALTER TABLE `shua_guanjia`
  ADD PRIMARY KEY (`tid`);";
            if ($DB->query($sql)) {
                echo "检测到初次使用代刷管家，已成功导入shua_guanjia表<br>";
            } else {
                echo "导入shua_guanjia表失败<br>";
            }
        } else {
            echo "导入shua_guanjia表失败<br>";
        }
    }

    $count_tools = $DB->count("SELECT MAX(tid) from shua_tools");
    $count_guanjia = $DB->count("SELECT MAX(tid) from shua_guanjia");

    //检测有无新商品上架
    if ($count_tools > $count_guanjia) {
        $sql = "INSERT INTO `shua_guanjia` (`tid`, `price`, `cost`, `cost2`, `status`) VALUES(0, NULL, NULL, NULL, 0);";
        if ($DB->query($sql)) {
            $sql = "INSERT INTO `shua_guanjia` (`tid`, `price`, `cost`, `cost2`, `status`) VALUES";
            for ($i = $count_guanjia + 1; $i <= $count_tools; $i++) {
                $sql = $sql . "(" . $i . ", NULL, NULL, NULL, 0)";
                if ($i == $count_tools) {
                    $sql = $sql . ";";
                } else {
                    $sql = $sql . ",";
                }
            }
            if ($DB->query($sql)) {
                $guanjia_new = "检测有新增商品，已成功更新，请再次刷新监控地址<br>";
            } else {
                $guanjia_new = "检测有新增商品，更新失败<br>";
            }
        } else {
            $guanjia_new = "检测有新增商品，更新失败<br>";
        }
        echo exit($guanjia_new);
    }

    exit("<script src=\"//lib.baomitu.com/jquery/1.12.4/jquery.min.js\"></script>
<a>第</a><span id='load_1'>0</span><a>个/总" . $count_tools . "个，进行中....</a><br>
<span id=\"load_2\" style=\"color:forestgreen\">如果卡着不动了，请检查相应社区是否可以正常打开</span><br><br><span style=\"color:darkblue\">代刷管家 - 在线版</span><br>
<span style=\"color:darkblue\">作者:<a href=\"\">KING</a> &nbsp;&nbsp; 数据赞助：<a href=\"\">小学生</a></span><br>
<script>
    // var i = 1;
    // setInterval(function () {
    //     $(\"#load_1\").text(i++);
    // }, 1000);

    var sign = 1;

    function setguantime() {
      $.ajax({
            type: \"GET\",
            url: \"../ajax.php?act=setguantime&star=true\",
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    
                } else {
                    alert(data.msg);
                    return false;
                }
            },
            error: function (data) {
                alert('服务器错误');
                return false;
            }
        });
    }
    
    function setguani() {
        $.ajax({
            type: \"GET\",
            url: \"../ajax.php?act=setguani&tid=\" + sign,
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    $(\"#load_1\").text(++sign);
                    if (sign == " . $count_tools . " || sign == " . $count_tools . ") {
                        setguantime();
                        $(\"#load_2\").text(\"已完成所有商品的设置\");
                        return false;
                    }
                    setguani()
                } else {
                    alert(data.msg);
                    return false;
                }
            },
            error: function (data) {
                alert('服务器错误');
                return false;
            }
        });
    }

    setguani();
</script>");
}

include './head.php';

$rs = $DB->query("SELECT * FROM shua_class WHERE active=1 order by sort asc");
$select = '<option value="0">请选择分类</option>';
$shua_class[0] = '默认分类';
while ($res = $DB->fetch($rs)) {
    $shua_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
}

$select2 = '<option value="0">请选择商品</option>';

?>

<link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">
<script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>
<div class="container" style="padding-top:100px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
        <div class="panel panel-primary">
            <div class="panel-heading"><h3 class="panel-title">代刷管家</h3></div>
            <div class="panel-body">
                <div class="alert alert-info">
                    此版本代挂管家不需要任何挂机宝，只需要在此页面上做好了相关设置，然后在<a href="">阿里云监控</a>/<a href="">360监控挂</a>上本页即可完成自动更新。<br>
                    你的监控地址为 <a target="_blank"
                               href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/admin/guanjia.php?key=<?php echo $key_c ?>">http://<?php echo $_SERVER['SERVER_NAME'] ?>
                        /admin/guanjia.php?key=<?php echo $key_c ?></a><br>
                    <a class="btn btn-info btn-xs">修改监控密匙请在本页面php内容里修改</a>
                    <a class="btn btn-danger btn-xs">开始监控之前请设置好所有商品的值！</a><br>
                    上次监控时间：<?php echo $last_cron ?>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">选择分类</div>
                        <select name="cid" id="cid" class="form-control"><?php echo $select ?></select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">选择商品</div>
                        <select name="tid" id="tid" class="form-control"><?php echo $select2 ?></select>
                    </div>
                </div>
                <div id="result_1" style="display: none">
                    <div class="form-group">
                        <h4>您选择的商品：
                        </h4>
                    </div>
                    <table class="table table-bordered" border="2">
                        <tbody>
                        <tr height="25">
                            <td class="col-xs-4 col-sm-4" align="center"> 成本价格为：</td>
                            <td class="col-xs-8 col-sm-8" align="center"> <span
                                        id="now_cb">0.04</span></td>
                        </tr>
                        <tr height="25">
                            <td class="col-xs-4 col-sm-4" align="center"> 专业价格：</td>
                            <td class="col-xs-8 col-sm-8" align="center"> <span
                                        id="price_zy">0.1</span>
                            </td>
                        </tr>
                        <tr height="25">
                            <td class="col-xs-4 col-sm-4" align="center"> 普及价格：</td>
                            <td class="col-xs-8 col-sm-8" align="center"> <span
                                        id="price_pj">0.2</span></td>
                        </tr>
                        <tr height="25">
                            <td class="col-xs-4 col-sm-4" align="center"> 用户价格：</td>
                            <td class="col-xs-8 col-sm-8" align="center"> <span
                                        id="price_yh">0.5</span></td>
                        </tr>
                        <tr height="25">
                            <td class="col-xs-4 col-sm-4" align="center"> 来自社区：</td>
                            <td class="col-xs-8 col-sm-8" align="center"> <span
                                        id="now_shequ">****</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <div class="masthead">
                        <nav>
                            <ul class="nav nav-justified">
                                <li style="background: #EEEEEE;"><a href="#">单商品利润设置</a></li>
                                <li><a href="#">全局利润设置</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div id="dan">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">赚取专业版分站</div>
                            <input type="text" id="input_zy" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">赚取普及版分站</div>
                            <input type="text" id="input_pj" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">赚取用户</div>
                            <input type="text" id="input_yh" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" id="bt_submit" name="submit" value="保存" class="btn btn-primary form-control">
                </div>
            </div>
        </div>
    </div>
</div>
</body>


<script src="//lib.baomitu.com/layer/2.3/layer.js"></script>
<script>
    $("#cid").change(function () {
        var cid = $(this).val();
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        $("#tid").empty();
        $("#tid").append('<option value="0">请选择商品</option>');
        $.ajax({
            type: "GET",
            url: "../ajax.php?act=gettool&cid=" + cid,
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#tid").append('<option value="' + res.tid + '">' + res.name + '</option>');
                        num++;
                    });
                    $("#tid").val(0);
                    if (num == 0 && cid != 0) layer.alert('该分类下没有商品');
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });
    window.onload = $("#cid").change();
</script>
<script>
    function shequ_name_get($shequ_id) {
        switch ($shequ_id) {
            case '0':
                $shequ_name = "玖伍系统(点数下单)";
                break;
            case '1':
                $shequ_name = "亿乐系统";
                break;
            case '2':
                $shequ_name = "玖伍系统(余额下单)";
                break;
            case '3':
                $shequ_name = "星墨系统(点数下单)";
                break;
            case '4':
                $shequ_name = "九流社区";
                break;
            case '5':
                $shequ_name = "星墨系统(余额下单)";
                break;
            case '6':
                $shequ_name = "卡易信";
                break;
            case '7':
                $shequ_name = "卡乐购";
                break;
            case '8':
                $shequ_name = "卡慧卡";
                break;
            case '9':
                $shequ_name = "卡商网";
                break;
            case '10':
                $shequ_name = "QQbug社区";
                break;
            default:
                break;
        }
        return $shequ_name;
    }

    function setTable() {
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        var options = $("#tid option:selected");
        var tid = options.val();
        $.ajax({
            type: "GET",
            url: "../ajax.php?act=getguanjia&tid=" + tid,
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var num = 0;
                    // alert(data.data);

                    $.each(data.data, function (i, res) {
                        $("#now_cb").text('' + res.chengben + '');
                        $("#price_zy").text('' + res.cost2 + '');
                        $("#price_pj").text('' + res.cost + '');
                        $("#price_yh").text('' + res.price + '');
                        $("#now_shequ").text('' + shequ_name_get(res.shequ) + '');
                        if (res.cost2_guanjia == null) {
                            $("#input_zy").val('');
                        } else {
                            $("#input_zy").val('' + res.cost2_guanjia + '');
                        }
                        if (res.cost_guanjia == null) {
                            $("#input_pj").val('');
                        } else {
                            $("#input_pj").val('' + res.cost_guanjia + '');
                        }
                        if (res.price_guanjia == null) {
                            $("#input_yh").val('');
                        } else {
                            $("#input_yh").val('' + res.price_guanjia + '');
                        }
                        num++;
                    });
                    $("#result_1").slideDown();
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    }

    var history_hit = 0;
    $("#tid").click(function () {
        var options = $("#tid option:selected");
        if (options.val() == history_hit) {
            return false;
        }
        history_hit = options.val();
        if (options.val() == 0 || options.val() == "0" || options.val() === 0 || options.val() === "0") {
            $("#result_1").hide();
        } else {
            setTable()
        }
    });
    $("#bt_submit").click(function () {
        var options = $("#tid option:selected");
        var tid = options.val();
        var price = $("#input_yh").val();
        var cost = $("#input_pj").val();
        var cost_2 = $("#input_zy").val();
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        $.ajax({
            type: "GET",
            url: "../ajax.php?act=setguanjia&tid=" + tid + "&price=" + price + "&cost=" + cost + "&cost_2=" + cost_2,
            dataType: 'json',
            success: function (data) {
                layer.close(ii);
                if (data.code == 1) {
                    alert("设置成功，下次监控到监控地址时生效")
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    })
</script>
</html>
