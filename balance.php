<?php
	$PAGE_TITLE = "Monzo Balance";
	
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
			file_name:  balance.php
			function:		readout, and store balance data
			arguments (default first):	
				-	format:					"json" or "page"
				- store:					"1" or "0"
				- hide_json: 			undefined or true
	*/

	//Connect and send the request
	require "conn.php";
	$op = [];
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/balance?account_id=$account_id";
	$arr = Array('account_id'=>$access_id);
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
	curl_close($curl);

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

	//Modulate storage (or not):
	$store = 1;
	if( isset($_REQUEST['store']) ) {
		if( $_REQUEST['store'] == "0" ) {
			$store = 0;
			$op['stored'] = false;
		}else {
			$op['stored'] = true;
		}
	}	


	#Check if the API returned appropriate information
	if( $resp['code'] == "forbidden.insufficient_permissions") {
		$op['error'] = "Access forbidden due to insufficient permissions";
		$title = "Access Forbidden";
		$body = $op['error'] . ". This is usually because you have not allowed access in the Monzo App. Please do so now.";
		$button_text = "Alternatively - Restart";
		$button_class = "btn-warning";
		$url = "index.php";
		$display_json = 0;
	}else if( isset($resp['balance']) ) {
		$op['success'] = 1;
		//Proceed to collect the balance information
		$op['current_balance'] = $resp['balance'];
		$op['current_total_balance'] = $resp['balance_including_flexible_savings'];
		$op['daily_spend'] = $resp['spend_today'];

		//If store is enabled - store now:
		if( $store ) {
			send_data($conn, "current_balance", $op['current_balance']);
			send_data($conn, "current_total_balance", $op['current_total_balance']);
			$op['daily_balance_sent'] = send_daily_balance($conn, $account_id, $op['current_balance'], $op['current_total_balance'] );
		}

		if( $format == "json" ) {
			die(json_encode($op));
		}

		$body = gen_table($op);
		$json_pre = "<pre class='text-start'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>";
		$button_text = "Return to Hub";
		$button_class = "btn-primary";
		$url = "hub.php";
	}

	function gen_table($op) {
		$r = "<table class='table table-hover'>";
		$r .= "<tr><th>#</th><th>Account Info</th></tr>";
		$desc = [
			"current_balance" => "Account Balance",
			"current_total_balance" => "Balance (inc Savings)",
			"daily_spend" => "Spent Today"
			];
		foreach($op as $key => $value) {
			if( in_array($key, ["current_balance", "current_total_balance", "daily_spend"]) ){
				$r .= "<tr><td>" . $desc[$key] . "</td><td>&pound;" . number_format(($value/100),2) . "</td></tr>";			
			}
		}
		$r .= "</table>";
		return $r;
	}

	
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
      body {
      	display: block !important;
      
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
						<div class="card-header">Balance Information</div>
						<div class="card-body">
							<h5 class="card-title"><?php echo $PAGE_TITLE; ?></h5>
							<p class="card-text"><?php echo $body; ?></p>
							<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
						</div>		
						<div class="card-footer text-muted"><?php echo FOOTER;?></div>
					</div>
				</div>

				<div class="col mb-3">
					<div class="card text-center">
						<div class="card-header">JSON Output</div>
						<div class="card-body">
							<p class="card-text"><?php if($display_json) echo $json_pre; ?></p>
						</div>		
						<div class="card-footer text-muted"><?php echo FOOTER;?></div>
					</div>
				</div>
			</div>
	    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
		</main>   
  </body>
</html>