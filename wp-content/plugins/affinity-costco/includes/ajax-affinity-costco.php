<?php

if ( isset($_REQUEST['action']) ){
	switch($_REQUEST['action']) {
		case 'apcostco_ajax':
			apcostco_ajax_requests();
			break;

		case 'dash_dates':
			ap_dates_ajax();

		case 'get_db_table':
			ap_get_db_table();
	}
}


function apcostco_ajax_requests() {

}

function ap_dates_ajax() {
	if ( isset($_GET['apdb_startdate']) && isset($_GET['apdb_enddate']) ) {
		$_SESSION['apdb_startdate'] = date('Y-m-d', strtotime($_GET['apdb_startdate']));
		$_SESSION['apdb_enddate'] = date('Y-m-d', strtotime($_GET['apdb_enddate']));

		return 1;
	}

	return 0;
}


function ap_get_db_table() {
	$table_id = (int)$_GET['tableid'];
	$apdb_startdate = (isset($_SESSION['apdb_startdate']) ? $_SESSION['apdb_startdate'] : date('Y-m-d', strtotime('-7 days')));
	$apdb_enddate = (isset($_SESSION['apdb_enddate']) ? $_SESSION['apdb_enddate'] : date('Y-m-d', time()));

	echo do_shortcode("[wpdatatable id=" . $table_id . " var1='".$apdb_startdate."' var2='".$apdb_enddate."']");
}