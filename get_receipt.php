<?php

	//This function will get receipt data transactions
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$op = [];
	$op['function'] = "get_receipt";

	if( isset($_POST['receipt_id']) ) {
		$op['receipt_id'] = $_POST['receipt_id'];
	}else {
		$op['error'] = "No receipt ID provided";
		die(json_encode($op));
	}
	

	//For test purpopse:
	$receipt_id = $_POST['receipt_id'];
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
	curl_close($curl);

	$op['response'] = $resp;

	echo json_encode($op);

	
	
?>
