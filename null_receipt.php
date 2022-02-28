<?php

	$PAGE_TITLE = "Null Receipt";
	
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
			file_name:  null_receipt.php
			function:	overwrite a receipt with null info
			arguments (default first):	
				- receipt_id:  The relevant receipt ID to nullify

		IMPORTANT - this function is in lieue of a working
					delete_receipt.php function
	*/

	//Connect and setup
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	if( isset($_REQUEST['receipt_id'])) {
		$receipt_id = $_REQUEST['receipt_id'];
	}else {
		$op['status'] = 400;
		$op['error'] = "No receipt ID provided";
		die(json_encode($op));
	}
	

	$query = "SELECT * FROM `monzo_receipts` WHERE `receipt_id`='$receipt_id'";
	$res = mysqli_query($conn, $query);

	//Do some checks
	$proceed = true;
	$transaction_id = "";
	if( $res ) {
		if( mysqli_num_rows($res) == 1 ) {
			$op['receipt_found'] = true;
								
			while($r = mysqli_fetch_assoc($res)) {
				$receipt_id = $r['receipt_id'];
				$op['transaction_id'] = $r['transaction_id'];
				$transaction_id = $r['transaction_id'];
				$op['local_db'] = "success";
			}
		}else {
			$receipt_id = null;
			$op['receipt_found'] = false;
			$proceed = false;
			$op['status'] = 400; 
		}
	}else{
		$op['error'] = "first_db_error";
		$op['status'] = 500;
		$op['mysqli'] = mysqli_error($conn);
		die(json_encode($op));
	}

	if( $op['receipt_found'] == true ) {
		$query2 = "SELECT * FROM `monzo_transactions` WHERE `transaction_id` = '$transaction_id'";
		$res2 = mysqli_query($conn, $query2);
		if( $res2 ) {
			$amt = 0;
			while($r = mysqli_fetch_assoc($res2) ) {
				$amt = $r['amount']*-1;
			}
			$op['transaction_amount'] = $amt;
		}else {
			$op['error'] = "Did not have record of the transaction";
			$proceed = false;
			$op['status'] = 500;
		}
	}

	if( $proceed ) {
		//Generate a very simple receipt to overwrite the old receipt
		//Generate a single item:
		$item = Array(
			"amount" => $amt,
			"quantity" => 1,
			"units" => "",
			"tax" => 0,
			"description" => "Transaction Value",
			"currency" => "GBP");

		$tax = Array(
		"description" => "VAT",
		"amount"=> 0,
		"currency"=> "GBP",
		"tax_number"=>"00000000");

		$payment = Array(
			"type" => "card",
			"amount" => $amt,
			"currency" => "GBP"
		);

		$payload = Array(
			"transaction_id" => $transaction_id,
			"external_id" => $receipt_id,
			"total" => $amt,
			"currency" => "GBP",
			"items" => [$item],
			"taxes" => [],
			"payments" => [$payment]
		);

		//Rest of curl parameters
		$authorisation = "Authorization: Bearer $access_token";
		$url = "https://api.monzo.com/transaction-receipts";
		//JSON ENCODE IT - important - different to other requests
		$curl_data = json_encode($payload);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_data);
		$headers = array(
	   		"Accept: application/json",
	   		'Content-Type:application/json',
	   		$authorisation,
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		//Execute it and get the code
		$response = curl_exec($curl);
		$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		curl_close($curl);
	
		if( $op['status'] == 200 ) {
			$op['nullify_receipt'] = nullify_receipt($conn, $receipt_id, json_encode([$item]));
			$op['success'] = "SUCCESS";
		}else {
			//Receipt was not added
			$op['success'] = "FAIL";
			$op['error'] = $response;
		}

	}else {
		$op['proceed'] = false;
		$op['status'] = 500;
		die(json_encode($op));
	}

	echo json_encode($op, JSON_PRETTY_PRINT);
?>