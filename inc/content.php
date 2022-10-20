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
    if (!$is_allow) {
        die("<center>仅限本地使用！需要域名授权请联系邮件：itlu#foxmail.com，详情请浏览：<a href='https://itlu.org/jizhang/' target='_blank'>https://itlu.org/jizhang/</a> </center>");
    }
}

allow_domain();*/
function state_day($start, $end, $proid, $isadmin, $type = 0, $classid = 0)
{
    global $conn;
    if ($start == "") {
        $start = date("Y-m-d");
    }
    if ($end == "") {
        $end = date("Y-m-d");
    }
    $where = "where 1=1 ";
    if ($isadmin == "1") {
    } else {
        if ($proid) {
            $where .= " and proid='$proid' ";
        }
    }
    $where .= " and actime >=" . strtotime($start . " 00:00:00") . " and actime <=" . strtotime($end . " 23:59:59");
    if ($type) {
        $where .= " and zhifu='$type' ";
    }
    if ($classid) {
        $where .= " and acclassid='$classid' ";
    }
    $sql   = "SELECT sum(acmoney) as total FROM " . TABLE . "account " . $where;
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['total']) {
        $money = $row['total'];
    } else {
        $money = "0.00";
    }
    echo $money;
}

function total_count($classid = 0, $year, $proid = 0, $isadmin, $zhifu = 0)
{
    global $conn;
    $where = "where FROM_UNIXTIME(actime,'%Y')='$year'";
    if ($isadmin == "1") {
    } else {
        if ($proid) {
            $where .= " and proid='$proid' ";
        }
    }
    if ($classid) {
        $where .= " and acclassid='$classid' ";
    }
    if ($zhifu) {
        $where .= " and zhifu='$zhifu' ";
    }
    $sql    = "SELECT FROM_UNIXTIME(actime, '%m') AS month,sum(acmoney) AS total FROM " . TABLE . "account " . $where . " GROUP BY month";
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function program_total_count($proid = 0, $zhifu, $isadmin)
{
    global $conn;
    $where = "where zhifu='$zhifu'";
    if ($isadmin == "1") {
    } else {
        if ($proid) {
            $where .= " and proid='$proid'";
        }
    }
    $sql    = "SELECT proid,sum(acmoney) AS total FROM " . TABLE . "account " . $where . " GROUP BY proid";
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function user_first_year()
{
    global $conn;
    global $this_year;
    $sql   = "SELECT actime FROM " . TABLE . "account order by actime limit 1";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['actime']) {
        $user_first_year = date("Y", $row['actime']);
    } else {
        $user_first_year = $this_year;
    }
    return $user_first_year;
}

function show_type($classtype)
{
    global $conn;
    $sql = "select * from " . TABLE . "account_class ";
    if ($classtype) {
        $sql .= "where classtype='$classtype' ";
    }
    $sql    .= "order by classtype asc,classid asc";
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function show_program($uid, $pro_id = 0, $isadmin = "2")
{
    global $conn;
    $sql = "select * from " . TABLE . "program ";
    if ($isadmin == "1") {
        $sql .= "order by orderid desc,proid desc";
    } else if ($pro_id) {
        $sql .= "where proid='$pro_id' order by orderid desc,proid desc";
    } else {
        $sql .= "where userid='$uid' order by orderid desc,proid desc";
    }
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function itlu_page_search($uid, $pagesize = 20, $page = 1, $classid, $starttime = "", $endtime = "", $startmoney = "", $endmoney = "", $proid = "", $bankid = "", $select_sys_all = "")
{
    global $conn;
    if ($select_sys_all == "") {
        $nums = record_num_query($uid, $classid, $starttime, $endtime, $startmoney, $endmoney, $proid, $bankid);
    } else {
        $nums = record_num_query($uid, $classid, $starttime, $endtime, $startmoney, $endmoney, $proid, $bankid, 1);
    }
    $pages = ceil($nums / $pagesize);
    if ($pages < 1) {
        $pages = 1;
    }
    if ($page > $pages) {
        $page = $pages;
    }
    if ($page < 1) {
        $page = 1;
    }
    $kaishi = ($page - 1) * $pagesize;
    $sql    = "SELECT a.*,b.classname FROM " . TABLE . "account as a INNER JOIN " . TABLE . "account_class as b ON b.classid=a.acclassid ";
    if ($classid == "all") {

    } else if ($classid == "pay") {
        $sql .= " and zhifu = 2 ";
    } else if ($classid == "income") {
        $sql .= " and zhifu = 1 ";
    } else {
        $sql .= " and acclassid = '" . $classid . "' ";
    }
    if (!empty($bankid)) {
        $sql .= " and bankid = '" . $bankid . "' ";
    }
    if (!empty($starttime)) {
        $sql .= " and actime >= '" . strtotime($starttime . " 00:00:00") . "' ";
    }
    if (!empty($endtime)) {
        $sql .= " and actime <= '" . strtotime($endtime . " 23:59:59") . "' ";
    }
    if (!empty($startmoney)) {
        $sql .= " and acmoney >= '" . $startmoney . "' ";
    }
    if (!empty($endmoney)) {
        $sql .= " and acmoney <= '" . $endmoney . "' ";
    }
    if (!empty($proid)) {
        $sql .= " and proid = '" . $proid . "' ";
    }
    if ($select_sys_all) {
        $sql .= "where a.acid in (select acid from " . TABLE . "account) order by a.actime desc,a.acid desc limit $kaishi,$pagesize";
    } else {
        $sql .= "where a.userid = '$uid' and a.acid in (select acid from " . TABLE . "account where userid = '$uid') order by a.actime desc,a.acid desc limit $kaishi,$pagesize";
    }
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function itlu_page_query($uid, $pagesize = 20, $page = 1)
{
    global $conn;
    $nums  = record_num_query($uid, "all");
    $pages = ceil($nums / $pagesize);
    if ($pages < 1) {
        $pages = 1;
    }
    if ($page > $pages) {
        $page = $pages;
    }
    if ($page < 1) {
        $page = 1;
    }
    $kaishi = ($page - 1) * $pagesize;
    $sql    = "SELECT a.*,b.classname FROM " . TABLE . "account as a INNER JOIN " . TABLE . "account_class as b ON b.classid=a.acclassid ";
    $sql    .= "where a.userid = '$uid' and ";
    $sql    .= "a.acid in (select acid from " . TABLE . "account where userid = '$uid') order by a.actime desc limit $kaishi,$pagesize";
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function record_num_query($uid, $classid = "", $starttime = "", $endtime = "", $startmoney = "", $endmoney = "", $proid = "", $bankid = "", $select_sys_all = "")
{
    global $conn;
    if ($select_sys_all == "") {
        $sql = "select count(acid) as total from " . TABLE . "account where userid = '$uid'";
    } else {
        $sql = "select count(acid) as total from " . TABLE . "account where 1 = 1";
    }
    if ($classid == "all") {

    } else if ($classid == "pay") {
        $sql .= " and zhifu = 2 ";
    } else if ($classid == "income") {
        $sql .= " and zhifu = 1 ";
    } else {
        $sql .= " and acclassid = '" . $classid . "' ";
    }
    if (!empty($bankid)) {
        $sql .= " and bankid = '" . $bankid . "' ";
    }
    if (!empty($starttime)) {
        $sql .= " and actime >= '" . strtotime($starttime . " 00:00:00") . "' ";
    }
    if (!empty($endtime)) {
        $sql .= " and actime <= '" . strtotime($endtime . " 23:59:59") . "' ";
    }
    if (!empty($startmoney)) {
        $sql .= " and acmoney >= '" . $startmoney . "' ";
    }
    if (!empty($endmoney)) {
        $sql .= " and acmoney <= '" . $endmoney . "' ";
    }
    if (!empty($proid)) {
        $sql .= " and proid = '" . $proid . "' ";
    }
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['total']) {
        $count_num = $row['total'];
    } else {
        $count_num = "0";
    }
    return $count_num;
}

function bankname($bankid, $uid, $defaultname = "默认")
{
    global $conn;
    $sql   = "select bankname from " . TABLE . "bank where userid = '$uid' and bankid='$bankid'";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['bankname']) {
        $bankname = $row['bankname'];
    } else {
        $bankname = $defaultname;
    }
    return $bankname;
}

function programname($proid, $uid, $defaultname = "默认项目")
{
    global $conn;
    $sql   = "select proname from " . TABLE . "program where userid = '$uid' and proid='$proid'";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['proname']) {
        $proname = $row['proname'];
    } else {
        $proname = $defaultname;
    }
    return $proname;
}

function rolename($role_id, $defaultname = "默认项目")
{
    global $conn;
    $sql   = "select role_name from " . TABLE . "sys_role where role_id='$role_id'";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['role_name']) {
        $role_name = $row['role_name'];
    } else {
        $role_name = $defaultname;
    }
    return $role_name;
}

function recordname($uid, $defaultname = "系统账户")
{
    global $conn;
    $sql   = "select username from " . TABLE . "user where uid = '$uid'";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['username']) {
        $username = $row['username'];
    } else {
        $username = $defaultname;
    }
    return $username;
}

function query_once($uid, $id)
{
    global $conn;
    $sql    = "SELECT a.*,b.classname FROM " . TABLE . "account as a INNER JOIN " . TABLE . "account_class as b ON b.classid=a.acclassid ";
    $sql    .= "where a.userid = '$uid' and ";
    $sql    .= "a.acid = '$id'";
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function db_del($table, $db_key = 'id', $key)
{
    global $conn;
    $sql = "delete from " . TABLE . $table . " where " . $db_key . "=" . $key;
    if (mysqli_query($conn, $sql)) {
        $result = 1;
    } else {
        $result = 0;
    }
    return $result;
}

function db_list($dbname, $where, $orderby)
{
    global $conn;
    $sql    = "SELECT * FROM " . TABLE . $dbname . " " . $where . " " . $orderby;
    $query  = mysqli_query($conn, $sql);
    $resArr = [];
    while ($row = mysqli_fetch_array($query)) {
        $resArr[] = $row;
    }
    return $resArr;
}

function db_record_num($table, $where = '', $key = '*')
{
    global $conn;
    $sql   = "select count($key) as total from " . TABLE . "$table $where";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['total']) {
        $count_num = $row['total'];
    } else {
        $count_num = 0;
    }
    return $count_num;
}

function db_one_key($table, $where = '', $key = '*')
{
    global $conn;
    $sql   = "select $key from " . TABLE . "$table $where";
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row[$key]) {
        $key = $row[$key];
    } else {
        $key = 0;
    }
    return $key;
}

function money_int_out($bankid, $money, $zhifu)
{
    global $conn;
    if ($zhifu == "1") {
        $sql = "update " . TABLE . "bank set balancemoney=balancemoney+" . $money . " where bankid=" . $bankid;
    } else if ($zhifu == "2") {
        $sql = "update " . TABLE . "bank set balancemoney=balancemoney-" . $money . " where bankid=" . $bankid;
    }
    $res = mysqli_query($conn, $sql);
}

function count_bank_money($bankid, $start_time, $end_time)
{
    global $conn;
    global $userid;
    $where = "where userid='$userid' and zhifu='2' and bankid='" . $bankid . "' and actime >= '" . strtotime($start_time . " 00:00:00") . "' and actime <= '" . strtotime($end_time . " 23:59:59") . "'";
    $sql   = "SELECT sum(acmoney) AS total FROM " . TABLE . "account " . $where;
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_array($query);
    if ($row['total']) {
        $count_num = $row['total'];
    } else {
        $count_num = "0.00";
    }
    return $count_num;
}

function month_type_count($typeid, $get_year, $proid = 0, $isadmin)
{
    $income_count_data = "";
    $income_count_list = total_count($typeid, $get_year, $proid, $isadmin, 0);
    for ($b = 1; $b <= 12; $b++) {
        $month_income_num = "0";
        foreach ($income_count_list as $countrow) {
            if ($b == $countrow['month']) {
                $month_income_num = $countrow['total'];
                continue;
            }
        }
        $income_count_data .= $month_income_num . ",";
    }
    $income_count_data = substr($income_count_data, 0, -1);
    return $income_count_data;
}

function sys_menu($isadmin, $role_id, $userid, $nowurl)
{
    global $sys_role_menu;
    $menu_show = "";
    if ($isadmin == "1") {
        $menulist_f_0 = db_list("sys_menu", "where m_f_id=0 and m_type=1 ", "order by orderid asc,m_id asc");
    } else if ($role_id > 0) {
        $role_menu    = $sys_role_menu;
        $menulist_f_0 = db_list("sys_menu", "where m_f_id=0 and m_type=1 and m_id in ($role_menu) ", "order by orderid asc,m_id asc");
    }
    foreach ($menulist_f_0 as $row) {
        $actionshow = "";
        if ($row['m_url'] == $nowurl) {
            $actionshow = " class=\"cur\"";
        }
        $menu_show .= "<li><a href=\"" . $row['m_url'] . "\"" . $actionshow . ">" . $row['m_name'] . "</a></li>";
    }
    return $menu_show;
}

function sys_role_check($isadmin, $role_id, $menu_opera_id)
{
    global $sys_role_menu;
    $result = 0;
    if ($isadmin == "1") {
        $result = 1;
    } else if ($role_id > 0) {
        $role_menu = $sys_role_menu;
        if (empty($role_menu)) {
            $result = 0;
        } else if (strpos($role_menu, ",") !== false) {
            $result = 0;
            $arr    = explode(",", $role_menu);
            for ($i = 0; $i < sizeof($arr); $i++) {
                if ($menu_opera_id == $arr[$i]) {
                    $result = 1;
                    break;
                }
            }
        } else {
            if ($menu_opera_id == $role_menu) {
                $result = 1;
            } else {
                $result = 0;
            }
        }
    }
    return $result;
}

?>
