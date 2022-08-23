<?php
	
	//Defs
	$DEFAULT_TARGET_URL = @WEB_ROOT . "whoami.php";
	$DEFAULT_IMAGE_URL = @WEB_ROOT . "logo.png";
	$DEFAULT_BG_COL = "#FFFFFF";
	$DEFAULT_TEXT_COL = "#000000";

	//Connect
	require "conn.php";

	//Get the relevant auth
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$authorisation = "Authorization: Bearer $access_token";

	//Setup the info
	$url = "https://api.monzo.com/feed";

	$op = [];

	$proceed = 1;

	$params = [];
	if( isset( $_REQUEST['title'] ) ){
		if( $_REQUEST['title'] != "" ){
			$params['title'] = $_REQUEST['title'];
		}else {
			$proceed = 0;
			$op['title_error'] = "No title provided";
		}
	}else {
		$proceed = 0;
		$op['title_error'] = "No title provided";
	}

	if( isset($_REQUEST['image_url']) ) {
		if( filter_var($_REQUEST['image_url'], FILTER_VALIDATE_URL) ) {
			$params['image_url'] = $_REQUEST['image_url'];
		}else {
			$params['image_url'] = WEB_ROOT . "/logo.png";
			$op['image_url_error'] = "Provided value did not validate as a URL";
		}		
	}else {
		$params['image_url'] = $DEFAULT_IMAGE_URL;
	}

	if( isset( $_REQUEST['body']) ) {
		if( $_REQUEST['body'] != "") {
			$params['body'] = $_REQUEST['body'];
		}else {
			$proceed = 0;
			$op['body_error'] = "No body provided";
		}
	}else {
		$proceed = 0;
		$op['body_error'] = "No body provided";
	}

	if( isset( $_REQUEST['background_colour']) ) {
		if( preg_match('/#([a-f0-9]{3}){1,2}\b/i', $_REQUEST['background_colour']) ) {
			$params['background_color'] = $_REQUEST['background_colour'];
		}else {
			$params['background_color'] = $DEFAULT_BG_COL;
			$op['background_color_error'] = "Defaulted to white as submitted colour was not hex";
		}
	}else {
		$params['background_color'] = $DEFAULT_BG_COL;
	}

	if( isset( $_REQUEST['text_colour']) ) {
		if( preg_match('/#([a-f0-9]{3}){1,2}\b/i', $_REQUEST['text_colour']) ) {
			$params['title_color'] = $_REQUEST['text_colour'];
			$params['body_color'] = $_REQUEST['text_colour'];
		}else {
			$params['body_color'] = $DEFAULT_TEXT_COL;
			$params['title_color'] = $DEFAULT_TEXT_COL;
			$op['text_colour_error'] = "Defaulted to black as submitted colour was not hex";
		}
	}else {
		$params['body_color'] = $DEFAULT_TEXT_COL;
		$params['title_color'] = $DEFAULT_TEXT_COL;
	}

	$target_url = $DEFAULT_TARGET_URL;
	if( isset( $_REQUEST['target_url']) ) {
		if( filter_var($_REQUEST['target_url'], FILTER_VALIDATE_URL) ) {
			$target_url = $_REQUEST['target_url'];
		}else {
			$op['target_url_error'] = "Provided value did not validate as a URL";
		}
	}

	if( $proceed ) {

		$data = Array(
			"account_id" => "$account_id",
			"url"=> $target_url,
			"type"=> "basic",
			"params"=> $params);

		$op['message_data'] = $data;

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
		curl_close($curl);

		$op['response'] = $resp;
	}else {
		$op['response'] = "Did not proceed - incomplete title or body";
	}
	
	echo json_encode($op);
	
?>