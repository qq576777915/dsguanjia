<?php
/**
 * Created by PhpStorm.
 * User: 57677
 * Date: 2018/8/12
 * Time: 15:55
 */

//error_reporting(E_ALL); ini_set("display_errors", 1);
error_reporting(0);
define('IN_CRONLITE', true);
define('ROOT', dirname(__FILE__) . '/');
define('TEMPLATE_ROOT', ROOT . '/template/');
define('SYS_KEY', 'qianchang');

date_default_timezone_set("PRC");
$date = date("Y-m-d H:i:s");
session_start();

$scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$sitepath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
$siteurl = ($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $sitepath . '/';

require ROOT . 'config.php';

if (!isset($port)) $port = '3306';

//连接数据库
include_once(ROOT . "db.class.php");
$DB = new DB($host, $user, $pwd, $dbname, $port);


include ROOT . 'cache.class.php';

$CACHE = new CACHE();
$confs = $CACHE->pre_fetch();//获取系统配置
$conf = $DB->get_row("SELECT * FROM auth_config WHERE id='1' limit 1");//获取系统配置
$password_hash = '!@#%!s!';
include ROOT . "function.php";

$my = isset($_GET['my']) ? $_GET['my'] : null;

$clientip = $_SERVER['REMOTE_ADDR'];


include 'config.php';

$act = $_GET['act'];


$host = $host;
$userName = $user;
$password = $pwd;
$dbName = $dbname;
$connID = mysql_connect($host, $userName, $password);
mysql_select_db($dbName, $connID);
mysql_query("set names gbk");

switch ($act) {
    case 'add':
        $sign = "0";
        $DB->query("update `auth_api` set cishu=cishu+1 where id = 1");
        exit($sign);
        break;
    case 'get':
        $sign = "0";
        $qq = $_GET['qq'];
        if ($qq != null) {
            $query = mysql_query("SELECT * FROM `auth_site` WHERE uid = '" . $qq . "'");
            while ($result = mysql_fetch_array($query)) {
                $sign = "1";
            }
            $query = mysql_query("SELECT * FROM `auth_user` WHERE dlqq = '" . $qq . "' ");
            while ($result = mysql_fetch_array($query)) {
                $sign = "1";
            }
        } elseif ($qq == null) {
            $query = mysql_query("SELECT cishu FROM `auth_api` WHERE id = 1");
            while ($result = mysql_fetch_array($query)) {
                $result_1['download'] = $result['cishu'];
            }
            $query = mysql_query("SELECT count(*) FROM `auth_site`");
            while ($result = mysql_fetch_array($query)) {
                $result_1['user'] = $result['count(*)'];
            }
            $query = mysql_query("SELECT count(*) FROM `auth_user`");
            while ($result = mysql_fetch_array($query)) {
                $result_1['dl'] = $result['count(*)'];
            }
            exit(json_encode($result_1));
        }
        exit($sign);
        break;
    default:
        exit("null");
        break;
}

mysql_close($connID);
?>


