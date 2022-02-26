<?php
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");


	$authorisation = "Authorization: Bearer $access_token";

	$url = "https://api.monzo.com/webhooks";
	$op = [];
	$op['function'] = "register_webook";
	if( isset( $_REQUEST['endpoint'] ) ) {
		$op['endpoint'] = $_REQUEST['endpoint'];
		$endpoint = $_REQUEST['endpoint'];
	}else {
		$op['error'] = "no handler provided";
		die( json_encode($op));
	}

	$data = Array(
		"account_id" => "$account_id",
		"url"=> $endpoint);

	$curl_data = http_build_query($data, '', '&');

	$data_json = json_encode($data);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_data);

	$headers = array(
   		"Accept: application/json",
   		'Content-Type:application/x-www-form-urlencoded',
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$resp = json_decode($response, true);
	$status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE );
	$op['status'] = $status;
	curl_close($curl);

	$op['response'] = $resp;

	echo json_encode($op);
	
?>