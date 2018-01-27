<?php
/**
 * Created by PhpStorm.
 * User: snowjujube
 * Date: 24/01/2018
 * Time: 10:28 PM
 */

class userinfo
{
    function loginpost($res,$str,$username,$password){
        $post['__VIEWSTATE'] = "$res";
        $post['txtUserName'] = "$username";
        $post['TextBox2'] = "$password";
        $post['TextBox1'] = "$password";
        $post['txtSecretCode'] = "$str";
        $post['lbLanguage'] = '';
        $post['RadioButtonList1'] = iconv('utf-8', 'gb2312', '学生');
        $post['Button1'] = iconv('utf-8', 'gb2312', '登录');
        return $post;
    }

    function scorepost($res){
        $post["__EVENTTARGET"] = "";
        $post["__EVENTARGUMENT"] = "";
        $post["__VIEWSTATE"] = "$res";
        $post["hidLanguage"] = "";
        $post["ddlXN"] = "2017-2018";
        $post["ddlXQ"] = "1";
        $post["ddl_kcxz"] = "01";
        $post["btn_zcj"] = iconv('utf-8', 'gb2312', '历年成绩');
        return $post;
    }
}