<?php

	$PAGE_TITLE = "Deposit Pots";	
	/*
		=======================================================
		Monzo API & PHP Integration
			-GH:		https://github.com/tomludlow2/monzo_api
			-Monzo:		https://docs.monzo.com/

		Created By:  	Tom Ludlow   tom.m.lud@gmail.com
		Date:			Feb 2022

		Tools / Frameworks / Acknowledgements 
			-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
				(https://getbootstrap.com/docs/5.1/about/license/)
			-jQuery:		MIT License, (C) 2019 JS Foundation 
				(https://jquery.org/license/)
			-Monzo Developer API
		========================================================
			file_name:  deposit_pots.php
			function:	Deposit money from main acc to pots
			arguments (default first):	
				- amount: 	amount in pence
				- pot_id:	the pot ID to send it to
	*/

	//Get connected
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	//Get a list of pots to validate.
	$pots = json_decode(get_data($conn, "pots_list"), true);
	$op = [];	
	$op['function'] = "withdraw_pot";

	//Perform some checks
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
					$op['status'] = 400;
					$op['error'] = "invalid amount given";
					$op['raw_amount'] = $_REQUEST['amount'];
					$op['amount'] = intval($_REQUEST['amount']);
				}
			}else {
				$op['status'] = 400;
				$op['error'] = "no amount given";
			}
		}else{
			$op['status'] = 400;
			$op['error'] = "invalid pot id";
			$op['pot_id'] = $_REQUEST['pot_id'];
		}		
	}else{
		$op['status'] = 400;
		$op['error'] = "no pot id given";
	}

	if( $pass == 2 ) {
		//Ready to make the transfer	
		//Dedupe ID important to prevent repeated attempts at transfer
		//TODO - store/retrieve for importance	
		$dedupe_id = md5(mktime());
		$authorisation = "Authorization: Bearer $access_token";
		$url = "https://api.monzo.com/pots/$pot/withdraw/";
		$data = Array(
			"destination_account_id"=> $account_id,
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