<?php
	
	$PAGE_TITLE = "Delete Receipt";
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
			file_name:  delete_receipt.php
			function:	attempt to delete a receipt from monzo
			arguments (default first):	
				-receipt_id:	The receipt ID to delete

		IMPORTANT: This does not work currently
		- Despite sending the request successfully the outcome
			is always that there are insufficient permissions,
		- If anyone knows why, let me know!
	*/

	//This function will get receipt data transactions
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$op = [];
	
	if( isset($_REQUEST['receipt_id']) ) {
		$receipt_id = $_REQUEST['receipt_id'];
		$op['receipt_id'] = $receipt_id; 
	}else {
		$op['error'] = "No receipt_id provided";
		$op['status'] = 400;
		die(json_encode($op));
	}	

	//Setup CURL
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/transaction-receipts/?external_id=$receipt_id";
	$curl_data = Array(
		"external_id" => $receipt_id
	);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_data);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Accept: application/json",
   		'Content-Type:application/x-www-form-urlencoded',
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);
	
	if( $op['status'] == 200 ) {
		//Receipt has been deleted
		$op['register_delete_receipt'] = delete_receipt($conn, $receipt_id);
		$op['success'] = "SUCCESS";
	}else {
		//Receipt was not added
		$op['success'] = "FAIL";
		$op['error'] = $response;
	}
	
	echo json_encode($op);
?>