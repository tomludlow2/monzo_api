<?php
	
	$PAGE_TITLE = "Access Token Exchange";
	
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
			file_name:  get_access_token.php
			function:		to create an access token from user info
			arguments (default first):	
				-	format:					"json" or "page"
				- hide_json: 			undefined or true
	*/

	require "conn.php";
	$authorisation_code = get_data($conn, "temporary_code");
	$client_id = get_data($conn, "client_id");
	$client_secret = get_data($conn, "client_secret");
	$redirect_uri = get_data($conn, "redirect_uri");
	$grant_type = "authorization_code";

	$url = "https://api.monzo.com/oauth2/token";

	//For some reason I didn't use curl here.
	$response = httpPost($url,
		array("grant_type"=>"authorization_code","client_id"=>$client_id, "client_secret"=>$client_secret, "redirect_uri"=>$redirect_uri, "code"=>$authorisation_code)
	);

	$resp = json_decode($response, true);
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

	$op = [];
	$a = $b = $c = $d = 0;

	if( isset($resp['access_token'])) {
		send_data($conn, "access_token", $resp['access_token']);
		$op['access_token_found'] = "Success 1 - Access token found";
		$a = 1;
	}else {
		$op['access_token_found'] = "Error 1 - Access token not found";
	}

	if( isset($resp['expires_in'])) {
		$t = $resp['expires_in'];
		$expires_at = strtotime("+$t second");
		$expires_at_human = date("F j, Y, g:i a", strtotime("+$t second") );
		send_data($conn, "expires_at", $expires_at);
		send_data($conn, "expires_at_human", $expires_at_human);
		$b = 1;
		$op['expiry_time'] = $expires_at;
		$op['expiry_time_human'] = $expires_at_human;
	}else {
		$op['expiry_time'] = "Error 2 - Expiry Token NOT found";
		$op['expiry_time_human'] = "Error 2 - Expiry Token NOT found";
	}

	if( isset($resp['refresh_token'])) {
		send_data($conn, "refresh_token", $resp['refresh_token']);
		$c = 1;
		$op['refresh_token_found'] = "Success 3 - Refresh token found";
	}else {
		$op['refresh_token_found'] = "Error 3 - Refresh token not found";
	}

	if( isset($resp['scope'])) {
		send_data($conn, "scope", $resp['scope']);
		$d = 1;
		$op['scope_found'] = "Success 4 - Scope Found";
	}else {
		$op['scope_found'] = "Error 4 - Scope Not Found";	
	}


	function generate_table($a,$b,$c,$d) {
		$table = "<ul class=\"list-group list-group-flush text-center\">";
		if( $a ) {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Access Token</div><div class='col'><span class=\"badge bg-success\">Success</span></div></div></li>";
		}else {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Access Token</div><div class='col'><span class=\"badge bg-danger\">Error</span></div></div></li>";
		}

		if( $b ) {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Expiry Time</div><div class='col'><span class=\"badge bg-success\">Success</span></div></div></li>";
		}else {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Expiry Time</div><div class='col'><span class=\"badge bg-danger\">Error</span></div></div></li>";
		}

		if( $c ) {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Refresh Token</div><div class='col'><span class=\"badge bg-success\">Success</span></div></div></li>";
		}else {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Refresh Token</div><div class='col'><span class=\"badge bg-danger\">Error</span></div></div></li>";
		}

		if( $d ) {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Scope</div><div class='col'><span class=\"badge bg-success\">Success</span></div></div></li>";
		}else {
			$table .= "<li class=\"list-group-item\"><div class='row'><div class='col'>Scope</div><div class='col'><span class=\"badge bg-danger\">Error</span></div></div></li>";
		}

		$table .= "</ul>";
		return $table;
	}

	if( ($a+$b+$c+$d) == 4 ) {
		//All correct - proceed
		$title = "Token Exchange Success";
		$body = "<p>The exchange process correctly converted your temporary access token to an authentication token, and is now ready for the next stage</p>";
		$body .= generate_table($a,$b,$c,$d);
		$url = "whoami.php";
		$button_text = "Validate Credentials";
		$button_class = "btn-primary";
		$op['status'] = 200;
		$op['success'] = 1;
	}else {
		//An error!
		$title = "Token Exchange Failure";
		$body = "<p>There was an error converting the temporary access token to an authentication token.</p>";
		$body .= generate_table($a,$b,$c,$d);
		$url = "index.php";
		$button_text = "Restart";
		$button_class = "btn-warning";
		$op['status'] = 500;
		$op['success'] = 0;
	}


	//using php curl (sudo apt-get install php-curl) 
	function httpPost($url, $data){
	    $curl = curl_init($url);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    $response = curl_exec($curl);
	    curl_close($curl);
	    return $response;
	}

	if( $format == "json") {
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
						<div class="card-header">Outcome of Exchange</div>
						<div class="card-body">
							<h5 class="card-title"><?php echo $title; ?></h5>
							<p class="card-text"><?php echo $body; ?></p>
							<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
						</div>
						<hr/>
						<h5 class="card-title" style='<?php if(($a+$b+$c+$d)!=4)echo "display:none;"?>'>Important</h5>
						<div class="card-body"  style='<?php if(($a+$b+$c+$d)!=4)echo "display: none;"?>'>
							<p class="card-text">You have been sent a Monzo Notification to allow the app to access info now. Please respond to this</p>
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
		</main>    
  </body>
</html>