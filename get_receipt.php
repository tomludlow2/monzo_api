<?php

	$PAGE_TITLE = "Get Receipt Info";
	
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
			file_name:  get_receipt.php
			function:	read out the receipt data for a trans
			arguments (default first):	
				- receipt_id:	the receipt ID to query
			outputs in JSON only
	*/

	//Connect and setup
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$op = [];
	$op['function'] = "get_receipt";
	if( isset($_REQUEST['receipt_id']) ) {
		$op['receipt_id'] = $_REQUEST['receipt_id'];
		$receipt_id = $op['receipt_id'];
	}else {
		$op['status'] = 400;
		$op['error'] = "No receipt ID provided";
		die(json_encode($op));
	}
	
	//Curl INIT
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/transaction-receipts/?external_id=$receipt_id";
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
   		"Accept: application/json",
   		'Content-Type:application/x-www-form-urlencoded',
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$resp = json_decode($response, true);
	$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);
	$op['response'] = $resp;
	echo json_encode($op);
	
?>
