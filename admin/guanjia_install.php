<?php
@header('Content-Type: text/html; charset=UTF-8');
$sign = "0215"; //路径号
//远程下载文件
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
    if (!getFile("http://cdn.dkfirst.cn/dsprotect/" . $sign . "/guanjia.php", '', 'guanjia.php', 1)) {
        exit("guanjia.php:no");
    }
    if (!getFile("http://cdn.dkfirst.cn/guanjia_key.php", '', 'guanjia_key.php', 1)) {
        exit("guanjia_key.php:no");
    }
    if (!getFile("http://cdn.dkfirst.cn/dsprotect/" . $sign . "/guanjia_ajax.php", '', '../guanjia_ajax.php', 1)) {
        exit("guanjia_ajax.php:no");
    }
    if (!getFile("http://cdn.dkfirst.cn/dsprotect/" . $sign . "/head.php", '', 'head.php', 1)) {
        exit("guanjia_head.php:no");
    }
    if (!getFile("http://cdn.dkfirst.cn/dsprotect/" . $sign . "/guanjia_db.php", '', 'guanjia_db.php', 1)) {
        exit("guanjia_db.php:no");
    }
    if (!getFile("http://cdn.dkfirst.cn/dsprotect/" . $sign . "/guanjia_setting.php", '', 'guanjia_setting.php', 1)) {
        exit("guanjia_setting.php:no");
    }
    exit("ok");
} else {
    exit ("<!DOCTYPE html>
<html lang=\"en\" class=\"no-js\">
<head>
    <meta charset=\"UTF-8\"/>
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>代刷管家在线版安装程序</title>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.dkfirst.cn/dsprotect/css/normalize.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.dkfirst.cn/dsprotect/css/font-awesome.min.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.dkfirst.cn/dsprotect/css/demo.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.dkfirst.cn/dsprotect/css/component.css\"/>
    <link rel=\"stylesheet\" type=\"text/css\" href=\"http://cdn.dkfirst.cn/dsprotect/css/custom-bars.css\"/>
</head>
<body style=\"background: #D8DBE4\">
<div class=\"container\">
    <section class=\"content\">
        <!-- <h2>h2</h2> -->
        <article class=\"flexy-grid\">
            <h1>代刷管家VIP版 V2.15</h1>
            <h2 id=\"h2_1\">正在安装.....</h2>
            <input type=\"checkbox\" id=\"bar-2\">
            <div class=\"flexy-column\">
                <div class=\"progress-factor flexy-item\">
                    <div class=\"progress-bar\">
                        <div class=\"bar has-rotation has-colors red heat-gradient move\" id=\"move_label\"
                             role=\"progressbar\" aria-valuenow=\"5\" aria-valuemin=\"0\" aria-valuemax=\"100\">
                            <div class=\"tooltip heat-gradient-tooltip\"></div>
                            <div class=\"bar-face face-position roof percentage\"></div>
                            <div class=\"bar-face face-position back percentage\"></div>
                            <div class=\"bar-face face-position floor percentage volume-lights\"></div>
                            <div class=\"bar-face face-position left\"></div>
                            <div class=\"bar-face face-position right\"></div>
                            <div class=\"bar-face face-position front percentage volume-lights shine\"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--<label class=\"value-label\" for=\"bar-2\">[ aria-valuenow = '90%' ]</label>-->
            <label id=\"h3_1\" style=\"display: none;margin-top: -20px;\" class=\"value-label\"><a href='./guanjia.php'>点击进入</label>
        </article>
    </section>
    <div style=\"position:fixed; height:50px;width:100%;bottom:0px;\">
        <center>代刷管家©感谢以下技术支持</center>
        <center><a href=\"http://www.idcyun.wang\">久梦云</a>，<a href=\"http://zeink.cn/\" >ZEINK</a></center>
    </div>
</div>
<!-- /container -->
<script src=\"https://cdn.bootcss.com/jquery/2.1.1/jquery.min.js\"></script>
<script type=\"text/javascript\" charset=\"utf-8\">
    $(\"#change-color .bar\").hover(function () {
        // $(this).toggleClass('active');
        $(this).find('.front').toggleClass('shine'); 
    });
    // $(\"#change-color .bar\").click(function(){
    //     $(this).toggleClass('sleep');
    // });
    var num = 5;
    var lock = 0;
    var a = setInterval(function () {
        num += 5;

        if (num == 35) {
            $.ajax({
                type: \"get\",
                url: \"./guanjia_install.php?key=1\",
                dataType: \"text\",
                success: function (data) {
                    if (data == \"ok\") {
                    } else {
                        alert(\"安装失败，\" + data);
                        $(\"#h2_1\").html(\"<font color='red'>安装失败</font>\")
                    }

                },
                error: function (data) {
                    if (data == \"ok\") {
                    } else {
                        alert(\"安装失败，\" + data);
                        $(\"#h2_1\").html(\"<font color='red'>安装失败</font>\");
                    }
                }
            });
        }

        $(\"#move_label\").attr(\"aria-valuenow\", num + \"\");
        if (num > 100) {
            $(\"#move_label\").removeClass(\"move\");
            $(\"#move_label\").addClass(\"move1\");
            $(\"#h3_1\").css(\"display\", \"\");
            $(\"#h2_1\").html(\"<font color='green'>安装完成</font>\")
            $(\"#move_label\").attr(\"aria-valuenow\", 100);
            clearInterval(a);
        }
    },400)
</script>
</body>
</html>
");
}
?>