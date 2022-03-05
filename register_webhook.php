<?php
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
		file_name:  register_webhook.php
		function:	async backend to register a webhook
		arguments (default first):	
			- endpoint:		qualified URL endpoint
	*/

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