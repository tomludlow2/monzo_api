<?php

	/*This function gets the last transaction ID and gets new transctions since then.*/
	$YOUR_TEST_TRANS_ID = "tx_0000AGEf7PnGkDa8eDPmXx";

	//Connect and get relevant information
	$op = ["function"=>"get_new_transactions"];
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");
	$last_transaction = get_last_transaction($conn);

	$last_trans_id = "";
	if( $last_transaction['query_success'] == 1 ) {
		$last_trans_id = $last_transaction['transaction_id'];
		$op["transaction_id"] = $last_transaction['transaction_id'];
	}else {
		$op["success"] = 0;
		$op["error"] = $last_transaction['error'];
		die( json_encode($op) );
	}
	$authorisation = "Authorization: Bearer $access_token";

	//Setup request
	if(isset($_GET['test_run'])) $last_trans_id = $YOUR_TEST_TRANS_ID;
	$url = "https://api.monzo.com/transactions/?account_id=$account_id&since=$last_trans_id";


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

	if($format == "page") {	
		//print_r($pots_lookup);
		require("generate_transaction_table.php");
		$table = transaction_table($output_transactions);
	}else if($format == "json") {
		die(json_encode($op));
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
    <title>RPI-Monzo - New Transactions</title>

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
    <p class="lead">Monzo New Transactions</p>
    	

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