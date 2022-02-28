<?php
	
	$PAGE_TITLE = "Monzo All Transactions";
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
			file_name:  all_transactions.php
			function:	Get all transactions from a users' account
			arguments (default first):	
				-	format:					"json" or "page"
				- store:					"1" or "0"
				- hide_json: 			undefined or true				

		IMPORTANT:
		1)  This will only work within the first 5 minutes of
				authorising monzo
				If you attempt this after the 5 minutes, you will
				get an error 403.
	*/

	/*NOTE THAT THIS FUNCTION CAN ONLY BE RUN WITHIN THE FIRST 5 MINUTES OF AUTHORISING MOZNO, OTHERWISE IT IS LIMITED TO 90 DAYS OF TRANSACTIONS*/

	//Connect and get info
	require "conn.php";
	$op = [];
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/transactions/?account_id=$account_id";

	//Curl INIT
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	$transactions_holder = json_decode($response, true);
	$transactions = $transactions_holder['transactions'];
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

	$output_transactions = [];
	//Loop each transaction and sort out
	$db_push = [];
	foreach ($transactions as $tx) {
		#Get the important bits
		$ib = [];
		$ib['account_id'] = $account_id;
		$ib['date_created'] = date("Y-m-d H:i:s", strtotime($tx['created']));		
		$ib['date_settled'] = date("Y-m-d H:i:s", strtotime($tx['settled']));
		$ib['amount'] = $tx['amount'];
		$ib['description'] = $tx['description'];
		$ib['merchant_id'] = $tx['merchant'];
		$ib['category'] = $tx['category'];
		$ib['transaction_id'] = $tx['id'];
		$ib['notes'] = $tx['notes'];	
		array_push($db_push, $ib);

		/*
		The output table will show that the transactions were NOT stored
		However this is because a separate function is used here.
		*/
		$send = "All Transactions Stored Together";
		$ib['stored'] = 0;
		$ib['reason_failed'] = $send;
		array_push($output_transactions, $ib);
	}
	$op['output_transactions'] = $output_transactions;

	if( $store ) {
		$op['sent_to_server'] = send_all_transactions($conn, $db_push);
		$op['total_transactions'] = count($transactions);
	}else {
		$op['sent_to_server'] = "User Declined";
		$op['total_transactions'] = count($transactions);
	}

	if( $format == "page" ) {
		require("generate_transaction_table.php");
		$table = transaction_table($output_transactions);
	}else if( $format == "json") {
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
      body {
      	display: block !important;
      }
      .negative_amount {
      	color: 	dark-red;
      }
      th {
      	white-space: nowrap;
      }
    </style>   
  </head>
  <body class="text-center">
    
		<main class="container">
	    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
	    <h1 class="display-5 mb-3 fw-normal"><?php echo TITLE;?></h1>
	    <p class="lead"><?php echo $PAGE_TITLE; ?></p>   	
	   	<div class="row">
	   		<?php echo $table;?>
			</div>

			<div class="row">
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
  <script src='assets/dist/js/bootstrap.bundle.min.js'></script>
  <script src='assets/jquery.js'></script>
  <script  src='assets/transactions.js'></script>
</html>