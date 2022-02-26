<?php

	//This function will get receipt data transactions
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$op = [];
	/*
	if( isset($_POST['receipt_id']) ) {
		$receipt_id = $_POST['receipt_id'];
		$op['receipt_id'] = $receipt_id;
		//Could here check back in DB etc but not going to now. 
	}else {
		$op['error'] = "No receipt_id provided";
		die(json_encode($op));
	}
	*/
	$num = $_GET['num'];
	$receipt_id = "rpi-monzo-receipt-28";

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
	$info = curl_getinfo($curl);
	curl_close($curl);

	//info is an assoc array - check the status code.
	$code = $info['http_code'];
	
	if( $code == 200 ) {
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