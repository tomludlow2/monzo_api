<?php

	$PAGE_TITLE = "Add Receipt";
	/*
		=======================================================
		Monzo API & PHP Integration
			-GH:			https://github.com/tomludlow2/monzo_api
			-Monzo:			https://docs.monzo.com/

		Created By:  	Tom Ludlow   tom.m.lud@gmail.com
		Date:			Feb 2022

		Tools / Frameworks / Acknowledgements 
			-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
				(https://getbootstrap.com/docs/5.1/about/license/)
			-jQuery:		MIT License, (C) 2019 JS Foundation 
				(https://jquery.org/license/)
			-Monzo Developer API
		========================================================
			file_name:  add_receipt.php
			function:	Adds receipt data to a specified transaction
			arguments:	Pass an argument called new_receipt
				- See the GH docs for the correct format of this receipt

		IMPORTANT - this will only work for certain types of transaction
		- TO DO - work out which types and filter
		IMPORTANT - there is no "list_all_receipts" function at
			Monzo - so therefore you need to keep a system.
		IMPORTANT - this function is JSON encoded rather than form
	*/	

	//Setup connection
	require "conn.php";
	$access_token = get_data($conn, "access_token");

	//Receipt ID will be a function of the transaction id
	$RECEIPT_PREFIX = "rpi-monzo-";

	//Setup OP
	$op = [];
	//Get the posted info:
	if( isset( $_REQUEST['new_receipt']) ) {
		$receipt = $_REQUEST['new_receipt'];
	}else{
		$op['error'] = "No receipt passed";
		$op['status'] = 400;
		die( json_encode($op) );
	}

	//Format the receipt to a payload:
	$transaction_id = $receipt['trans_id'];
	$trans_amount = round($receipt['amount']*-1);	
	$raw_items = json_decode($receipt['content']);

	$receipt_id = $RECEIPT_PREFIX . substr($transaction_id, 3);
	$op['final_receipt_id'] = $receipt_id;

	$post_items = [];
	//Create an empty Tax Line
	$tax = Array(
		"description" => "VAT",
		"amount"=> 0,
		"currency"=> "GBP",
		"tax_number"=>"00000000"
		);

	//Process each receipt line
	foreach ($raw_items as $k => $v) {
		$p = [];	
		$p['amount'] = round($v->amount *1);		
		$p['quantity'] = $v->quantity *1;
		$p['units'] = $v->units;
		$p['tax'] = 0;
		$p['description'] = $v->description;
		$p['currency'] = "GBP";
		array_push($post_items, $p);
	}
	
	//Add generic payment line
	$payment = Array(
		"type" => "card",
		"amount" => $trans_amount,
		"currency" => "GBP"
	);
	

	//Generate the curl payload for the request:
	$payload = Array(
		"transaction_id" => $transaction_id,
		"external_id" => $receipt_id,
		"total" => $trans_amount,
		"currency" => "GBP",
		"items" => $post_items,
		"taxes" => [$tax],
		"payments" => [$payment]
	);

	$op['payload'] = $payload;	
	
	//Rest of curl parameters
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/transaction-receipts";

	//JSON ENCODE IT - important - different to other requests
	$curl_data = json_encode($payload);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	//This is a PUT request
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
		//Receipt has been added
		$op['register_receipt'] = send_receipt($conn, $receipt_id, $transaction_id, json_encode($post_items));
		$op['success'] = "SUCCESS";
	}else {
		//Receipt was not added
		$op['success'] = "FAIL";
		$op['error'] = json_decode($response);
	}
	echo json_encode($op);
?>