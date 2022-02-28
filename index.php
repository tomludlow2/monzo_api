<?php
  $PAGE_TITLE = "Monzo RPi Welcome";
  
  /*
    =======================================================
    Monzo API & PHP Integration
      -GH:        https://github.com/tomludlow2/monzo_api
      -Monzo:     https://docs.monzo.com/

    Created By:   Tom Ludlow   tom.m.lud@gmail.com
    Date:         Feb 2022

    Tools / Frameworks / Acknowledgements 
      -Bootstrap (inc Icons): MIT License, (C) 2018 Twitter 
        (https://getbootstrap.com/docs/5.1/about/license/)
      -jQuery:    MIT License, (C) 2019 JS Foundation 
        (https://jquery.org/license/)
      -Monzo Developer API
    ========================================================
      file_name:  index..php
      function:   begin the process of registration
      arguments (default first):  
        - nil
  */

  //Setup and connect
	require "conn.php";
  //TODO: Registration locally
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
        <h1 class="display-5 mb-3 fw-normal"><?php echo $PAGE_TITLE;?></h1>
        <p class="lead">Welcome</p>
        <p>Welcome to the Monzo API Integration - designed to allow you to import your monzo history live into other applications & manipulate the data as requried</p>
        <p>To commence, click the button below</p>        
        <a href="<?php echo $url;?>" class="btn btn-success" role="button">Sign in</a>
        <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
      </form>
    </main>
  </body>
</html>
