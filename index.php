<?php

	#ToDO:
	/*
	If user already has account, this should redirect the landing page
	*/

	require "conn.php";
	$new_user = 0;

	if($new_user) {
		//Generate new state token and store it
		$state = 0;
	}else {
		//As this is currently just for one user, the state token has already been generated
		$state = get_data($conn, "state");
	}

	$client_id = get_data($conn, "client_id");
	$redirect_uri = get_data($conn, "redirect_uri");
	$response_type = "code";

	$url = "https://auth.monzo.com/?client_id=$client_id&redirect_uri=$redirect_uri&response_type=code&state=$state";

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>RPI - Monzo</title>

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
    <h1 class="h3 mb-3 fw-normal">Monzo API Integration</h1>
    <p>Welcome to the Monzo API Integration - designed to allow you to import your monzo history live into other applications & manipulate the data as requried</p>
    <p>To commence, click the button below</p>
    
    <a href="<?php echo $url;?>" class="btn btn-success" role="button">Sign in</a>

    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
  </form>
</main>


    
  </body>
</html>
