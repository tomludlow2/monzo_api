<?php

	require "conn.php";
	$client_id = get_data($conn, "client_id");
	$client_secret = get_data($conn, "client_secret");
	$redirect_uri = get_data($conn, "redirect_uri");
	$grant_type = "refresh_token";
	$refresh_token = get_data($conn, "refresh_token");

	$url = "https://api.monzo.com/oauth2/token";

	$response = httpPost($url,
		array("grant_type"=>$grant_type,"client_id"=>$client_id, "client_secret"=>$client_secret, "redirect_uri"=>$redirect_uri, "refresh_token"=>$refresh_token)
	);


	$resp = json_decode($response, true);

	$op = [];
	$a = $b = $c = $d = 0;


	if( isset($resp['access_token'])) {
		send_data($conn, "access_token", $resp['access_token']);
		array_push($op, "Success 1 - Access token found");
		$a = 1;
	}else {
		array_push($op, "Error 1 - Access token NOT found");
	}

	if( isset($resp['expires_in'])) {
		$t = $resp['expires_in'];
		$expires_at = strtotime("+$t second");
		$expires_at_human = date("F j, Y, g:i a", strtotime("+$t second") );
		send_data($conn, "expires_at", $expires_at);
		send_data($conn, "expires_at_human", $expires_at_human);
		$b = 1;
		array_push($op, "Success 2 - Expiry Time found");
	}else {
		array_push($op, "Error 2 - Expiry Token NOT found");
	}

	if( isset($resp['refresh_token'])) {
		send_data($conn, "refresh_token", $resp['refresh_token']);
		$c = 1;
		array_push($op, "Success 3 - Refresh token found");
	}else {
		array_push($op, "Error 3 - Refresh token NOT found");
	}

	if( isset($resp['scope'])) {
		send_data($conn, "scope", $resp['scope']);
		$d = 1;
		array_push($op, "Success 4 - Scope found");
	}else {
		array_push($op, "Error 4 - Scope NOT found");
	}


	function generate_table($a,$b,$c,$d) {
		$table = "<ul class=\"list-group list-group-flush text-start\">";
		if( $a ) {
			$table .= "<li class=\"list-group-item\">Access Token <span class=\"badge bg-success\">Success</span></li>";
		}else {
			$table .= "<li class=\"list-group-item\">Access Token <span class=\"badge bg-danger\">Error</span></li>";
		}

		if( $b ) {
			$table .= "<li class=\"list-group-item\">Expiry Time <span class=\"badge bg-success\">Success</span></li>";
		}else {
			$table .= "<li class=\"list-group-item\">Expiry Time <span class=\"badge bg-danger\">Error</span></li>";
		}

		if( $c ) {
			$table .= "<li class=\"list-group-item\">Refresh Token <span class=\"badge bg-success\">Success</span></li>";
		}else {
			$table .= "<li class=\"list-group-item\">Refresh Token <span class=\"badge bg-danger\">Error</span></li>";
		}

		if( $d ) {
			$table .= "<li class=\"list-group-item\">Scope <span class=\"badge bg-success\">Success</span></li>";
		}else {
			$table .= "<li class=\"list-group-item\">Scope <span class=\"badge bg-danger\">Error</span></li>";
		}

		$table .= "</ul>";
		return $table;
	}

	if( ($a+$b+$c+$d) == 4 ) {
		//All correct - proceed
		$title = "Token Refresh Success";
		$body = "<p>The exchange process correctly refreshed your refresh token to a new authentication token, and is now ready for the next stage</p>";
		$body .= generate_table($a,$b,$c,$d);
		$url = "hub.php";
		$button_text = "Operations Hub";
		$button_class = "btn-primary";

	}else {
		//An error!
		$title = "Token Refresh Failure";
		$body = "<p>There was an error converting the refresh token to an authentication token.</p>";
		$body .= generate_table($a,$b,$c,$d);
		$url = "index.php";
		$button_text = "Restart";
		$button_class = "btn-warning";
	}

	function httpPost($url, $data){
	    $curl = curl_init($url);
	    curl_setopt($curl, CURLOPT_POST, true);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    $response = curl_exec($curl);
	    curl_close($curl);
	    return $response;
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
    <title>RPI-Monzo - Refresh Token?</title>

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
    
<main class="form-signin">
  <form>
    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
    <p class="lead">Refresh Access Token</h1>
    
	<div class="card text-center">
		<div class="card-header">Outcome of Operation</div>
		<div class="card-body">
			<h5 class="card-title"><?php echo $title; ?></h5>
			<p class="card-text"><?php echo $body; ?></p>
			<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
		</div>
		<div class="card-footer text-muted">Monzo API Integration</div>
	</div>
    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
  </form>
</main>


    
  </body>
</html>
