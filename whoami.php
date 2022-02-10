<?php
	require "conn.php";
	$access_token = get_data($conn, "access_token");

	$authorisation = "Authorization: Bearer $access_token";

	$url = "https://api.monzo.com/ping/whoami";

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



	if( $resp['authenticated'] == true) {
		$title = "Token Validated";
		$body = "The token is <span class=\"badge bg-success\">Validated</span> and is okay to use. Note that this does not mean that it can be used to <span class=\"badge bg-info\">Manipulate</span> data yet, the user needs to validate this in the app first";
		$url = "";
		$button_class = "btn-primary";
		$button_text = "Proceed to Options Page";

	}else {
		$title = "Token Failed";
		$body = "The token is <span class=\"badge bg-danger\">Invalid</span> and cannot be used. This could be because the token has expired, or has been replaced. A new token needs to be generated. To do this the process must be restarted.";
		$url = "index.php";
		$button_class = "btn-warning";
		$button_text = "Restart Auth";
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
    <title>RPI-Monzo - Who am I?</title>

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
    <p class="lead">Who am I?</h1>
    
	<div class="card text-center">
		<div class="card-header">Outcome of Check</div>
		<div class="card-body">
			<h5 class="card-title"><?php echo $title; ?></h5>
			<p class="card-text"><?php echo $body; ?></p>
			<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
		</div>
		<div class="card-footer text-muted">
		Monzo API Integration
		</div>
	</div>
    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
  </form>
</main>


    
  </body>
</html>
