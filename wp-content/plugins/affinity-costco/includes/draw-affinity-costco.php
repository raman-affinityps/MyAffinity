<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);



function affinity_costco_form() {
	global $wpdb;
?>
	<style type="text/css">
	#confirmOpenMarket {
	}
	#confirmOpenMarket input {
		float: left;
		margin-top: 8px;
		margin-left: 4px;
	}
	#confirmOpenMarket p {
		margin-left: 32px;
	}

	#ap_costco_form input[type="text"] {
		width: 300px;
		border: 1px solid rgba(0,0,0,0.5);
	}
	#apc_submit[disabled] {
		background: rgba(0,0,0,0.7);
	}
	.apc-waiting {
		background: url(/wp-content/plugins/affinity-costco/public/images/ajax-loading.gif) no-repeat center center;
		width: 32px;
		height: 38px;
		vertical-align: middle;
		display: none;
	}
	#costco_member_results {
		padding: 15px 5px;
		background: rgba(0,0,0,0.2);
		margin: 15px 0;
	}
	#costco_member_results:empty {
		display: none;
	}
	#costco_member_results div {
		border-bottom: 1px solid rgba(0,0,0,0.2);
		margin-bottom: 10px;
		padding: 5px;
	}
	</style>
	<form id="ap_costco_form">
<?php
	if ( !session_id() ) {
		session_start();
	}

	if ( !isset($_SESSION['apc_lender']) || !isset($_SESSION['apc_lender_id']) ) {
		$c = get_current_user_id();
		$lender = $wpdb->get_var("SELECT `VALUE` FROM `wp_cimy_uef_data` WHERE `USER_ID`=" .(int)$c . " AND `FIELD_ID`=1");

		$lenders = array(
			3	=> 'First Choice',
			7	=> 'Capwest Mortgage',
			9	=> 'HomeBridge',
			11	=> 'Bank of Internet USA',
			13	=> 'Umpqua',
			19	=> 'JG Wentworth Home Lending LLC',
			39	=> 'NBKC Bank',
			51	=> 'Sun West Mortgage Company Inc',
			55	=> 'Bank of Affinity',
			59	=> 'Synergy One Lending',
			61	=> 'DiTech Mortgage Corp',
			62	=> 'LightStream',
			63	=> 'New Penn Financial'
		);

		$lender_id = array_search($lender, $lenders);

		$_SESSION['apc_lender'] = $lender;
		$_SESSION['apc_lender_id'] = $lender_id;
	}

	$c = get_current_user_id();
	$lender = $wpdb->get_var("SELECT `VALUE` FROM `wp_cimy_uef_data` WHERE `USER_ID`=" .(int)$c . " AND `FIELD_ID`=1");

?>
	<label for="leadid" style="font-size:16px">Lead ID <sup><small>Leave Blank for Open Market</small></sup></label><br>
	<input type="text" name="leadid" id="leadid" value="" placeholder="Lead ID" style="font-size:16px;line-height:24px;"><br>
	<div id="leadResult">&nbsp;</div><br>

	<label for="mid" style="font-size:16px">Costco Membership ID</label><br>
	<input type="text" name="mid" id="mid" value="" placeholder="Costco Membership ID" required style="font-size:16px;line-height:24px;"><br>
	<div id="memberResult">&nbsp;</div>
	<br>
	<label>
	<div id="confirmOpenMarket">
		<input type="checkbox" name="apc_iagree" id="apc_iagree" value="1">
		<p>Using an incorrect and invalid "Lead ID" may result in an additional charge. If this is a new loan file without a Mortgage Services Lead ID, please leave the "Lead ID" field blank.</p>
	</div>
	</label>
	<input id="apc_submit" type="submit" value="Validate" disabled><span class="apc-waiting"></span>
	</form>
	<div id="costco_member_results"></div>
<p><br><br><br></p>
<?php
}