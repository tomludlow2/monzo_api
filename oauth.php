<?php
	require "conn.php";
	$client_secret = get_data($conn, "client_secret");;

	$title = "Error";
	$body = "There was an error loading the token";
	$url = "index.php";
	$button_text = "Go back to Index";
	$button_class = "btn-warning";

	#Data comes back in Server:
	if( isset($_GET['code']) ) {
		#This is the first return from the website
		$temporary_code = $_GET['code'];
		if( isset($_GET['state']) ) {
			$validate_state = $_GET['state'];
		}else {
			$validate_state = "Not found in request";
		}
		$current_state = get_data($conn, "state");
		if( $current_state == $validate_state ) {
			#State is correct, store token for next step
			send_data($conn, "temporary_code", $temporary_code);
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
			$button_text = "Quit"; #Probs need to remove data here
		}
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
    <title>RPI-Monzo - OAuth Reception</title>

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
    <p class="lead">OAuth Reception</p>
    
	<div class="card text-center">
		<div class="card-header">Outcome of Authentication</div>
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

