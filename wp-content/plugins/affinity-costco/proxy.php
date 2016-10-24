<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

	$api = $_GET['api'];
	if ( !session_id() ) {
		session_start();
	}

	switch($api) {
		case 'lead':
			$lead = null;
			$leadid = (int)(isset($_GET['leadid']) ? preg_replace('/\D/', '', $_GET['leadid']) : null);


			$dbc = array(
				'host'	=> 'cliverates-1.cluster-c5zqp8l8xdvl.us-west-2.rds.amazonaws.com',
				'user'	=> 'cliverates',
				'pass'	=> 'Vnl6DenI',
				'name'	=> 'cliverates'
			);

			$mysqli = new mysqli($dbc['host'], $dbc['user'], $dbc['pass'], $dbc['name']);
			if ($mysqli->connect_errno) {
			    printf("Connect failed: %s\n", $mysqli->connect_error);
			    exit();
			}

			$query = "SELECT d.first_name as fname,d.last_name as lname FROM `demographic` d
							LEFT JOIN `leadRequest` lr ON lr.demographic_id=d.id
							LEFT JOIN `%s` ph ON ph.request_id=lr.id
							WHERE ph.ap_leadid=" . $leadid;


			if ( !($res = $mysqli->query(sprintf($query, 'pricingHistory'))) || $res->num_rows < 1 ) {
				if ( !($res = $mysqli->query(sprintf($query, 'pricingHistoryShort'))) || $res->num_rows < 1 ) {
					$query = "SELECT First_Name as fname,Last_Name as lname from `boberdoo_leadrequest` WHERE `Unique_Identifier`=" . $leadid;
					if ( ($res = $mysqli->query($query)) && $res->num_rows > 0 ) {
						$lead = $res->fetch_assoc();
						$lead['valid'] = true;
						$_SESSION['lead_id'] = $leadid;						
					}
				} else {
					$lead = $res->fetch_assoc();
					$lead['valid'] = true;
					$_SESSION['lead_id'] = $leadid;

				}
			} else {
				$lead = $res->fetch_assoc();
				$lead['valid'] = true;
				$_SESSION['lead_id'] = $leadid;

			}

			$lead = json_encode($lead);
			header('Content-Type: application/json');
			echo $lead;

			break;


		case 'membership':
			$apikey = 'dd6aae8b-682a-438f-80aa-7076d0708e6c';
			$membership_id = preg_replace('/\D/', '', $_GET['mid']);
			$leadid = (isset($_REQUEST['leadid']) && isset($_SESSION['lead_id']) && $_REQUEST['leadid'] == $_SESSION['lead_id']) ? $_SESSION['lead_id'] : '';
			$lender = (isset($_REQUEST['lender']) ? $_REQUEST['lender'] : (isset($_SESSION['apc_lender_id']) ? $_SESSION['apc_lender_id'] : ''));

			$result = file_get_contents("http://secure.affinityps.com/api/costco/?key=" . $apikey . "&membership_id=" . $membership_id . '&lender=' . urlencode($lender) . '&leadid=' . $leadid);

			if ( ($json = json_decode($result, true)) != null ) {
				$_SESSION['costco_membership'] = $json;

				$minfo = array(
					'id'			=> (isset($json['memberID']) ? $json['memberID'] : $json['CardNumber']),
					'valid'			=> $json['eligible'],
					'executive' 	=> (isset($json['executive']) ? $json['executive'] : ($json['Tier']['Code'] == 'EXEC' ? 1 : 0)),
					'fname'			=> (isset($json['name']) ? $json['name']['fname'] : $json['Member']['FirstName']),
					'lname'			=> (isset($json['name']) ? $json['name']['lname'] : $json['Member']['LastName']),
					'memberType'	=> (isset($json['memberType']) ? $json['memberType'] : $json['MembershipType']['Description'])
				);

				if ( isset($_REQUEST['summary']) && $_REQUEST['summary'] == 0 ) {
					$minfo['data'] = $json;
				}

				$result = json_encode($minfo);

				header('Content-Type: application/json');
			}

			echo $result;
			break;
	}


function getSSLPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

	exit;
?>