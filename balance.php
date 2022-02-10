<?php

	//Connect and send the request
	require "conn.php";
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
	curl_close($curl);

	//Now check what to do:
	$format = "page";
	//Generate the output for the json
	$op = [];

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

		#Balances are in pence
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

		$title = "Monzo Balance Information";
		$body = gen_table($op);
		$json_pre = "<pre class='text-start'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>";
		$button_text = "Return to Hub";
		$button_class = "btn-primary";
		$url = "hub.php";
		if( 1==1 /*Check if user displaying JSON*/) {
			$display_json = 1;
		}

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
    <title>RPI-Monzo - Balances</title>   

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
    </style>

    
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">    
	<main class="container">
	    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
	    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
	    <p class="lead">Account Balance</h1>
	    	

	   	<div class="row">
	   		<div class="col mb-3">
				<div class="card text-center">
					<div class="card-header">Balance Information</div>
					<div class="card-body">
						<h5 class="card-title"><?php echo $title; ?></h5>
						<p class="card-text"><?php echo $body; ?></p>
						<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
					</div>		
					<div class="card-footer text-muted">Monzo API Integration</div>
				</div>
			</div>

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
</html>