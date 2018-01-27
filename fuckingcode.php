<?php
/**
 * Created by PhpStorm.
 * User: snowjujube
 * Date: 25/01/2018
 * Time: 5:39 PM
 */

    $url = "http://202.200.206.54";
    $headers = get_headers($url, TRUE);
    $url = $headers["Location"];
    $result = array();
    preg_match_all("/(?:\()(.*)(?:\))/i",$url, $result);
    $_SESSION["fuckingcode"] = $result[1][0];