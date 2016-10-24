<?php
 //if ( ! defined( 'ABSPATH' ) ) exit;
class TableFormat 
{

    public function __construct($parameter) {	
		if($parameter==0){
			$this->enqueJavascript();
			$this->mainheader();
		}
    }

    public function enqueJavascript(){
            /*wp_enqueue_script("highcharts", 'HOME_URLwp-content/plugins/consumer-reports/includes/js/highcharts.js');
            wp_enqueue_script("funnel", 'HOME_URLwp-content/plugins/consumer-reports/includes/js/funnel.js');	
            wp_enqueue_script("exporting",'HOME_URLwp-content/plugins/consumer-reports/includes/js/exporting.js');  
      */
    ?>
    <style>

      .highcharts-tooltip + text{
       display: none;
    }
    </style>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/2.3.1/moment.min.js"></script>
    
<!--    <link rel="stylesheet" href="/wp-content/plugins/consumer-reports/includes/js/jquery.comiseo.daterangepicker.css">
    <script type="text/javascript" src="/wp-content/plugins/consumer-reports/includes/js/jquery.comiseo.daterangepicker.js"></script>-->
    
     <script type="text/javascript">    
        jQuery(function() 
        {   jQuery("#e1").daterangepicker
            ({

               onChange: function() 
               {  
                    var end          = jQuery.parseJSON(jQuery("#e1").val()).end;
                    var start        = jQuery.parseJSON(jQuery("#e1").val()).start;
                    var daterange    = start+"&"+end;
                    var functionname = 1;
                    jQuery.ajax({
                            type:'POST',
                            data:{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
                            url: "/wp-admin/admin-ajax.php",
                            success: function(value) { 
                                            var chart = $('#container').highcharts();
                                            chart.series[0].setData(JSON.parse(value), true);
                                    }
                            });
                    functionname=2;
                    jQuery.ajax({
                            type:'POST',
                            data:{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
                            url: "/wp-admin/admin-ajax.php",
                            success: function(value) { 

                                            var chart = $('#containerlinechartfunnel').highcharts();
                                            var chartdata = JSON.parse(value);
                                            var i=0; 
                                                    for (var key in chartdata) {

                                                            //chart.series[0].setData(chartdata[key] , true);
                                                            chart.series[i].setData(chartdata[key] , true);
                                                            i++;
                                                    }
                                    }
                            });  
                    functionname=3;
                    jQuery.ajax({
                            type:'POST',
                            data:{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
                            url: "/wp-admin/admin-ajax.php",
                            success: function(value) { 
                                                    var chart = $('#container1').highcharts();
                                            chart.series[0].setData(JSON.parse(value), true);
                                    }
                            });
                    functionname=4; 
                    jQuery.ajax({
                            type:'POST',
                            data:{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
                            url: "/wp-admin/admin-ajax.php",
                            success: function(value) { 
                                                    var chart = $('#containerlinechart').highcharts();
                                                    var chartdata = JSON.parse(value);
                                                    var i=0; 
                                                    for (var key in chartdata) {

                                                            chart.series[i].setData(chartdata[key] , true);
                                                            i++;
                                                    }

                                    }
                            }); 
                    functionname=5; 
                    jQuery.ajax({
                            type:'POST',
                            data:{action:'analytics_dashboard',daterange:daterange,functionname:functionname},
                            url: "/wp-admin/admin-ajax.php",
                            success: function(value) { 
                                                    var chart = $('#containerlinechartpageviewperminute').highcharts();

                                            chart.series[0].setData(JSON.parse(value) , true);
                                    }
                            });
                }	

            });   
            jQuery("#wpbody text").css("display","none");
        });
    </script>
    

    <?php 
    }
 
    public function mainheader()
    {
        $html='<div class="wrap"><h1> Google Analytic Dashboard</h1>
                <div class="tablenav top">
                <div class="makedo actions bulkactions">

                </div>
                <div class="makedo1 actions">
                        <label for="filter-by-date">Filter From date</label>
                        <input name="rangeA"class="rangeA" id="rangeA" type="text" />
                </div>
                <br class="clear">
        </div></div>';   
        echo $html;
    }
    public function htmlstructure($data,$format,$ajax)
    {				
        switch ($format) 
        {
            case funnel:
                $this->drawfunnel($data,$ajax);
                break;
            case semicircle:
                $this->drawsemicircle($data,$ajax);
                break;
            case linechart:
                $this->drawlinechart($data,$ajax);
                break;
            case linechartfunnel:
                $this->drawlinechartfunnel($data,$ajax);
                break;
            case linchartPagePerMinute:
                $this->drawlinchartPagePerMinute($data,$ajax);
                break;
            default:

        } 		  
    }

    public function drawlinchartPagePerMinute($data,$ajax)
    { 
        $finalDataLineChart=$this->getHighChartJson(array_search($key,$keyDataForFunnel),$data);
        if($ajax==1){
                echo str_replace('"]',"]",str_replace(',"',',',json_encode($data)));
                exit;
        }
        echo'<div id="containerlinechartpageviewperminute" style="min-width: 410px;height: 400px; float:left; width:100%;"></div>';
        ?>			
        <script type="text/javascript">
            jQuery(function () {
            jQuery('#containerlinechartpageviewperminute').highcharts({
                    title: {
                            text: 'Page views Per Minute'
                    },
                    xAxis: {
                            categories: []
                    },
                    series: [ <?php echo $finalDataLineChart;?>]

            }); 
            });
        </script>
    <?php		
    }	
    public function drawlinechartfunnel($data,$ajax)
    { 
        $keyDataForFunnel = array("request-lenders"=>"/#request-lenders", "request-pricing"=>"/#request-pricing","request-quote"=>"/#request-quote");
        $i=0;
        foreach($data as $key => $value)
        {
            if(in_array($value["0"],$keyDataForFunnel))
            { 
                $dataForFunnel[$value[0]][$i][0]=date('Y-m-d', strtotime(' + '.$value[1]." day",strtotime(ANALYTIC_DEFAULT_START_DATE)));
                $dataForFunnel[$value[0]][$i][1]=$value[2];
                $dataForFunnelforajaxdata[$value[0]][$value[1]]=$value[2];
                $i++;
            }
        }


        $dataForLinechart=array();
        foreach($dataForFunnel as $key=>$value)
        {  
            $dataForLinechart[]=$this->getHighChartJson(array_search($key,$keyDataForFunnel),$value);

        }
        $finalDataLineChart=implode(",",$dataForLinechart);

        if($ajax==1)
        {
            $userdata["request-lenders"]=$dataForFunnel["/#request-lenders"];
            $userdata["request-pricing"]=array_values($dataForFunnel["/#request-pricing"]);
            $userdata["request-quote"]=array_values($dataForFunnel["/#request-quote"]);
            echo str_replace(',request-quote',',"request-quote',str_replace(',request-pricing',',"request-pricing',str_replace('"]',']',str_replace(',"',',',json_encode($userdata)))));
            exit;
        } 
        echo'<div id="containerlinechartfunnel" style="min-width: 410px; max-width: 600px; height: 400px; float:left; width:48%;"></div>';
        ?>			
        <script type="text/javascript">
            jQuery(function () 
            {		
                jQuery('#containerlinechartfunnel').highcharts({
                        title: {
                                text: 'Request Princing and Quote'
                        },
                        xAxis: {
                                categories: []
                        },
                        series: [ <?php echo $finalDataLineChart;?>]

                }); 
            });
        </script>
    <?php
    }	
    public function drawlinechart($data,$ajax)
    { 	 
        $usersJson = $this->getHighChartJson('user',$data[0]["rows"]); 
        $newUsersJson = $this->getHighChartJson('newuser',$data[1]["rows"]);		 
        $t =  $usersJson.','.$newUsersJson; 
        if($ajax==1)
        {
            $getArray=$data[0]["rows"];
             $getArray1=$data[1]["rows"];
            foreach($getArray as $key=>$value){
                   $getArray[$key][0]=date('Y-m-d', strtotime(' + '.$getArray[$key][0]." day",strtotime(ANALYTIC_DEFAULT_START_DATE)));
            }	 
            foreach($getArray1 as $key=>$value){
                   $getArray1[$key][0]=date('Y-m-d', strtotime(' + '.$getArray1[$key][0]." day",strtotime(ANALYTIC_DEFAULT_START_DATE)));
            }	
            $userdata["user"]=$getArray;
            $userdata["newuser"]=$getArray1;
            echo  str_replace(',n',',"n',str_replace('"]',']',str_replace(',"',',',json_encode($userdata))));			 
            exit;
        }
        ?>			
        <script type="text/javascript">
            jQuery(function () 
            {
                jQuery('#containerlinechart').highcharts({
                        title: {
                                text: 'Users & New Users'
                        },
                        xAxis: {
                                categories: []
                        },
                        series: [ <?php echo $t;?>]

                });
            });
        </script>
    <?php
        echo'<div id="containerlinechart"></div>';

    }

    public function drawsemicircle($data,$ajax)
    {
        foreach($data as $key => $value)
        {
            $dataForFunnel[]="['".$value[0]."',".$value[1]."]";

        }
        $dataForFunneldata=implode(",",$dataForFunnel);
        if($ajax==1){
                echo str_replace(']"',']',str_replace('"[','[',str_replace("'",'"',json_encode($dataForFunnel))));
                exit;
        }
        echo'<div id="container1" style="min-width: 410px;   height: 400px;float:left; width:100%; 	"></div>';
        ?>
        <script>
            jQuery(function () 
            {
                jQuery('#container1').highcharts
                ({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: 0,
                        plotShadow: false
                    },
                    title: {
                        text: '<br>Device shares<br>',
                        align: 'center',
                        verticalAlign: 'middle',
                        y: 40
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
                        pie: {
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
                        data: [
                           <?php echo $dataForFunneldata; ?>,
                            {
                                name: 'Proprietary or Undetectable',
                                y: 0.2,
                                dataLabels: {
                                    enabled: false
                                }
                            }
                        ]
                    }]
                });
            });
        </script>
    <?php		
    }
    public function drawfunnel($data,$ajax)
    {
        $keyDataForFunnel = array("root"=>"/", "request-lenders"=>"/#request-lenders", "request-pricing"=>"/#request-pricing","request-quote"=>"/#request-quote");
        foreach($data as $key => $value)
        {
            if(in_array($value["0"],$keyDataForFunnel))
            {
                $dataForFunnel[$value[0]]=$value[1];
            }
            else 
            {
                $dataForFunnel["/"]=$dataForFunnel["/"]+$value[1];			
            }
        }		
        arsort($dataForFunnel); 
        $dataForFunnel1=array();
        $dataForLinechart=array();
        foreach($dataForFunnel as $key=>$value)
        {
            $dataForFunnel1[]="['".array_search($key,$keyDataForFunnel)."',".$value."]";
            $dataForFunnel1ajax[]="['".array_search($key,$keyDataForFunnel)."',".$value."]";
        }

        $finaldata = implode(",",$dataForFunnel1);				
        if($ajax==1)
        {
            echo str_replace(']"',']',str_replace('"[','[',str_replace("'",'"',json_encode($dataForFunnel1))));
            exit;
        }
        echo'<div id="container" style="min-width: 410px; max-width: 600px; height: 400px; float:left; width:48%;"></div>';		
    ?>
        <script>
            jQuery(function () 
            { 
                jQuery('#container').highcharts
                ({ 
                    chart: {
                        type: 'funnel',
                        marginRight: 100
                    },
                    title: {
                        text: 'Site visit funnel',
                        x: -50
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b> ({point.y:,.0f})',

                                softConnector: true
                            },
                            neckWidth: '30%',
                            neckHeight: '20%'

                            //-- Other available options
                            // height: pixels or percent
                            // width: pixels or percent
                        }
                    }, 
                    legend: {
                        enabled: false
                    },
                    series: [{
                        name: 'Unique users',
                        data: [
                         <?php echo $finaldata; ?> 
                        ]
                    }]
                });
            });
        </script>
    <?php
    }
	
	
    // Creates and returns the open table tag
    public function openTableTag($colName=array()) 
    {
        return '<table border="1" class="">';
    }  
    // Creates and returns the head section of the table
    public function getHeadSection($colName=array())
    {
        $head ='';
        if(is_array($colName) && count($colName)) 
        {
            $head .= '<thead>';
            foreach ($colName as $key => $value) {
             $head .='<th>'.$value.'</th>';
            }
            $head .= '</thead>';
        }
        return $head;    
    }
    // Creates and returns the body section of the table
    public function getTableBodySection($numRow=0,$colValus=array()) 
    {
        $body ='';
        if(is_array($colValus) && count($colValus)) 
        {
            for($i=0;$i<$numRow;$i++) 
            {      
                $body .= '<tr>';
                foreach ($colValus[$i] as $val) {
                   $body .='<td>'.$val.'</td>';
                }
                $body .= '</tr>';        
            }
        }   
        return $body;
    }
    // Creates and returns the close table tag
    public function closeTableTag() 
    {
        return "</table>";      
    }
 
    public function getHighChartJson($name,$data = array()) 
    {
        if($name=="user")
        {
        $dataJson ='';
        if(is_array($data) && count($data)>0) { // echo "asdasdasdas". $dataJson; die;
            $name = ($name!='')? $name:'Right';
            foreach ($data as $k=>$value) {
                            $key = $value[0];
                            $comma = ((count($data)-1)==($k))?'':",";
                            $dataRcd .= '["'.date('Y-m-d', strtotime(' + '.$key." day",strtotime(ANALYTIC_DEFAULT_START_DATE))).'",'.$value[1].']'.$comma;
            }
            $dataJson = "{data:[".$dataRcd."], name: '".$name."'}";
        }
        return $dataJson;
        }
        elseif($name=="newuser")
        {
            $dataJson ='';
            if(is_array($data) && count($data)>0) 
            { // echo "asdasdasdas". $dataJson; die;
                $name = ($name!='')? $name:'Right';
                foreach ($data as $k=>$value) 
                {
                    $key = $value[0];
                    $comma = ((count($data)-1)==($k))?'':",";
                    $dataRcd .= '["'.date('Y-m-d', strtotime(' + '.$key." day",strtotime(ANALYTIC_DEFAULT_START_DATE))).'",'.$value[1].']'.$comma;
                }
                $dataJson = "{data:[".$dataRcd."], name: '".$name."'}";
            }
            return $dataJson; 	  
        }
        else
        {
            $dataJson ='';
            if(is_array($data) && count($data)>0) 
            { // echo "asdasdasdas". $dataJson; die;
                $name = ($name!='')? $name:'Right';
                foreach ($data as $k=>$value) {
                                $key = $value[0];
                                $comma = ((count($data)-1)==($k))?'':",";
                                $dataRcd .= '["'.$key.'",'.$value[1].']'.$comma;
                }
                $dataJson = "{data:[".$dataRcd."], name: '".$name."'}";
            }
            return $dataJson;
        }
    }
}
?>
