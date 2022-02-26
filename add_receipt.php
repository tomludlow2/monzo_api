<?php

	//This function will add receipt data to certain transactions
	/*Note that this will only work with certain types of payment
	
	WARNING: THERE IS NO "LIST ALL RECEIPTS" FUNCTION so it is important you have a relevant structure to store receipts once they have been submitted. 
	*/

	require "conn.php";
	$access_token = get_data($conn, "access_token");


	//Receipt ID will be a function of the transaction id
	$RECEIPT_PREFIX = "rpi-monzo-";

	//Setup OP
	$op = [];

	//Get the posted info:
	if( isset( $_POST['new_receipt']) ) {
		$receipt = $_POST['new_receipt'];
	}else{
		$op['error'] = "No receipt passed";
		die( json_encode($op) );
	}

	//Format the receipt to a payload:

	$transaction_id = $receipt['trans_id'];
	$trans_amount = round($receipt['amount']*-1);	
	$raw_items = json_decode($receipt['content']);

	$receipt_id = $RECEIPT_PREFIX . substr($transaction_id, 3);
	$op['final_receipt_id'] = $receipt_id;

	$post_items = [];
	$tax = Array(
		"description" => "VAT",
		"amount"=> 0,
		"currency"=> "GBP",
		"tax_number"=>"00000000"
		);
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
	//At this stage would be wise to check the amounts add up.

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
	$info = curl_getinfo($curl);
	curl_close($curl);

	//info is an assoc array - check the status code.
	$code = $info['http_code'];
	if( $code == 200 ) {
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