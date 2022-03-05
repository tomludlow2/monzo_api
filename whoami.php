<?php

	$PAGE_TITLE = "Monzo Permissions Check";
	
	/*
		=======================================================
		Monzo API & PHP Integration
			-GH:				https://github.com/tomludlow2/monzo_api
			-Monzo:			https://docs.monzo.com/

		Created By:  	Tom Ludlow   tom.m.lud@gmail.com
		Date:					Feb 2022

		Tools / Frameworks / Acknowledgements 
			-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
				(https://getbootstrap.com/docs/5.1/about/license/)
			-jQuery:		MIT License, (C) 2019 JS Foundation 
				(https://jquery.org/license/)
			-Monzo Developer API
		========================================================
			file_name:  whoami.php
			function:		validate stored credentials
			arguments (default first):	
				-	format:					"json" or "page"
				- store:					"1" or "0"
				- hide_json: 			undefined or true
	*/


	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/ping/whoami";
	$op = [];

	//Modulate outcome (json vs page):
	$format = "page";	
	if( isset($_REQUEST['format']) ) {
		if( $_REQUEST['format'] == "json" ) {
			$format = "json";
			$op['format'] = "json";
		}
	}

	//Modulate display of json (for page setting)
	$display_json = 1;
	if( isset($_REQUEST['hide_json']) ) {
		$display_json = 0;
	}

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec($curl);
	$resp = json_decode($response, true);
	$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	$op['response'] = $resp;
	curl_close($curl);

	if( $resp['authenticated'] == true) {
		$title = "Token Validated";
		$body = "The token is <span class=\"badge bg-success\">Validated</span> and is okay to use. Note that this does not mean that it can be used to <span class=\"badge bg-info\">Manipulate</span> data yet, the user needs to validate this in the app first";
		$url = "hub.php";
		$button_class = "btn-primary";
		$button_text = "Proceed to Operations Hub";

	}else {
		$title = "Token Failed";
		$body = "The token is <span class=\"badge bg-danger\">Invalid</span> and cannot be used. This could be because the token has expired, or has been replaced. A new token needs to be generated. To do this the process must be restarted.";
		$url = "index.php";
		$button_class = "btn-warning";
		$button_text = "Restart Auth";
	}

	if($format == "json") {
		die(json_encode($op));
	}

	$json_pre = "<pre class='text-start' id='response_output'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>"; 

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title><?php echo TITLE;?></title>
		<link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="signin.css" rel="stylesheet">
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }
      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
  </head>
  <body class="text-center">    
		<main class="container">		
	    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
	    <h1 class="display-5 mb-3 fw-normal"><?php echo TITLE;?></h1>
	    <p class="lead"><?php echo $PAGE_TITLE;?></p>

	    <div class="row">
	    	<div class="col mb-3">		    
					<div class="card text-center">
						<div class="card-header">Outcome of Check</div>
						<div class="card-body">
							<h5 class="card-title"><?php echo $title; ?></h5>
							<p class="card-text"><?php echo $body; ?></p>
							<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
						</div>
						<div class="card-footer text-muted"><?php echo FOOTER;?></div>
					</div>
				</div>

				<div class="col mb-3">
					<div class="card text-center" >
						<div class="card-header">JSON Output</div>
						<div class="card-body">
							<p class="card-text"><?php if($display_json) echo $json_pre; ?></p>
						</div>		
						<div class="card-footer text-muted"><?php echo FOOTER;?></div>
					</div>
				</div>

			</div>
	    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
		</main>`
  </body>
</html>
