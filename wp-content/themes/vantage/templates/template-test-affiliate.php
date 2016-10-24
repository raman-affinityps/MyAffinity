<?php
/**
 * This template displays full width pages.
 *
 * @package vantage
 * @since vantage 1.0
 * @license GPL 2.0
 * 
 * Template Name: Affiliate Test Dashboard
 */

get_header("dashboard");

$user = wp_get_current_user();
?>

<style type="text/css">
#table_1_wrapper,
#table_3_wrapper,
#table_5_wrapper,
#table_10_wrapper,
#table_11_wrapper,
#table_12_wrapper,
#table_14_wrapper,
#table_16_wrapper,
#table_18_wrapper
 {
    overflow:hidden;
    height: 0 !important;
}
.dataTables_filter, .dataTables_info { display: none; }
header#masthead, .site-footer {
    display: none;
}
body.responsive.layout-full #page-wrapper .full-container {
    max-width: 1240px;
    width: 90%;
}
#leads_tab-content {
    padding: 0;
    border: 0 !important;
}
</style>

<script>
jQuery(function () {
    jQuery("#e1").daterangepicker({
        datepickerOptions : {
            numberOfMonths : 2
        }
    })
    jQuery("#e1").data('comiseoDaterangepicker').setRange({
        start: new Date('<?php echo date('m/d/Y', strtotime($_SESSION['apdb_startdate']));?>'),
        end: new Date('<?php echo date('m/d/Y', strtotime($_SESSION['apdb_enddate']));?>')
    });
});
</script>

    <div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">
            <div id="tab_container">
            <ul id="myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
                <li class="active" role="presentation">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_1" href="#tabs_desc_1" aria-expanded="true"><i class="fa fa-globe"></i> <span>Site Stats</span></a>
                </li>
                <li role="presentation" class="">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_2" href="#tabs_desc_2" aria-expanded="false"><i class="fa fa-columns"></i><span>Leads</span></a>
                </li>
                <li role="presentation" class="">
                    <a data-toggle="tab" role="tab" aria-controls="tabs_desc_3" href="#tabs_desc_3" aria-expanded="false"><i class="fa fa-phone"></i><span>Call Center</span></a>
                </li>
                <input id="e1" name="e1">                
            </ul>

            <div id="tab-content" class="tab-content">
                <div id="tabs_desc_1" class="tab-pane active animated fadeIn" role="tabpanel">
                     <script type="text/javascript">
                        function iframeLoaded_QTHXcQshMO() {
                            var iFrameID = document.getElementById('iframe_QTHXcQshMO');
                            if(iFrameID) {

                                iFrameID.height = "";
                                iFrameID.contentWindow.postMessage("setheight","http://www.embeddedanalytics.com");
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

                <div id="tabs_desc_2" class="tab-pane animated" role="tabpanel">

                    <div id="leads_tab_container">
                        <ul id="leads_myTab" role="tablist" class="wpsm_nav wpsm_nav-tabs">
                            <li class="active" role="presentation">
                                <a data-toggle="tab" role="tab" aria-controls="leads_tabs_desc_1" href="#leads_tabs_desc_1" aria-expanded="true"><i class="fa fa-bar-chart"></i> <span>Leads By...</span></a>
                            </li>
                            <li role="presentation" class="">
                                <a data-toggle="tab" role="tab" aria-controls="leads_tabs_desc_2" href="#leads_tabs_desc_2" aria-expanded="false"><i class="fa fa-bar-chart"></i><span>Lead Stats</span></a>
                            </li>
                        </ul>
                        <div id="leads_tab-content" class="tab-content" style="padding:0!important;border:0!important">
                            <div id="leads_tabs_desc_1" class="tab-pane active animated fadeIn" role="tabpanel">
<?php 
        echo do_shortcode("[wpdatatable id=1 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=3 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

        echo do_shortcode("[wpdatatable id=13 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=15 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

        echo do_shortcode("[wpdatatable id=14 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=16 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
?>
                            </div>
                            <div id="leads_tabs_desc_2" class="tab-pane animated fadeIn" role="tabpanel"><br><br>
<?php 

        echo do_shortcode("[wpdatatable id=15]");

        echo do_shortcode("[wpdatatable id=16]");

        echo do_shortcode("[wpdatatable id=17]");

        echo do_shortcode("[wpdatatable id=7 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=6 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");


        echo do_shortcode("[wpdatatable id=8 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
        echo do_shortcode("[wpdatachart id=13 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tabs_desc_3" class="tab-pane animated" role="tabpanel">
<?php
      echo do_shortcode("[wpdatatable id=10 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=9 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

      echo do_shortcode("[wpdatatable id=11 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=11 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");

      echo do_shortcode("[wpdatatable id=12 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
      echo do_shortcode("[wpdatachart id=12 var1='" . @$_SESSION['apdb_startdate'] . "' var2='" . @$_SESSION['apdb_enddate'] . "']");
?>
                </div>
            </div>

        </div>
<script>
jQuery(function () {
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

<?php get_footer('dashboard'); ?>
