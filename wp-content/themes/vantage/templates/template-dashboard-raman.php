<?php
/**
 * This template displays full width pages.
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 * 
 * Template Name: Raman Dashboard Page
 */

$dbc = array(
    'host'  => 'cliverates-1.cluster-c5zqp8l8xdvl.us-west-2.rds.amazonaws.com',
        'user'  => 'cliverates',
        'pass'  => 'Vnl6DenI',
        'name'  => 'cliverates'
);

if ( !$mysqli ) {
    $mysqli = new mysqli($dbc['host'], $dbc['user'], $dbc['pass'], $dbc['name']);
    if ( $mysqli->connect_error ) {
        die('Connect error (' . $mysqli->connect_error . ')');
    }
}

$user = wp_get_current_user();
$start_time = (isset($_SESSION['apdb_startdate']) ? $_SESSION['apdb_startdate'] : date('Y-m-d', strtotime('2016/03/01')));
$end_time = (isset($_SESSION['apdb_enddate']) ? $_SESSION['apdb_enddate'] : date('Y-m-d', strtotime('2016/08/31')));

$_SESSION['apdb_startdate'] = $start_time;
$_SESSION['apdb_enddate'] = $end_time;

$lender_id = 0;
$lender_name = get_cimyFieldValue($user->ID, 'LENDER_NAME');

if ( !empty($lender_name) && !is_null($lender_name) ) {
    $lender_map = [
        'First Choice' => 3,
        'Patriot Bank' => 5,
        'Capwest Mortgage' => 7,
        'HomeBridge' => 9,
        'Bank of Internet USA' => 11,
        'Umpqua' => 13,
        'Wyndham Capital Mortgage' => 17,
        'JG Wentworth Home Lending, LLC' => 19,
        'PrimeSource Mortgage' => 21,
        'PennyMac' => 31,
        'Loan Depot' => 35,
        'NBKC' => 39,
        'New American Funding' => 43,
        'Kondaur' => 45,
        'American Financial Network' => 47,
        'RHF Corp' => 49,
        'Sun West Mortgage Company, Inc' => 51,
        'Banc Home Loans' => 53,
        'Bank of Affinity' => 55,
        'RMS Security Lending' => 57,
        'Synergy One Lending' => 59,
        'DiTech Mortgage Corp' => 61,
        'LightStream' => 62,
        'New Penn Financial' => 63,
        'eRates' => 71,
    ];

    $lender_id = $lender_map[$lender_name];
}

if ( $lender_id != 0 ) {
    echo '<script>document.location.href="/lender-dashboard"</script>';
    exit;
}

$ytdquery = "SELECT COUNT(*) as 'FundedLoans',
(SELECT ROUND(AVG(FICO), 0) FROM cliverates.funded f2 
WHERE FICO <> ''
AND date_format(f2.ActualClosedDate, '%Y%m%d')
BETWEEN date_format('" . $start_time . "', '%Y%m%d')
AND date_format('" . $end_time . "', '%Y%m%d') AND LenderID  NOT IN (53,31)) as 'AverageFICO',
ROUND(AVG(f.`Final Loan Amount`), 0) as 'AverageFundedLoanSize'
FROM cliverates.funded f
WHERE date_format(f.ActualClosedDate, '%Y%m%d')
BETWEEN date_format('" . $start_time . "', '%Y%m%d')
AND date_format('" . $end_time . "', '%Y%m%d')
AND LenderID NOT IN (53,31)";

get_header();
?>
<h1 class="header-h1">Mortgage Platform</h1>

<style type="text/css">
.header-h1 
{
    position: absolute;
    top: -100px;
    font-size: 40px;
    right: 0;
}
#table_1_wrapper,
#table_7_wrapper,
#table_9_wrapper
/*#table_10_wrapper,
#table_14_wrapper,
#table_16_wrapper,
#table_18_wrapper,
#table_20_wrapper,
#table_22_wrapper*/
 {
    overflow:hidden;
    height: 0 !important;
}
#tab_container h2 { margin-bottom: 10px; }

.tab-pane h2 {
    margin: 15px;
}
.dataTables_filter, .dataTables_info { display: none; }

.site-footer,
#sticky-container,
.site-navigation.main-navigation
 {
    display: none;
}

body.responsive.layout-full #page-wrapper .full-container {
    max-width: 1300px;
    width: 100%;
}
#leads_tab-content {
    padding: 0;
    border: 0 !important;
}
#main {
    padding: 35px 0;
}

.wpDataTablesWrapper table.wpDataTable {
    margin-left: 0 !important;
    max-width: 1200px !important;
}
.chart-title {
    background-color: #3d3d3d;
    color: #fff;
    width: 1244px;
    padding: 10px;
}



table.wpData_Table {
    color: #333;
    background-color: transparent;
    border-collapse: collapse;
    border-spacing: 0;
    clear: both;
    font-size: inherit;
    margin: 0 auto;
    width: 100%;
    margin-left: 0 !important;
    max-width: 1200px !important;
}

table.wpData_Table thead th {
    background-color: #ffffff;
    border-color: #cccccc !important;
    color: #333333;
}
table.wpData_Table thead th {
    font-weight: bold;
    border-style: solid;
    border-width: 1px 1px 2px !important;
    cursor: pointer;
    padding: 7px 10px !important;
    text-align: left;
}
table.wpData_Table td, table.wpData_Table th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

table.wpData_Table tr.odd td {
    background-color: #f5f5f5;
}
table.wpData_Table td {
    border: 1px solid #e0e0e0 !important;
    padding: 3px 10px;
}
table.wpData_Table td, table.wpData_Table th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#table_13 tbody tr:last-of-type td {
    font-weight: bold;
}
</style>

<script>
jQuery(function () {
    
    jQuery("#e1").daterangepicker({datepickerOptions : {numberOfMonths : 2}});
    jQuery("#e1").data('comiseoDaterangepicker').setRange
    ({
        start: new Date('<?php echo date('m/d/Y', strtotime($start_time));?>'),
        end  : new Date('<?php echo date('m/d/Y', strtotime($end_time));?>')
    });
});
</script>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">
        <div id="tab_container">
            <center><input id="e1" name="e1"> </center>           
            <ul id="myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
               
                <li class="active" role="presentation">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_1" href="#tabs_desc_1" aria-expanded="false"><i class="fa fa-columns"></i><span>Leads</span></a>
                </li>
                <li role="presentation" class="" style="display:none!important">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_2" href="#tabs_desc_2" aria-expanded="false"><i class="fa fa-phone"></i><span>Call Center</span></a>
                </li>
                <li class="" role="">
                    <a data-toggle="tab"  role="tab" ari-acontrols="tabs_desc_3" href="#tabs_desc_3" aria-expanded="true"><i class="fa fa-globe"></i> <span>Site Stats</span></a>
                </li>
                         
            </ul>

            <div id="tab-content" class="tab-content">

               <div id="tabs_desc_1" class="tab-pane animated fadeIn active" role="tabpanel">

                    <div id="leads_tab_container">
                        <ul id="leads_myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
                            <li class="active" role="presentation">
                                <a data-toggle="tab" role="tab" aria-controls="leads_tabs_desc_1" href="#leads_tabs_desc_1" aria-expanded="true"><i class="fa fa-bar-chart"></i> <span>Requests By...</span></a>
                            </li>
                            <li role="presentation" class="">
                                <a data-toggle="tab" role="tab" aria-controls="leads_tabs_desc_2" href="#leads_tabs_desc_2" aria-expanded="false"><i class="fa fa-bar-chart"></i><span>Lead Stats</span></a>
                            </li>
                        </ul>
                        <div id="leads_tab-content" class="tab-content" style="padding:0!important;border:0!important">
                            <div id="leads_tabs_desc_1" class="tab-pane active animated fadeIn" role="tabpanel">
<?php 
        //wpdatatables_filter_mysql_query( $query, $table_id );

        //funded loans
        echo do_shortcode("[wpdatatable id=30 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=27]");

        //loan type
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=24 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=32]");

        //lead count
       // echo '<p><br></p>';
       // echo do_shortcode("[wpdatatable id=52 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
       // echo do_shortcode("[wpdatachart id=36]");
        
        //requests by state
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=33 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");
        echo do_shortcode("[wpdatachart id=30]");

        //requests by hour
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=20 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=21]");

        //average funded loans
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=25 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=23]");        

        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=22 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
?>
                            </div>
                            <div id="leads_tabs_desc_2" class="tab-pane animated fadeIn" role="tabpanel"><br><br>
<?php 
        //year to date
        //echo do_shortcode("[wpdatatable id=35]");

        $ytdo = $mysqli->query($ytdquery);
        $ytdd = $ytdo->fetch_assoc();
?>
        <h2>Year to Date</h2>
        <div>
            <table id="table_144" class="display responsive nowrap data-t wpData_Table dataTable no-footer" role="grid">
                <thead>
                    <tr role="row">
                        <th data-class="expand" class="header sort sorting_disabled Funded Evaluation">Funded Evaluation</th>
                        <th data-class="expand" class="header sort sorting_disabled Total">Total</th>
                    </tr>
                </thead>
                <tbody>
                <tr id="table_144_row_1" role="row" class="odd">
                    <td style="" class="  Funded Evaluation"><span class="responsiveExpander"></span>Funded Loans</td>
                    <td style="" class="  Total"><?php echo number_format($ytdd['FundedLoans']); ?></td>
                </tr>
                <tr id="table_144_row_3" role="row" class="even">
                    <td style="" class="  Funded Evaluation"><span class="responsiveExpander"></span>Average FICO</td>
                    <td style="" class="  Total"><?php echo $ytdd['AverageFICO']; ?></td>
                </tr>
                <tr id="table_144_row_4" role="row" class="odd">
                    <td style="" class="  Funded Evaluation"><span class="responsiveExpander"></span>Average Funded Loan Size</td>
                    <td style="" class="  Total"><?php echo number_format($ytdd['AverageFundedLoanSize']); ?></td>
                </tr>
                </tbody>
            </table><br><br>
        </div>
<?php

        //inform comparison
        echo '<h2>' . date('F', strtotime('-1 month')) . " Mid-Month Informa Comparison</h2>";
        echo do_shortcode("[wpdatatable id=51]");
        echo '<h2>' . date('F', strtotime('-1 month')) . " Month-End Informa Comparison</h2>";
        echo do_shortcode("[wpdatatable id=50]");
        echo '<h2>' . date('F', time()) . " Mid-Month Informa Comparison</h2>";
        echo do_shortcode("[wpdatatable id=49]");
        echo '<h2>' . date('F', time()) . " Month-End Informa Comparison</h2>";
        echo do_shortcode("[wpdatatable id=42]");

        //members new
        echo do_shortcode("[wpdatatable id=47]");

        //members upgraded
        echo do_shortcode("[wpdatatable id=48]");

        echo '<div style="float:left;width:47.5%;margin-right:1.5%;">';

        //overall survey
        echo do_shortcode("[wpdatatable id=44 var1='0']");
        
        echo '</div><div style="float:left;width:46.5%;">';

        //lender survey
        echo do_shortcode("[wpdatatable id=45 var1='" . $lender_id . "']");

        echo '</div><br clear="both">';

        //recommend survey
        echo do_shortcode("[wpdatatable id=46 var1='0']");
        
?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tabs_desc_2" class="tab-pane animated" role="tabpanel">
<?php
      echo do_shortcode("[wpdatatable id=10 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=26 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

      echo do_shortcode("[wpdatatable id=11 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=11 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

      echo do_shortcode("[wpdatatable id=12 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=12 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

?>
                </div>
                <div id="tabs_desc_3" class="tab-pane animated" role="tabpanel">                    
                    <div id="container" style="width:1244px;margin-bottom: 10px"></div>                    
                    <div id="containerlinechartfunnel" style="width:1244px;margin-bottom: 10px"></div>
                    <div id="container1"  style="width:620px;margin-bottom: 10px"></div>
                    <div id="containerDeviceShare"  style="width:622px;float:right;margin-top: -410px;"></div>
                    <div id="containerlinechart" style="width:1244px;margin:0px 0px 10px 0px"></div> 
                    <!--div id="containerlinechartpageviewperminute" style="width:1244px"></div--> 
                    <div id="containerForGeoLocationVisitor" style="width:1244px;margin-bottom: 10px"></div> 
                    <div id="avgSessionDuration" style="width:1244px"></div>
                </div>
            </div>

        </div>
        
    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer('dashboard'); ?>
<script>           
jQuery(function ($) 
{

    var end          = jQuery.parseJSON(jQuery("#e1").val()).end;
    var start        = jQuery.parseJSON(jQuery("#e1").val()).start;
    var daterange    = start+"&"+end;
    var functionname = 1;
    //alert(daterange);
    $("#container").html("<center><img src='/giphy.gif'/></center>");
    $("#containerlinechartfunnel").html("<center><img src='/giphy.gif'/></center>");
    $("#container1").html("<center><img src='/giphy.gif' height='400px' /></center>");
    $("#containerDeviceShare").html("<center><img src='/giphy.gif' height='400px' /></center>");
    $("#containerlinechart").html("<center><img src='/giphy.gif'/></center>");
    $("#containerlinechartpageviewperminute").html("<center><img src='/giphy.gif'/></center>");
    $("#containerForGeoLocationVisitor").html("<center><img src='/giphy.gif'/></center>");
    $("#avgSessionDuration").html("<center><img src='/giphy.gif'/></center>");
    
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success: function(value) 
                 { 

                    $('#container').highcharts(
                    {
                        chart: {
                                    type: 'funnel',
                                    marginRight: 100
                                },
                        title: {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,
                                    text: '<span class="chart-title">Site visit funnel</span>',
                                    x: -50
                                },
                        plotOptions:{
                                        series: {
                                                    dataLabels:{
                                                                    enabled: true,
                                                                    format: '<b>{point.name}</b> ({point.y:,.0f})',
                                                                    softConnector: true
                                                                },
                                                    neckWidth: '30%',
                                                    neckHeight: '20%'
                                                }
                                    }, 
                        legend: {
                                    enabled: false
                                },
                        series: [{
                                        name: 'Unique users',
                                    data: JSON.parse(value)
                                }]
                    });                                                       
                }
    });
    functionname=2;
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                { 
                    var options1 =  {
                                        title: {

                                                    useHTML: true,

                                                    text: '<span class="chart-title">Request,Pricing and Quote</span>'
                                               },
                                        xAxis: {
                                                    categories: []
                                                },
                                        chart:  {
                                                    renderTo: 'containerlinechartfunnel'
                                                },
                                        series: []
                                    };
                    var colors ={'request-lenders':"", 'request-pricing':"#00FF00",'request-quote':'#FF00FF'}                
                    var drawChart = function (data, name) 
                                    {

                                        var newSeriesData = {
                                                                name: name,
                                                                data: data,
                                                                color: colors[name]
                                                            };
                                            // Add the new data to the series array
                                        options1.series.push(newSeriesData);
                                            // Render the chart
                                        var chart = new Highcharts.Chart(options1);
                                    };    ;
                    var chartdata = JSON.parse(value);
                    for (var key in chartdata) 
                    {
                        drawChart(chartdata[key], key);
                    }
                }
    });        
    functionname=3;
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                {
                    //console.log(JSON.parse(value));
                    jQuery('#container1').highcharts
                    ({
                        chart : {
                                    plotBackgroundColor: null,
                                    plotBorderWidth: 0,
                                    plotShadow: false
                               },
                        title  : {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,
                                    text: '<br><h3>Pricing Users</h3><br>',
                                    align: 'center',
                                    verticalAlign: 'middle',
                                    y: 40
                                },
                        tooltip: {
                                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                                 },
                        plotOptions:{
                                        pie:{
                                                dataLabels: {
                                                                enabled: true,
                                                                distance: -50,
                                                                style: {
                                                                    fontWeight: 'bold',
                                                                    color: 'white',
                                                                    textShadow: '0px 1px 2px black'
                                                                }
                                                            },
                                                startAngle: -90,
                                                endAngle: 90,
                                                center: ['50%', '75%']
                                            }
                                    },
                        series: [{
                                    type: 'pie',
                                    name: 'Browser share',
                                    innerSize: '50%',
                                    data: JSON.parse(value)
                                }]
                    });
                }
    }); 
    functionname=4; 
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                {                                                
                    var options1      = {
                                            title: {
                                                        style:  {
                                                                'fontSize': '1em'
                                                               },
                                                        useHTML: true,
                                                        text: '<span class="chart-title">Users & New Users</span>'
                                                   },
                                            xAxis: {
                                                        categories: []
                                                    },
                                            chart:  {
                                                         renderTo: 'containerlinechart'
                                                    },
                                            series: []
                                        };
                    var colors ={'user':"#00FF00",'newuser':'#FF00FF'}                    
                    var drawChart     = function (data, name) 
                                        {

                                            var newSeriesData = {
                                                                    name: name,
                                                                    data: data,
                                                                    color:colors[name]
                                                                };
                                            // Add the new data to the series array
                                            options1.series.push(newSeriesData);
                                            // Render the chart
                                            var chart         = new Highcharts.Chart(options1);
                                        };                                              
                    var chartdata     = JSON.parse(value);
                    for (var key in chartdata) 
                    {
                            drawChart(chartdata[key], key);

                    }                               
                }
    });    
    /*
    functionname=5; 
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                { 
                    $('#containerlinechartpageviewperminute').highcharts(
                    {
                        title:  {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,
                                    text: '<span class="chart-title">Page views Per Minute</span>'
                                },
                        xAxis:  {
                                    categories: []
                                },
                        series: [{
                                    data: JSON.parse(value),
                                    name:'Right'
                                }]
                    });                                               
                }
    });  
    */
    functionname=6; 
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                {

                    //console.log(value);
                    var obj= JSON.parse(value);
                    //console.log(obj['data']);
                    $('#containerForGeoLocationVisitor').highcharts(
                    {
                        chart: {
                                    renderTo: 'containerForGeoLocationVisitor',
                                    type   : 'column'
                                },                               
                        title:  {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,   
                                    text: '<span class="chart-title">Geo Location Visitor</span>'
                                },
                        xAxis:  {
                                    type: 'category',
                                    labels: {
                                                rotation: -45,
                                                style: {
                                                            fontSize: '13px',
                                                            fontFamily: 'Verdana, sans-serif'
                                                       }
                                            }
                                },                
                        yAxis:  {
                                    title: {
                                                text: 'Users',
                                                useHTML: true,
                                                style: {
                                                            rotation: 90
                                                       }
                                            }
                                },
                        series: [{
                                    data: obj['data'],
                                    name:'Visits',
                                    dataLabels: {
                                                    enabled: true,
                                                    rotation: -90,
                                                    color: '#F0F',
                                                    align: 'right',                                                   
                                                    y: -35, // 10 pixels down from the top
                                                    style: {
                                                                fontSize: '11px',
                                                                fontFamily: 'Verdana, sans-serif'
                                                            }
                                                }
                                }]
                        
                    });                                         
                }
    });
    functionname=7; 
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                {  
                    //alert(value);
                    //console.log(value);
                    var obj= JSON.parse(value);
                    //console.log(obj);
                    $('#avgSessionDuration').highcharts(
                    {
                        title:  {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,   
                                    text: '<span class="chart-title">Avg Session Duration</span>'
                                },
                        xAxis:  {
                                    categories: []
                                },
                        series: [{
                                    data: obj['data'],
                                    name:'Avg Session Duration'
                                }]
                    });                                               
                }
    });
    functionname=8;
    jQuery.ajax(
    {
        type   :'POST',
        data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
        url    : "/wp-admin/admin-ajax.php",
        success:function(value) 
                {
                    console.log(JSON.parse(value));
                    //return false;
                    jQuery('#containerDeviceShare').highcharts
                    ({
                        chart : {
                                    plotBackgroundColor: null,
                                    plotBorderWidth: 0,
                                    plotShadow: false
                               },
                        title  : {
                                    style:  {
                                                'fontSize': '1em'
                                            },
                                    useHTML: true,
                                    text: '<br><h3>Quote Users</h3><br>',
                                    align: 'center',
                                    verticalAlign: 'middle',
                                    y: 40
                                },
                        tooltip: {
                                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                                 },
                        plotOptions:{
                                        pie:{
                                                dataLabels: {
                                                                enabled: true,
                                                                distance: -50,
                                                                style: {
                                                                    fontWeight: 'bold',
                                                                    color: 'white',
                                                                    textShadow: '0px 1px 2px black'
                                                                }
                                                            },
                                                startAngle: -90,
                                                endAngle: 90,
                                                center: ['50%', '75%']
                                            }
                                    },
                        series: [{
                                    type: 'pie',
                                    name: 'Browser share',
                                    innerSize: '50%',
                                    data: JSON.parse(value)
                                }]
                    });
                }
    }); 
    jQuery('.comiseo-daterangepicker-buttonpanel .ui-button-text').trigger('click');
    jQuery('#myTab a:first').tab('show');
    jQuery('#leads_myTab a:first').tab('show');
    jQuery("#e1").change(function()
    {
        var end          = jQuery.parseJSON(jQuery("#e1").val()).end;
        var start        = jQuery.parseJSON(jQuery("#e1").val()).start;
        var daterange    = start+"&"+end;
        var functionname = 1;

        $("#container").html("<center><img src='/giphy.gif'/></center>");
        $("#containerlinechartfunnel").html("<center><img src='/giphy.gif'/></center>");
        $("#container1").html("<center><img src='/giphy.gif' height='400px'/></center>");
        $("#containerDeviceShare").html("<center><img src='/giphy.gif' height='400px'/></center>");
        $("#containerlinechart").html("<center><img src='/giphy.gif'/></center>");
        $("#containerlinechartpageviewperminute").html("<center><img src='/giphy.gif'/></center>");
        $("#containerForGeoLocationVisitor").html("<center><img src='/giphy.gif'/></center>");
        $("#avgSessionDuration").html("<center><img src='/giphy.gif'/></center>");
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success: function(value) 
                     { 

                        $('#container').highcharts(
                        {
                            chart: {
                                        type: 'funnel',
                                        marginRight: 100
                                    },
                            title: {                                      
                                        style:  {
                                                'fontSize': '1em'
                                                },
                                        useHTML: true,                                                             
                                        text: '<span class="chart-title">Site visit funnel<span>',
                                        x: -50
                                    },
                            plotOptions:{
                                            series: {
                                                        dataLabels:{
                                                                        enabled: true,
                                                                        format: '<b>{point.name}</b> ({point.y:,.0f})',
                                                                        softConnector: true
                                                                    },
                                                        neckWidth: '30%',
                                                        neckHeight: '20%'
                                                    }
                                        }, 
                            legend: {
                                        enabled: false
                                    },
                            series: [{
                                            name: 'Unique users',
                                        data: JSON.parse(value)
                                    }]
                        });                                                       
                    }
        });
        functionname=2;
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    { 
                        var options1 =  {
                                            title: {
                                                        style:  {
                                                                    'fontSize': '1em'
                                                                },
                                                        useHTML: true,                                                             

                                                        text: '<span class="chart-title">Request,Pricing and Quote</span>'
                                                   },
                                            xAxis: {
                                                        categories: []
                                                    },
                                            chart:  {
                                                        renderTo: 'containerlinechartfunnel'
                                                    },
                                            series: []
                                        };
                        var colors ={'request-lenders':"", 'request-pricing':"#00FF00",'request-quote':'#FF00FF'}                                
                        var drawChart = function (data, name) 
                                        {

                                            var newSeriesData = {
                                                                    name: name,
                                                                    data: data,
                                                                    color:colors[name]
                                                                };
                                                // Add the new data to the series array
                                            options1.series.push(newSeriesData);
                                                // Render the chart
                                            var chart = new Highcharts.Chart(options1);
                                        };    ;
                        var chartdata = JSON.parse(value);
                        for (var key in chartdata) 
                        {
                            drawChart(chartdata[key], key);
                        }
                    }
        });        
        functionname=3;
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    { 
                        jQuery('#container1').highcharts
                        ({
                            chart : {
                                        plotBackgroundColor: null,
                                        plotBorderWidth: 0,
                                        plotShadow: false
                                   },
                            title  : {
                                        text: '<br>Pricing Users<br>',
                                        align: 'center',
                                        verticalAlign: 'middle',
                                        y: 40
                                    },
                            tooltip: {
                                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                                     },
                            plotOptions:{
                                            pie:{
                                                    dataLabels: {
                                                                    enabled: true,
                                                                    distance: -50,
                                                                    style: {
                                                                        fontWeight: 'bold',
                                                                        color: 'white',
                                                                        textShadow: '0px 1px 2px black'
                                                                    }
                                                                },
                                                    startAngle: -90,
                                                    endAngle: 90,
                                                    center: ['50%', '75%']
                                                }
                                        },
                            series: [{
                                        type: 'pie',
                                        name: 'Browser share',
                                        innerSize: '50%',
                                        data: JSON.parse(value)
                                    }]
                        });
                    }
        }); 
        functionname=4; 
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    {                                                
                        var options1      = {
                                                title: {
                                                           style:  {
                                                                    'fontSize': '1em'
                                                                   },
                                                            useHTML: true,   
                                                            text: '<span class="chart-title">Users & New Users</span>'
                                                       },
                                                xAxis: {
                                                            categories: []
                                                        },
                                                chart:  {
                                                             renderTo: 'containerlinechart'
                                                        },
                                                series: []
                                            };
                        var colors        = {'user':"#00FF00",'newuser':'#FF00FF'}                       
                        var drawChart     = function (data, name) 
                                            {

                                                var newSeriesData = {
                                                                        name: name,
                                                                        data: data,
                                                                        color:colors[name]
                                                                    };
                                                // Add the new data to the series array
                                                options1.series.push(newSeriesData);
                                                // Render the chart
                                                var chart         = new Highcharts.Chart(options1);
                                            };                                              
                        var chartdata     = JSON.parse(value);
                        for (var key in chartdata) 
                        {
                                drawChart(chartdata[key], key);

                        }                               
                    }
        });        
        functionname=5; 
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    { 

                        $('#containerlinechartpageviewperminute').highcharts(
                        {
                            title:  {
                                        style:  {
                                                    'fontSize': '1em'
                                                },
                                        useHTML: true,   
                                        text: '<span class="chart-title">Page views Per Minute</span>'
                                    },
                            xAxis:  {
                                        categories: []
                                    },
                            series: [{
                                        data: JSON.parse(value),
                                        name:'Right'
                                    }]
                        });                                               
                    }
        });
        functionname=6; 
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    {

                        //console.log(value);
                        var obj= JSON.parse(value);
                        //console.log(obj['data']);
                        $('#containerForGeoLocationVisitor').highcharts(
                        {
                            chart: {
                                        renderTo: 'containerForGeoLocationVisitor',
                                        type   : 'column'
                                    },                               
                            title:  {
                                        style:  {
                                                    'fontSize': '1em'
                                                },
                                        useHTML: true,   
                                        text: '<span class="chart-title">Geo Location Visitor</span>'
                                    },
                            xAxis:  {
                                        type: 'category',
                                        labels: {
                                                    rotation: -45,
                                                    style: {
                                                                fontSize: '13px',
                                                                fontFamily: 'Verdana, sans-serif'
                                                           }
                                                }
                                    },                
                            yAxis:  {
                                        title: {
                                                    text: 'Users',
                                                    useHTML: true,
                                                    style: {
                                                                rotation: 90
                                                           }
                                                }
                                    },
                            series: [{
                                        data: obj['data'],
                                        name:'Visits',
                                        dataLabels: {
                                                        enabled: true,
                                                        rotation: -90,
                                                        color: '#F0F',
                                                        align: 'right',                                                   
                                                        y: -35, // 10 pixels down from the top
                                                        style: {
                                                                    fontSize: '11px',
                                                                    fontFamily: 'Verdana, sans-serif'
                                                                }
                                                    }
                                    }]

                        });                                         
                    }
        });
        functionname=7; 
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    {  
                        //alert(value);
                        //console.log(value);
                        var obj= JSON.parse(value);
                        //console.log(obj);
                        $('#avgSessionDuration').highcharts(
                        {
                            title:  {
                                        style:  {
                                                    'fontSize': '1em'
                                                },
                                        useHTML: true,   
                                        text: '<span class="chart-title">Avg Session Duration</span>'
                                    },
                            xAxis:  {
                                        categories: []
                                    },
                            series: [{
                                        data: obj['data'],
                                        name:'Avg Session Duration'
                                    }]
                        });                                               
                    }
        });
        functionname=8;
        jQuery.ajax(
        {
            type   :'POST',
            data   :{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
            url    : "/wp-admin/admin-ajax.php",
            success:function(value) 
                    {
                        //console.log(JSON.parse(value));
                        jQuery('#containerDeviceShare').highcharts
                        ({
                            chart : {
                                        plotBackgroundColor: null,
                                        plotBorderWidth: 0,
                                        plotShadow: false
                                   },
                            title  : {
                                        style:  {
                                                    'fontSize': '1em'
                                                },
                                        useHTML: true,
                                        text: '<br><h3>Quote Users</h3><br>',
                                        align: 'center',
                                        verticalAlign: 'middle',
                                        y: 40
                                    },
                            tooltip: {
                                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                                     },
                            plotOptions:{
                                            pie:{
                                                    dataLabels: {
                                                                    enabled: true,
                                                                    distance: -50,
                                                                    style: {
                                                                        fontWeight: 'bold',
                                                                        color: 'white',
                                                                        textShadow: '0px 1px 2px black'
                                                                    }
                                                                },
                                                    startAngle: -90,
                                                    endAngle: 90,
                                                    center: ['50%', '75%']
                                                }
                                        },
                            series: [{
                                        type: 'pie',
                                        name: 'Browser share',
                                        innerSize: '50%',
                                        data: JSON.parse(value)
                                    }]
                        });
                    }
        }); 
    });
});

var b, c, a;
function tabsFadeIn() 
{
    b="fadeIn";
    d(jQuery("#myTab a"), jQuery("#tab-content"));
    d(jQuery("#leads_myTab a"), jQuery("#leads_tab-content"));
}
function d(e,f,g)
{
    e.click(function(i) 
    {
        i.preventDefault();
        jQuery(this).tab("show");
        var h = jQuery(this).data("easein");
        if(c)
        {
            c.removeClass(a);
        }
        if(h)
        {
            f.find("div.active").addClass("animated "+h);a=h;
        }
        else
        {
            if(g)
            {
                f.find("div.active").addClass("animated "+g);a=g;
            }else
            {
                f.find("div.active").addClass("animated "+b);a=b;
            }
        }
        c=f.find("div.active");
    });
}
tabsFadeIn();
</script>      
<script src="https://code.highcharts.com/modules/funnel.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>   
<!--script src="https://code.highcharts.com/mapdata/countries/us/us-all.js"></script-->  
       
        