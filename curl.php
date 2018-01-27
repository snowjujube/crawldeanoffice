<?php
/**
 * Created by PhpStorm.
 * User: snowjujube
 * Date: 24/01/2018
 * Time: 8:49 PM
 */

class curl
{
    public function curl_request($url,$post='',$referer=''){
        $curl = curl_init();
            //初始化curl
        curl_setopt($curl,CURLOPT_URL,$url);
            //设置CURLOPT_URL
        curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER["HTTP_USER_AGENT"]);
            //设置user agent，设置为浏览器默认的user agent
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,0);
            //设置默认允许重定向
        curl_setopt($curl,CURLOPT_AUTOREFERER,1);
            //当返回的信息头含有转向信息时,自动设置前向连接
        curl_setopt($curl,CURLOPT_REFERER,"http://202.200.206.54/xs_main.aspx?xh=".$referer);
            //设置referer
        if ($post){
            curl_setopt($curl,CURLOPT_POST,1);
            //如果有Post数据，则开启Post
            curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($post));
            //设置查询Post数据
        }
        curl_setopt($curl,CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //以文件流形式返回而不是直接输出
        $data = curl_exec($curl);
        //执行查询
        if (curl_errno($curl)) {
            return curl_error($curl);
            //如果有错返回错误并终止操作
        }
        curl_close($curl);
        return $data;
    }

    public function viewStage($inputurl,$result){
        $url = $inputurl;
        $pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*?)" \/>/is';
        preg_match_all($pattern, $result, $matches);
        $res = $matches[1][0];
         // $pattern = '/<input type="hidden" name="__VIEWSTATEGENERATOR" value="(.*?)" \/>/is';
        //  preg_match_all($pattern, $result, $matches);
       // $res[1] = $matches[1][0];
        return $res;
    }

}