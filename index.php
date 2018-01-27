<?php
/**
 * Created by PhpStorm.
 * User: snowjujube
 * Date: 24/01/2018
 * Time: 10:36 PM
 */
try {
    session_start();

    header("Content-Type:text/html;charset=gb2312");
    include_once "fuckingcode.php";
    require_once "curl.php";
    require_once "userinfo.php";
    require_once "zhengfang/PIN Identify by fangzheng.php";
    require_once "simple_html_dom.php";

    $status = 0;
    $curl = new curl();
    $userinfo = new userinfo();
    $username = $_GET["usr"];
    $password = $_GET["pwd"];




    $login_url = "http://202.200.206.54/({$_SESSION["fuckingcode"]})/Default2.aspx";
    $login_referer = "http://202.200.206.54/xs_main.aspx?xh=$username";
    $login_result = $curl->curl_request($login_url);

    $str = $_SESSION["letteri"];
    $str .= $_SESSION["letterii"];
    $str .= $_SESSION["letteriii"];
    $str .= $_SESSION["letteriv"];

    $res_login = $curl->viewStage($login_url, $login_result);

    $post_login = $userinfo->loginpost($res_login, $str, $username, $password);


    $main = $curl->curl_request($login_url, $post_login, $username);
    $mainhtml = str_get_html($main);
    foreach ($mainhtml->find("a") as $item){
        $name = $item->plaintext;
    }
    if ($name == "here") {
        $status = 1;
        $len = strlen($name[1]) / 2;
        $user = mb_substr($name[1], 0, $len - 2, "GB2312");
        $urlname = urlencode($user);
    }
    $urlname = urlencode(iconv("utf-8", "gb2312", $urlname));
    $info_url = "http://202.200.206.54/({$_SESSION["fuckingcode"]})/xscjcx.aspx?xh=$username&xm=$urlname&gnmkdm=N121623";
    $info_result = $curl->curl_request($info_url);
    $res_info = $curl->viewStage($info_url, $info_result);
    $post_info = $userinfo->scorepost($res_info);
    $info = $curl->curl_request($info_url, $post_info, $login_referer);
    preg_match('/<span id=\"lbl_zymc\">(.*)<\/span>/', $info, $label);
    if ($label == "") {
        $status = 0;
    }
    $html = str_get_html($info);
    foreach ($html->find("table#Datagrid1") as $table) {
        foreach ($table->find("tr") as $k => $tr) {
            $score[$k]['year'] = $tr->find('td', 0)->plaintext;//学年
            $score[$k]['term'] = $tr->find('td', 1)->plaintext;//学期
            $score[$k]['code'] = $tr->find('td', 2)->plaintext;//课程编号
            $score[$k]['name'] = $tr->find('td', 3)->plaintext;//课程名
            $score[$k]['nature'] = $tr->find('td', 4)->plaintext;//课程性质
            $score[$k]['credit'] = $tr->find('td', 6)->plaintext;//学分
            $score[$k]['point'] = $tr->find('td', 7)->plaintext;//绩点
            $score[$k]['first_score'] = $tr->find('td', 8)->plaintext;//成绩
            $score[$k]['second_score'] = $tr->find('td', 10)->plaintext;//补考成绩
            $score[$k]['third_score'] = $tr->find('td', 11)->plaintext;//重修成绩
            $score[$k]['studentid'] = $username;
        }
    }
    unset($score[0]);
}
catch (Exception $e){
    echo $status;
}
echo $status;
?>