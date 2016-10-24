<?php

global $_wp_admin_css_colors;
$theme = get_user_option('admin_color');
$themes = array('fresh', 'light', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise');
$colors = $_wp_admin_css_colors[$theme];
$color = $colors->icon_colors;

if(in_array($theme, $themes)){
	switch($theme){
		case 'fresh':
		
			$bcolor[0] = $colors->colors[1]; //header color
			$bcolor[1] = $colors->colors[0]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = "#111111";          //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = $color['focus'];
		  $fcolor['current'] = $color['current'];
		  
		  break;
		case 'light':
		  $bcolor[0] = $colors->colors[1]; //header color
			$bcolor[1] = $colors->colors[0]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = "#333333";          //list hover color
		 
		  $fcolor['base']    = "#686868";
		  $fcolor['focus']   = "#0074A2";
		  $fcolor['current'] = $color['current'];
		  break;
		case 'blue':
		  $bcolor[0] = $colors->colors[1]; //header color
			$bcolor[1] = $colors->colors[0]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3];          //list hover color
		 
		  $fcolor['base']    = "#E2ECF1";
		  $fcolor['focus']   = "#FFFFFF";
		  $fcolor['current'] = $color['current'];
		  break;
		case 'coffee':
		  $bcolor[0] = $colors->colors[0]; //header color
			$bcolor[1] = $colors->colors[1]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3]; //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = $color['focus'];
		  $fcolor['current'] = $color['current'];
		  break;
		case 'ectoplasm':
		  $bcolor[0] = $colors->colors[0]; //header color
			$bcolor[1] = $colors->colors[1]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3]; //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = $color['focus'];
		  $fcolor['current'] = $color['current'];
		  break;
		case 'midnight':
		  $bcolor[0] = $colors->colors[0]; //header color
			$bcolor[1] = $colors->colors[1]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3]; //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = $color['current'];
		  $fcolor['current'] = $colors->colors[3];
		  break;
		case 'ocean':
		  $bcolor[0] = $colors->colors[0]; //header color
			$bcolor[1] = $colors->colors[1]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3]; //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = $colors->colors[2];
		  $fcolor['current'] = $color['current'];
		  break;
		case 'sunrise':
		 $bcolor[0]  = $colors->colors[0]; //header color
			$bcolor[1] = $colors->colors[1]; //header hover color
			$bcolor[2] = $colors->colors[2]; //list active color
			$bcolor[3] = $colors->colors[3]; //list hover color
		 
		  $fcolor['base']    = $color['base'];
		  $fcolor['focus']   = "#FFFFFF";
		  $fcolor['current'] = $color['current'];
		  break;
		default:
	}
}

?>

/*------------------------ Base font color --------------------------------------*/

#GuiForm #header span,
#formName,
#tools li span,
#tools li span.icon{
color: <?php echo $fcolor['base']; ?>;
}

/*------------------------ Focus font color --------------------------------------*/

#GuiForm #header li:hover,
#GuiForm #header li:hover span,
#formName:hover,
#tools h3.ui-accordion-header:hover,
#tools li:hover span{
color: <?php echo $fcolor['focus']; ?>;
}

/*------------------------ End Focus font color --------------------------------------*/


#GuiForm #header{
background: <?php echo $bcolor[0]; ?>;
border-radius: 0;	
border: 1px solid <?php echo $bcolor[0]; ?>;
}

#GuiForm #header ul {
padding: 0px 10px;
}

#GuiForm #header li:hover{
background-color: <?php echo $bcolor[1]; ?>;
}

#GuiForm #header span{
text-shadow: none;
}

#header .formName-list {
width: 40%;
padding: 5px !important;
}

#formName {
margin: 0px;
text-shadow: inherit;
}

#GuiForm .ui-resizable-e {
border-left: 1px solid <?php echo $bcolor[0]; ?>;
border-right: 1px solid <?php echo $bcolor[0]; ?>;
background-color: <?php echo $bcolor[0]; ?> !important;
}

#container {
margin-top: 45px;
}

#container {
border: 1px solid <?php echo $bcolor[0]; ?>;
border-radius: 0;
box-shadow: none;
margin-left: 200px;
border-top: none;
}

#canvas{
border-radius: 0;
}

#tools {
width: 200px;
margin-top: 50px;
}

#tools h3.ui-accordion-header:hover{
background-color: <?php echo $bcolor[3]; ?>;
}

#tools h3.ui-state-active, 
#tools h3.ui-state-active:hover{
background: none repeat scroll 0 0 <?php echo $bcolor[2]; ?>;
color: <?php echo $fcolor['current']; ?>;
}

#tools h3{
background: <?php echo $bcolor[0]; ?>;
border-radius: 0;
margin: 0;
border: none;
font-weight: normal;
padding: 8px 30px;
font-size: 14px;
text-shadow: none;
}

#tools li {
background: none;
border: none;
}

#tools li span {
font-size: 13px;
line-height: 18px;
text-shadow: none;
font-weight: normal;
}

#tools li span.icon{
left: 20px;
font-size: 16px;
}

#tools .ui-accordion-content {
background: <?php echo $bcolor[1]; ?>;
border-radius: 0;
border: none;
}

#tools ul.ui-accordion-content-active li:last-of-type,
#tools h3.ui-state-default:last-of-type, 
#tools .ui-accordion-content:last-of-type {
border-bottom: medium none;
border-radius: 0;
}

#guiform-notice {
border: 1px solid <?php echo $bcolor[0]; ?>;
border-radius: 0;
color: #333333;
}

#guiform-notice h1{
text-align: center;
}

#guiform-notice p{
text-align: justify;
}