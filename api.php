<?php
/**
 * Created by PhpStorm.
 * User: 57677
 * Date: 2018/8/12
 * Time: 15:55
 */
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


