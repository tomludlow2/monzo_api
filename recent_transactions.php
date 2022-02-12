<?php

	/*This function is run by changing the $time variable to one wihtin the last 90 days and then it will fetch the transactions
	Note that transaction_id is a unique field so it will only add the ones that are not already in the table
	*/

	//Connect and get relevant information
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$authorisation = "Authorization: Bearer $access_token";

	// Change this to change when the transactions come from:
	$time = strtotime("-7 days");
	if( isset($_GET['time_filter']) ) {
		$op['custom_filter'] = 1;
		$filter = $_GET['time_filter'];
		if( preg_match('/^[0-9]+_(d|w|h|m)$/', $filter) ) {
			$bits = explode("_", $filter);
			if( $bits[1] == "d") {
				if( $bits[0] <= 90) {
					$new_str = "-" . $bits[0] . " days";
					$time = strtotime($new_str);
				}else {
					$op['filter_error'] = "Too long - only permits <=90 days";
				}
			}else if( $bits[1] == "w") {
				if( $bits[0] <= 12) {
					$new_str = "-" . $bits[0] . " weeks";
					$time = strtotime($new_str);
				}else {
					$op['filter_error'] = "Too long - only permits <=12 weeks";
				}
			}else if( $bits[1] == "h") {
				if( $bits[0] <= 2160) {
					$new_str = "-" . $bits[0] . " hours";
					$time = strtotime($new_str);
				}else {
					$op['filter_error'] = "Too long - only permits <=2160 hours";
				}
			}else if( $bits[1] == "m") {
				if( $bits[0] <= 3) {
					$new_str = "-" . $bits[0] . " months";
					$time = strtotime($new_str);
				}else {
					$op['filter_error'] = "Too long - only permits <=3 months";
				}
			}	
		}else {
			$op['filter_error'] = "Incorrect filter format. Expected num_(d|h|w|m)";
		}
	}
	if( isset($op['filter_error'])) {
		$op['custom_filter'] = 0;
		$op['default_filter'] = "7 days";
	}
	$op['time_filter'] = $time;

	//Setup request
	$url = "https://api.monzo.com/transactions/?account_id=$account_id&since=" . date("Y-m-d\TH:i:s\Z", $time);
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	//Send and decode
	$response = curl_exec($curl);
	$transactions_holder = json_decode($response, true);
	$transactions = $transactions_holder['transactions'];
	curl_close($curl);

	//Now check what to do:
	$format = "page";
	if( isset($_GET['format']) ) {
		if( $_GET['format'] == "json" ) {
			$format = "json";
			$op['format'] = "json";
		}
	}

	$store = 1;
	if( isset($_GET['store']) ) {
		if( $_GET['store'] == "0" ) {
			$store = 0;
			$op['stored'] = false;
		}else {
			$op['stored'] = true;
		}
	}
	
	$op['successful_submissions'] = 0;
	$op['failed_submissions'] = 0;

	$output_transactions = [];
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


		//Attempt to send if requested
		if($store){
			$send = send_transaction_obj($conn, $ib);
		}else {
			$send = "Store parameter set to 0";
		}

		if($send == 1 ) {
			$op['successful_submissions'] ++;
			$ib['stored'] = 1;
		}else {
			$op['failed_submissions']++;
			$ib['reason_failed'] = $send;
			$ib['stored'] = 0;
		}
		array_push($output_transactions, $ib);
	}
	$op['transactions'] = $output_transactions;
	$op['total_transactions'] = count($transactions);
	$op['time_filter_human'] = date("F j, Y, g:i a", $time);

	if($format == "page") {	
		//print_r($pots_lookup);
		require("generate_transaction_table.php");
		$table = transaction_table($output_transactions);
	}else if($format == "json") {
		die(json_encode($op));
	}

	$display_json = 1;
	$json_pre = "<pre class='text-start'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>";

?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>RPI-Monzo - Recent Transactions</title>

   <!-- Bootstrap core CSS -->
<link href="assets/dist/css/bootstrap.min.css" rel="stylesheet">

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

      .positive_amount {

      }

      .negative_amount {
      	color: 	dark-red;
      }

      th {
      	white-space: nowrap;
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    
<main class="container">
    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
    <p class="lead">Monzo Recent Transactions</p>
    	

   	<div class="row">
   		<?php echo $table;?>
	</div>

	<div class="row">
		<div class="col mb-3" style='<?php if(!$display_json) echo "display: none;"?>'>
			<div class="card text-center" >
				<div class="card-header">JSON Output</div>
				<div class="card-body">
					<p class="card-text"><?php echo $json_pre; ?></p>
				</div>		
				<div class="card-footer text-muted">Monzo API Integration</div>
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