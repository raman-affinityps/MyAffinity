<?php
/**
 * This template displays full width pages.
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 * 
 * Template Name: Lender Dashboard Page
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


get_header();
?>
    <h1 class="header-h1">Mortgage Platform</h1>
<?php


$user = wp_get_current_user();
$start_time = (isset($_SESSION['apdb_startdate']) ? $_SESSION['apdb_startdate'] : date('Y-m-d', strtotime('2016/03/01')));
$end_time = (isset($_SESSION['apdb_enddate']) ? $_SESSION['apdb_enddate'] : date('Y-m-d', strtotime('2016/08/31')));

$_SESSION['apdb_startdate'] = $start_time;
$_SESSION['apdb_enddate'] = $end_time;

$lender_id = 0;
$lender_name = get_cimyFieldValue($user->ID, 'LENDER_NAME');

$userdata = get_userdata($user->ID);
$is_admin = (in_array('administrator', $userdata->roles) ? true : false);
$is_lo = (in_array('loan_officer', $userdata->roles) ? true : false);
$lender_select = (isset($_REQUEST['lender_select']) ? $_REQUEST['lender_select'] : null);

$lender_list = [];
$lsql = "SELECT l.id,l.name 
            FROM `lender` l
            LEFT JOIN `platformLender` pl ON l.`id`=pl.`lender_id`
            WHERE l.`status`=1 AND pl.`status`=1 AND pl.`platform_id`=2 ORDER BY l.name";
if ( ($lres = $mysqli->query($lsql)) ) {
    while( ($lrow = $lres->fetch_assoc()) ) {
        $lender_list[$lrow['id']] = $lrow['name'];
    }
}


if ( $is_admin && $lender_select ) {
    $lender_id = $lender_select;
    $lender_name = $lender_list[$lender_select];
}
else if ( !empty($lender_name) && !is_null($lender_name) ) {
    $lender_map = [
        'First Choice' => 3,
        'Patriot Bank' => 5,
        'Capwest Mortgage' => 7,
        'HomeBridge' => 9,
        'Bank of Internet USA' => 11,
        'Umpqua' => 13,
        'Wyndham Capital Mortgage' => 17,
        'JG Wentworth Home Lending, LLC' => 19,
        'JG Wentworth Home Lending' => 19,
        'JG Wentworth Home LendingC' => 19,
        'PrimeSource Mortgage' => 21,
        'PennyMac' => 31,
        'Loan Depot' => 35,
        'NBKC' => 39,
        'New American Funding' => 43,
        'Kondaur' => 45,
        'American Financial Network' => 47,
        'RHF Corp' => 49,
        'Sun West Mortgage Company, Inc' => 51,
        'Sun West Mortgage Company' => 51,
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
AND LenderID =" . $lender_id;

//$start_time = $_SESSION['apdb_startdate'] = '2016-01-01';
//$end_time = $_SESSION['apdb_enddate'] = '2016-07-31';
?>

<style type="text/css">
.header-h1 {
    position: absolute;
    top: -100px;
    font-size: 40px;
    right: 0;
}

#table_1_wrapper,
#table_7_wrapper,
#table_9_wrapper
/*,
#table_9_wrapper,
#table_10_wrapper,
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

article.post, article.page { border-bottom: 0 !important; }

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
h1 {
    padding: 10px 0 10px 0;
}
#leads_tab-content {
    padding: 0;
    border: 0 !important;
}
#post-552 {
    padding-bottom: 0;
    margin-bottom: 0;
}
#main {
    padding: 35px 0;
}
div[data-highcharts-chart]
{
    padding-bottom: 35px;
}

.wpDataTablesWrapper table.wpDataTable {
    margin-left: 0 !important;
    max-width: 1200px !important;
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

#lender_select {
    height: 30px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.125), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    vertical-align: bottom;
    margin-left: 20px;
}

</style>

<script>
var lenderID = <?php echo $lender_id; ?>;
jQuery(function () {
<?php
    if ( !$is_lo ) {
?>
    jQuery("#e1").daterangepicker({
        datepickerOptions : {
            numberOfMonths : 2
        }
    })
    jQuery("#e1").data('comiseoDaterangepicker').setRange({
        start: new Date('<?php echo date('m/d/Y', strtotime($start_time));?>'),
        end: new Date('<?php echo date('m/d/Y', strtotime($end_time));?>')
    });
<?php
    }
?>
});
</script>

<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">

        <!-- <div>
            Choose the platform you would like to review...
            <select>
                <option>Select A Platform</option>
                <option>5Linx</option>
                <option>Best Lenders</option>
                <option>Costco</option>
                <option>Ullico</option>
                <option>USA Reverse</option>
                <option>VA Loan Ranger</option>     
            </select>

        </div> -->

        <?php while ( have_posts() ) : the_post(); ?>

            <?php get_template_part( 'content', 'page' ); ?>

            <?php if ( comments_open() || '0' != get_comments_number() ) : ?>
                <?php comments_template( '', true ); ?>
            <?php endif; ?>

        <?php endwhile; // end of the loop. ?>

        <div id="tab_container">

            <ul id="myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
<?php
    if ( !$is_lo ) {
?>
                <li class="active" role="presentation">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_1" href="#tabs_desc_1" aria-expanded="true"><i class="fa fa-columns"></i><span>Conversion</span>
                    </a>
                </li>
<?php
    }
?>
                <li role="presentation" class="<?php echo ( $is_lo ? 'active' : ''); ?>">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_2" href="#tabs_desc_2" aria-expanded="false"><i class="fa fa-pencil"></i><span>Tools</span>
                    </a>
                </li>
<?php
    if ( !$is_lo ) {
?>
                <li class="" role="">
                <a data-toggle="tab" role="tab" aria-controls="tabs_desc_3" href="#tabs_desc_3" aria-expanded="false"><i class="fa fa-globe"></i> <span>Web Site</span>
                    </a>
                </li>

                <input id="e1" name="e1">
<?php
    }
?>
<?php
    if ( $is_admin ) {
?>
    <select id="lender_select" name="lender_select" class="ui-selectmenu ui-widget ui-state-default ui-corner-all">
    <option value="">-- SELECT LENDER --</option>
<?php
    foreach($lender_list as $llid => $llnm) {
        $is_seld = ($lender_select == $llid) ? ' selected' : '';
        echo '<option value="' . $llid . '"' . $is_seld . '>' . $llnm . '</option>';
    }
?>
    </select>
<?php
    }
?>

                <div style="float:right">
<?php
    if ( $is_admin ) {
?>
                <a href="/affiliate-dashboard/" onclick="document.location.href='/affiliate-dashboard/'">Affiliate Dashboard</a> |
 <?php
    }
?>
                <a href="/logout/" onclick="document.location.href='/logout/'">Log Out</a>
                </div>
            </ul>

            <!-- Tab panes -->
            <div id="tab-content" class="tab-content">
<?php
    if ( !$is_lo ) {
?>
                <div id="tabs_desc_1" class="tab-pane animated active fadeIn" role="tabpanel">
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
                            <div id="leads_tabs_desc_1" class="tab-pane active animated fadeIn" role="tabpanel"><br><br>
                                <?php 
                                    
        //funded loans
        echo do_shortcode("[wpdatatable id=30 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");
        echo do_shortcode("[wpdatachart id=27 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");

        //loan type
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=24 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");
        echo do_shortcode("[wpdatachart id=32 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");
        
        //requests by state
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=55 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='0']");
        echo do_shortcode("[wpdatachart id=37 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='0']");

        //requests by hour
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=20 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='0']");
        echo do_shortcode("[wpdatachart id=21 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='0']");

        //average funded loans
        echo '<p><br></p>';
        echo do_shortcode("[wpdatatable id=25 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']");
        echo do_shortcode("[wpdatachart id=23 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "' var3='" . $lender_id . "']"); 

        // Leads by Lender by Lead Type
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
                    <td style="" class="  Total">$<?php echo number_format($ytdd['AverageFundedLoanSize']); ?></td>
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
        echo do_shortcode("[wpdatatable id=47 var1='" . $lender_id . "']");

        //members upgraded
        echo do_shortcode("[wpdatatable id=48 var1='" . $lender_id . "']");

        echo '<div style="float:left;width:47.5%;margin-right:1.5%;">';

        //overall survey
        echo do_shortcode("[wpdatatable id=44 var1='0']");
        
        echo '</div><div style="float:left;width:46.5%;">';

        //lender survey
        echo do_shortcode("[wpdatatable id=45 var1='" . $lender_id . "']");

        echo '</div><br clear="both">';

        //recommend survey
        echo do_shortcode("[wpdatatable id=46 var1='0']");

        //survey comments
       // echo do_shortcode("[wpdatatable id=54 var3='" . $lender_id . "']");

?>
                            </div>
                        </div>
                    </div>
                </div>
<?php
    }
?>
                <div id="tabs_desc_2" class="<?php echo ( $is_lo ? 'tab-pane animated active fadeIn' : 'tab-pane animated'); ?>" role="tabpanel">
                    <div id="tools_tab_container">
                        <ul id="tools_myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
                            <li class="active" role="presentation">
                                <a data-toggle="tab" role="tab" aria-controls="tools_tabs_desc_1" href="#tools_tabs_desc_1" aria-expanded="true"><i class="fa fa-bar-chart"></i> <span>Membership Validation</span></a>
                            </li>
                            <li role="presentation" class="">
                                <a data-toggle="tab" role="tab" aria-controls="tools_tabs_desc_2" href="#tools_tabs_desc_2" aria-expanded="false"><i class="fa fa-bar-chart"></i><span>Resources</span></a>
                            </li><!-- 
                            <li role="presentation" class="">
                                <a data-toggle="tab" role="tab" aria-controls="tools_tabs_desc_3" href="#tools_tabs_desc_3" aria-expanded="false"><i class="fa fa-bar-chart"></i><span>Documentation</span></a>
                            </li> -->
                        </ul>
                        <div id="tools_tab-content" class="tab-content" style="padding:0!important;border:0!important">
                            <div id="tools_tabs_desc_1" class="tab-pane active animated fadeIn" role="tabpanel"><br><br>

                                <div style="max-width:800px;margin:0 50px">
                                <h2 style="margin-bottom:10px">Membership Validation</h2>
                                <?php 
                                    echo do_shortcode("[ap_costco_form]");
                                ?>
                                </div>
                            </div>
                            <div id="tools_tabs_desc_2" class="tab-pane animated fadeIn" role="tabpanel"><br><br>
                                <ul>
                                    <li style="margin-bottom:8px"><a href="https://leads.affinityps.com/res_partners/brpage.php" target="_blank">Costco Bad Leads</a></li>
                                    <li style="margin-bottom:8px"><a href="https://jira.affinityps.com/servicedesk/customer/portal/13/group/33" target="_blank">Lenders Service Desk</a></li>
                                </ul>
                            </div>
                            <div id="tools_tabs_desc_3" class="tab-pane animated fadeIn" role="tabpanel"><br><br>
                            <!-- RESOURCES HERE -->
                            </div>
                        </div>
                    </div>
                </div>
<?php
    if ( !$is_lo ) {
?>
                <div id="tabs_desc_3" class="tab-pane animated" role="tabpanel">

<script type="text/javascript">
    function iframeLoaded_QTHXcQshMO() {
        var iFrameID = document.getElementById('iframe_QTHXcQshMO');
        if(iFrameID) {

            iFrameID.height = "2160";
            iFrameID.contentWindow.postMessage("setheight","https://www.embeddedanalytics.com");
            //iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + "px";

        }
    }
    function receiveMessage(event)
    {
        var iFrameID = document.getElementById('iframe_QTHXcQshMO');
        if (iFrameID) {
            iFrameID.height = event.data+"px";
        }
    }

    window.addEventListener("message", receiveMessage, false);



</script>

<iframe width="100%" height="2101px" frameborder="0" title="Embed Google Analytics Charts into your Website!" scrolling="yes" type="text/html" src="http://www.embeddedanalytics.com/reports/displayreport?reportcode=QTHXcQshMO&amp;chckcode=gaBfDveE872CcDjC7COMUZ" marginheight="0" marginwidth="0" onload="iframeLoaded_QTHXcQshMO()" id="iframe_QTHXcQshMO"></iframe>
                </div>
<?php
    }
?>
            </div>

        </div>

        <script>
jQuery(function () {
<?php
    if ( !$is_lo ) {
?>
    jQuery('#myTab a:first').tab('show');
    jQuery('#leads_myTab a:first').tab('show');

    jQuery("#e1").change(function(){
        var dateData = JSON.parse(this.value);
        var start = dateData.start;
        var end = dateData.end;
        var daterange=start+"&"+end;

        jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",
            type:'POST',
            data:{
                action:'analytics_dashboard',
                apdb_startdate: start,
                apdb_enddate: end
            },
            success: function(response) {
                document.location.reload();
/*                var result = JSON.parse(response);
                var wpTblIDs = Object.keys(wpDataTables);

                for(var k in wpTblIDs) {
                    var dataFld = jQuery('#' + wpTblIDs[k] + '_desc');
                    var upData = dataFld.val().replace(/wdt_var1=[^&]+&wdt_var2=[^&]+/, 'wdt_var1=' + result.start + '&wdt_var2=' + result.end);
                    dataFld.val(upData);

                    wpDataTables[wpTblIDs[k]].fnDraw(!1);
                }*/
            }
        });
    });

    jQuery('#lender_select').on('change', function() {
        window.location.href = '/lender-dashboard/?lender_select=' + jQuery(this).val();
    });
<?php
    }
?>
});

var b, c, a;
function tabsFadeIn() {
    b="fadeIn";
    d(jQuery("#myTab a"), jQuery("#tab-content"));
    d(jQuery("#leads_myTab a"), jQuery("#leads_tab-content"));
}

function d(e,f,g){
    e.click(function(i) {
        i.preventDefault();
        jQuery(this).tab("show");

        var h=jQuery(this).data("easein");

        if(c){c.removeClass(a);}

        if(h){
            f.find("div.active").addClass("animated "+h);a=h;
        }else{
            if(g){
                f.find("div.active").addClass("animated "+g);a=g;
            }else{
                f.find("div.active").addClass("animated "+b);a=b;
            }
        }

        c=f.find("div.active");
    });
}

tabsFadeIn();
</script>

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_footer(); ?>
