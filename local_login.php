<?php
  $PAGE_TITLE = "Monzo RPi Local Login";
  
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
      file_name:  local_login.php
      function:   check local login credentials
      arguments (default first):  
        - username  - Local username
        - password  - Local password
  */

  //Setup and connect
  $SESSION_EXEMPT = 1;
	require "conn.php";

  $display_signin_form = 1;
  $display_result = 0;
  $op = "";
  $login_attempt = "<h3><span class='badge bg-danger'>Login Failed</span></h3>";

  if( isset($_REQUEST['username']) && isset($_REQUEST['password']) ) {
    //User is attempting to authenticate
    $authenticated = 0;
    $display_result = 1;
    $display_signin_form = 0;
    $db_uname = get_data($conn, "local_username");
    $db_pwd = get_data($conn, "local_pwd");
    if( $_REQUEST['username'] == $db_uname ) {
      if( md5($_REQUEST['password']) == $db_pwd ) {
        $authenticated = 1;
        $login_attempt = "<h3><span class='badge bg-success'>Login Success</span></h3>";
        validate_session();
      }else {
        destroy_session();
      }
    }else {
      destroy_session();
    }

  }else {
    //User has landed - display username / password fields
    destroy_session();
  }

  if(!$display_signin_form) {
    $signin_css = "style='display:none;'";
  }

  if(!$display_result) {
    $result_css = "style='display:none;'";
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
    <?php echo $op;?>
    <main class="form-signin" id='login_form' <?php echo $signin_css;?>>
      <form action="local_login.php" method="POST">
        <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
        <h1 class="display-5 mb-3 fw-normal"><?php echo $PAGE_TITLE;?></h1>
        <p class="lead">Welcome</p>
        <p>Please login to the system locally before any remote features will work.</p>
        <div class="form-floating">
          <input type="text" class="form-control" id="username" name="username">
          <label for="username">Username</label>
        </div>
        <div class="form-floating">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password">
          <label for="password">Password</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
      </form>
      <p class="mt-5 mb-3 text-muted">© 2017–2021</p>
    </main>

    <main class="form-signin" id='result' <?php echo $result_css;?>>
      <form>
        <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
        <h1 class="display-5 mb-3 fw-normal"><?php echo $PAGE_TITLE;?></h1>
        <div class="row">
          <div class="col mb-3">
            <h3>Outcome</h3>
          </div>
          <div class="col mb-3">
            <?php echo $login_attempt;?>
          </div>
        
      </form>
      <p class="mt-5 mb-3 text-muted">© 2017–2021</p>
    </main>
  </body>
</html>
