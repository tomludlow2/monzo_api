<?php

	//Connect and send the request
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");

	$authorisation = "Authorization: Bearer $access_token";

	$url = "https://api.monzo.com/pots?current_account_id=$account_id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$pots_holder = json_decode($response, true);
	$pots = $pots_holder['pots'];
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

	$show_deleted = 0;
	if( isset($_GET['show_deleted']) ) {
		if($_GET['show_deleted'] == 1) $show_deleted = 1;
	}


	#Create a pots array for storage in main table
	#Wil be [{pot_id:id, pot_name:name},n++]
	$pots_string_pre = [];
	$op = [];
	$op['pots'] = [];
	//Will need array to hold pots cards
	$cards = "";

	foreach ($pots as $pot) {
		#Check that the pot has not been deleted by the user. 
		$ib = [];
		if($pot['deleted'] != 1) {
			#Get Important Bits:
			$ib['pot_id'] = $pot['id'];
			$ib['pot_name'] = $pot['name'];
			$ib['pot_type'] = $pot['type'];#(default|flexible_savings)
			//Pots_string_pre holds simply a list of current pots to send to the master table
			//It does not include balances
			array_push($pots_string_pre, $ib);


			//Now add the balances to it. 
			$ib['pot_balance'] = $pot['balance']; 

			if( $store ) {
				#Send pot current balance to the server
				$send = send_daily_pot_balance($conn, $account_id, $pot['id'], $pot['balance']);
				$ib['sent_to_server'] = "Did not send to server properly";
				if( $send ) $ib['sent_to_server'] = "Sent successfully";
			}else {
				$ib['sent_to_server'] = "Did not try - store disabled";
			}

			$cards.= generate_card($ib);
			
		}else {
			$ib['pot_id'] = $pot['id'];
			$ib['pot_name'] = $pot['name'];
			$ib['sent_to_server'] = "Did not try - deleted pot";
			$ib['error'] = "Pot was deleted";

			if( $show_deleted ) {
				$ib['pot_balance'] = 0;
				$ib['pot_type'] = "deleted";
				$cards .= generate_card($ib);
			}
		}
		array_push($op['pots'], $ib);

	}

	if( $store ) {
		$main_db = send_data($conn, "pots_list", json_encode($pots_string_pre));
		$op['sent_main_table'] = "Failed";
		if( $main_db) $op['sent_main_table'] = "Success";
	}else {
		$op['sent_main_table'] = 0;
	}

	if( $format == "json" ) {
		die(json_encode($op));
	}

	$display_json = 1;


	function generate_card($pot) {
		$r = "<div class=\"col mb-3\">";
		$r .= "<div class=\"card text-center\">";
		$r .= "<div class=\"card-header\"> -- <img src='assets/icons/piggy-bank.svg' height='25px' /> -- </div>";
		$r .= "<div class=\"card-body\">";
		$r .= "<h5 class=\"card-title\">" . $pot['pot_name'] . "</h5>";
		$r .= "<p class=\"card-text\">" . generate_table($pot) . "</p></div></div></div>";
		return $r;
	}

	function generate_table($pot) {
		$r = "<table class='table table-hover'>";
		$r .= "<tr><th>#</th><th>Account Info</th></tr>";
		$r .= "<tr><td>Pot Name</td><td>" . $pot['pot_name'] . "</td></tr>";
		$r .= "<tr><td>Pot ID</td><td>" . $pot['pot_id'] . "</td></tr>";
		if($pot['pot_type'] == "default") {
			$r .= "<tr><td>Pot Type</td><td>Normal Pot</td></tr>";
		}else if($pot['pot_type'] == "flexible_savings") {
			$r .= "<tr><td>Pot Type</td><td>Flexible Savings</td></tr>";
		}else if($pot['pot_type'] == "deleted") {
			$r .= "<tr><td>Pot Type</td><td>Deleted Pot</td></tr>";
		}
		$r .= "<tr><td>Current Bal</td><td><span pot_id='" . $pot['pot_id'] . "'>&pound;" . number_format(($pot['pot_balance']/100),2) . "</span></td></tr>";
		if( $pot['sent_to_server'] == "Sent successfully") {
			$r .= "<tr><td>Sent to Server?</td><td><span class='badge bg-success'>Sent</span></td></tr>";
		}else {
			$r .= "<tr><td>Sent to Server?</td><td><span class='badge bg-danger'>" . $pot['sent_to_server'] . "</span></td></tr>";
		}
		if( $pot['pot_type'] != "deleted") {
			$r .= "<tr class='text-center'><td><button class='btn btn-outline-primary deposit_button' pot_id='" . $pot['pot_id'] . "' value='Deposit Funds'>Deposit</button></td>";
			$r .= "<td><button class='btn btn-outline-secondary withdraw_button' pot_id='" . $pot['pot_id'] . "' value='Withdraw Funds'>Withdraw</button></td></tr>";
			$r .= "<tr><td colspan='2' class='input_row'><input type='text' class='form-control amount_input' pot_id='" . $pot['pot_id'] . "' placeholder='Amount to Transfer (£)'></td></tr>";
		}

		$r .= "</table>";
		return $r;
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
    <title>RPI-Monzo - Pots</title>

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

      .input_row {
      	display: none;
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    
<main class="container">
    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
    <p class="lead">Monzo Pots</p>
    	

   	<div class="row">
   		<?php echo $cards;?>
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
  <script src='assets/jquery.js'></script>
  <script  src='assets/pots.js'></script>
</html>