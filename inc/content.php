<?php
define("SiteNameTitle", SiteName . " - 免费版");
/*$Allow_domain = "localhost,127.0.0.1,test.com,www.test.com,www.test1.com";
function allow_domain()
{
    global $Allow_domain;
    $is_allow   = false;
    $servername = strtolower(trim($_SERVER['SERVER_NAME']));
    $arr        = explode(",", $Allow_domain);
    for ($i = 0; $i < sizeof($arr); $i++) {
        if ($servername == $arr[$i]) {
            $is_allow = true;
            break;
        }
    }
