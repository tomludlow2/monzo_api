<?php
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$account_id = get_data($conn, "account_id");


	//Now check what to do:
	
	//Generate the output for the json
	$op = [];

	if( isset($_REQUEST['webhook_id']) ) {
		$webhook_id = $_REQUEST['webhook_id'];
		$op['webhook_id'] = $webhook_id;
	}else {
		$op['error'] = "no webhook id provided";
		die( json_encode($op));
	}

	$format = "page";
	if( isset($_REQUEST['format']) ) {
		if( $_REQUEST['format'] == "json" ) {
			$format = "json";
			$op['format'] = "json";
		}
	}

	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/webhooks/$webhook_id";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);		
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
   		"Accept: application/json",
   		$authorisation,
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$response = curl_exec($curl);
	$resp = json_decode($response, true);
	$status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	curl_close($curl);

	$op['delete_outcome'] = $status;

	//Now update the local webhook database
	$authorisation = "Authorization: Bearer $access_token";
	$url = "https://api.monzo.com/webhooks?account_id=$account_id";

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

	$webhook_ids = [];
	$webhooks = $resp['webhooks'];
	$cards = "";
	foreach ($webhooks as $webhook) {
		array_push($webhook_ids, $webhook['id']);
		$cards .= generate_card($webhook);
	}

	$send = send_data($conn, "webhook_ids", json_encode($webhook_ids));
	$op['final_list'] = $resp;

	if( $format == "json" ) {
		die( json_encode($op) );
	}else{

	}

	function generate_card($webhook) {
		$id = $webhook['id'];
		$endpoint = $webhook['url'];
		$r = "<div class=\"col mb-3\">";
		$r .= "<div class=\"card text-center\">";
		$r .= "<div class=\"card-header\"> -- <img src='assets/icons/link.svg' height='25px' /> -- Webhook -- <img src='assets/icons/link.svg' height='25px' /> -- </div>";
		$r .= "<div class=\"card-body\">";
		$r .= "<h5 class=\"card-title\">$id</h5>";
		$r .= "<p class=\"card-text\">This webhook will receive a PUSH request with updates to the linked monzo account.</p>";
		$r .= "<p class=\"card-text\">Endpoint: $endpoint</p>";

		$r .= "<a class='btn btn-primary test_webhook' id='$id' href='$endpoint'>Test Webhook</a> - <a class='btn btn-danger delete_webhook' id='$id'>Delete Webhook</a></div></div></div>";
		return $r;
	}
	
	$json_pre = "<pre class='text-start'>" . json_encode($op, JSON_PRETTY_PRINT) . "</pre>";
	$display_json = 1;
	
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>RPI-Monzo - Delete Webhook</title>

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
    <p class="lead">Monzo Delete Webhook</p>
    	

   	<div class="row">
   		<?php echo $cards;?>
	</div>

	<div class="row">
		<div class="col mb-3" style='<?php if(!$display_json) echo "display: none;"?>'>
			<div class="card text-center" >
				<div class="card-header">JSON Output</div>
				<div class="card-body">
					<p class="card-text" id='response_output'><?php echo $json_pre; ?></p>
				</div>		
				<div class="card-footer text-muted">Monzo API Integration</div>
			</div>
		</div>

	</div>


    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
</main>

  </body>
  <script src='assets/jquery.js'></script>
  <script  src='assets/webhooks.js'></script>
</html>