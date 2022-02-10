<?php

	//Connect and send the request
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/accounts";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$accounts_holder = json_decode($response, true);
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
	

	//Load in the accounts information
	$accounts = $accounts_holder['accounts'];
	$op['num_accounts'] = count($accounts);
	if( $op['num_accounts'] >= 1 ) {
		//Currently only works for a sinle account
		$account = $accounts[0];
		#Get the important bits:
		$ib['account_id'] = $account['id'];
		$ib['account_number'] = $account['account_number'];
		$ib['account_sort_code'] = $account['sort_code'];
		$ib['account_created'] = $account['created'];
		$ib['preferred_name'] = $account['owners'][0]['preferred_name'];
		$ib['first_name'] = $account['owners'][0]['preferred_first_name'];

		//Try to sort time
		$ib['account_created_human'] = date("F j, Y, g:i a", strtotime($account['created']) );

		foreach ($ib as $key => $value) {
			if($store) send_data($conn, $key, $value);
			$op[$key] = $value;
		}
	}else {
		$account['error'] = "No account found";
	}

	if($format == "json") {
		die(json_encode($op));
	}else {
		if( $op['num_accounts'] < 1) {
			$title = "No Accounts";
			$body = "There were no accounts found. The most common reason for this is that you have not <span class='badge bg-info'>Activated</span> your API. Open Monzo, and there should be an \"Allow access to your data\" badge. Click this, then return here.";
			$button_text = "Alternatively - Restart";
			$button_class = "btn-warning";
			$url = "index.php";
			$display_json = 0;
		}else {			
			$title = "Relevant Account Info";
			$body = gen_table($op);
			$json_pre = "<pre class='text-start'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>";
			$button_text = "Return to Hub";
			$button_class = "btn-primary";
			$url = "hub.php";
			if( 1==1 /*Check if user displaying JSON*/) {
				$display_json = 1;
			}
		}
	}
	

	function gen_table($op) {
		$r = "<table class='table table-hover'>";
		$r .= "<tr><th>#</th><th>Account Info</th></tr>";
		$desc = [
			"account_id" => "Account ID",
			"account_number" => "Account Number",
			"account_sort_code" => "Sort Code",
			"preferred_name" => "Customer Name",
			"account_created_human" => "Account Created"
			];
		foreach($op as $key => $value) {
			if( in_array($key, ["account_id", "account_number", "account_sort_code", "preferred_name", "account_created_human"]) ){
				$r .= "<tr><td>" . $desc[$key] . "</td><td>" . $value . "</td></tr>";			
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
    <title>RPI-Monzo - Accounts</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">

    

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
    </style>

    
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    
<main class="container">
    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
    <p class="lead">Monzo Accounts</h1>
    	

   	<div class="row">
   		<div class="col mb-3" style='<?php if(!$display_json) echo "width: 50%;"?>'>
			<div class="card text-center">
				<div class="card-header">Account Information</div>
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