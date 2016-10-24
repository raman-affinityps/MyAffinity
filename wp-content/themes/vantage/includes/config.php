<?php 
define("SERVICE_ACCOUNT_EMAIL",'applatformdashboard@premium-hybrid-128017.iam.gserviceaccount.com');
define("KEY_FILE_LOCATION",get_template_directory().'/includes/GoogleAnalyticsKey.p12');
define("ANALYTIC_ID","ga:108526417");
define("ANALYTIC_DEFAULT_METRIX",'ga:users');
define("ANALYTIC_DEFAULT_REALTIME_METRIX",'rt:activeUsers');


// DATE FORMATE:- YYYY-MM-DD
$dateep       = explode("&",$_POST["daterange"]);
if($dateep[0]!="")
{
    $ajax     = 1;
    $endDay   = $dateep[1];
    $startDay = $dateep[0];
}
else
{
    $ajax     = 0;
    $endDay   = date('Y-m-d',strtotime("-1 days"));
    $startDay = date('Y-m-d',strtotime("-30 days"));	
}

define("ANALYTIC_DEFAULT_START_DATE", $startDay);
define("ANALYTIC_DEFAULT_END_DATE", $endDay);
require_once get_template_directory() . '/includes/table-file.php';
require_once get_template_directory() . '/includes/data-file.php';
?>