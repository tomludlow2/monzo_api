<?php

	/*NOTE THAT THIS FUNCTION CAN ONLY BE RUN WITHIN THE FIRST 5 MINUTES OF AUTHORISING MOZNO, OTHERWISE IT IS LIMITED TO 90 DAYS OF TRANSACTIONS*/

	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");

	$authorisation = "Authorization: Bearer $access_token";

	$url = "https://api.monzo.com/transactions/?account_id=$account_id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$transactions_holder = json_decode($response, true);
	//print_r($transactions_holder);
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
	
	$op = [];
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
		array_push($op, $ib);

		$send = "All Transactions Stored Together";
		$ib['stored'] = 0;
		$ib['reason_failed'] = $send;
		array_push($output_transactions, $ib);
	}
	$op['sent_to_server'] = send_all_transactions($conn, $op);
	$op['total_transactions'] = count($transactions);

	if( $format == "page" ) {
		require("generate_transaction_table.php");
		$table = transaction_table($output_transactions);
	}else if( $format == "json") {
		die(json_encode($op));
	}

	$display_json = 0;
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
    <title>RPI-Monzo - All Transactions</title>

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
    <p class="lead">Monzo All Transactions</p>
    	

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