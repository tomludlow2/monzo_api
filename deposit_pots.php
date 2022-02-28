<?php

	//This function needs to :
	/*
	- Accept $_REQUEST data with pot_id and amount
	- It will then lookup the pot and verify
	- Get the pot name etc
	- Generate a dedupe id 
	- Perform the transfer
	- Write back with success and details etc

	- This function simply performs the transfer
	*/

	//Get connected
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$pots = json_decode(get_data($conn, "pots_list"), true);

	$op = [];	
	$op['function'] = "deposit_pot";
	$pass = 0;
	if( isset($_REQUEST['pot_id']) ) {
		$pot = $_REQUEST['pot_id'];
		
		foreach ($pots as $p) {
			if( $p['pot_id'] == $pot) {
				$pass = 1;
				$op['pot_id'] = $pot;
				$op['pot_name'] = $p['pot_name'];
				$op['pot_type'] = $p['pot_type'];
				break;
			}
		}
		if( $pass == 1 ) {
			if(isset($_REQUEST['amount'])) {

				if( is_numeric($_REQUEST['amount']) ) {
					$pass = 2;
					$op['raw_amount'] = $_REQUEST['amount'];
					$op['amount'] = intval($_REQUEST['amount']);
					$amount = intval($_REQUEST['amount']);
				}else {
					$op['error'] = "invalid amount given";
					$op['raw_amount'] = $_REQUEST['amount'];
					$op['amount'] = intval($_REQUEST['amount']);
				}
			}else {
				$op['error'] = "no amount given";
			}
		}else{
			$op['error'] = "invalid pot id";
			$op['pot_id'] = $_REQUEST['pot_id'];
		}


		
	}else{
		$op['error'] = "no pot id given";
	}

	if( $pass == 2 ) {
		//Ready to make the transfer
		//$amount, $pot_id, $auth, $account_id
		//Generate dedupe_id
		$dedupe_id = md5(mktime());
		$authorisation = "Authorization: Bearer $access_token";

		$url = "https://api.monzo.com/pots/$pot/deposit/";
		$data = Array(
			"source_account_id"=> $account_id,
			"amount"=> $amount,
			"dedupe_id"=> $dedupe_id);
		$curl_data = http_build_query($data, '', '&');
		$data_json = json_encode($data);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_data);

		$headers = array(
	   		"Accept: application/json",
	   		'Content-Type:application/x-www-form-urlencoded',
	   		$authorisation,
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($curl);
		$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		$resp = json_decode($response, true);
		curl_close($curl);

		$op['response'] = $resp;
		$op['new_balance'] = $resp['balance'];	
	}
	
	echo(json_encode($op, JSON_PRETTY_PRINT));
?>