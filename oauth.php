<?php

	$PAGE_TITLE = "Monzo OAuth";
	
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
			file_name:  oauth.php
			function:		handle the oauth functions
			arguments (default first):	
				-	code:		The code sent back from monzo
				- state:	The state token we sent to monzo

		IMPORTANT:  This function is visited from the user's email
								once they have asked for a code
								Therefore no JSON option
	*/

	//Connect and setup
	require "conn.php";
	$client_secret = get_data($conn, "client_secret");;

	//Setup some prelimiary info
	$title = "Error";
	$body = "There was an error loading the token";
	$url = "index.php";
	$button_text = "Go back to Index";
	$button_class = "btn-warning";

	//Data comes back in Server:
	if( isset($_REQUEST['code']) ) {
		//This is the first return from the website
		$temporary_code = $_REQUEST['code'];
		if( isset($_REQUEST['state']) ) {
			$validate_state = $_REQUEST['state'];
		}else {
			$validate_state = "Not found in request";
			$op['status'] = 400;
		}

		//Check that the states match (prevent MITM attack)
		$current_state = get_data($conn, "state");
		if( $current_state == $validate_state ) {
			//State is correct, store token for next step
			send_data($conn, "temporary_code", $temporary_code);

			$op['status'] = 200;
			$title = "OAuth Success";
			$body = "You successfully authenticated the app - it is now ready for you to ask Monzo for permissions to access your information";
			$url = "get_access_token.php"; #Could be async.
			$button_class="btn-primary";
			$button_text = "Click to Exchange Temporary -> Access Token";
		}else {
			$title = "OAuth Failure";
			$body = "The information received here does not match that stored in the server. This could suggest a man-in-the-middle attack.";
			$url = "index.php"; 
			$button_class="btn-danger"; 
			$button_text = "Quit";
		}
	}else {
		$op['status'] = 400;
		$op['error'] = "No information generated";
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
		<main class="form-signin">
		  <form>
		    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
		    <h1 class="display-5 mb-3 fw-normal"><?php echo TITLE;?></h1>
		    <p class="lead"><?php echo $PAGE_TITLE;?></p>		    
				<div class="card text-center">
					<div class="card-header">Outcome of Authentication</div>
					<div class="card-body">
						<h5 class="card-title"><?php echo $title; ?></h5>
						<p class="card-text"><?php echo $body; ?></p>
						<a href="<?php echo $url; ?>" class="btn <?php echo $button_class;?>"><?php echo $button_text;?></a>
					</div>
					<div class="card-footer text-muted"><?php echo FOOTER;?></div>
				</div>
		    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
		  </form>
		</main>
  </body>
</html>

