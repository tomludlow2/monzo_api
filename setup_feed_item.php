<?php

  $PAGE_TITLE = "Setup Feed Item";
  
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
      file_name:  setup_feed_item.php
      function:   create and push a feed item
      arguments (default first):  
        - nil arguments
  */
	require "conn.php";

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
      body {
        display: block !important;
      }
      #feed_bg_colour, #feed_text_colour{
        width:    60%;
        margin:   auto;
        height:   60px;
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
            <div class="card-header">Create Feed Item</div>
            <div class="card-body">
              <h5 class="card-title">Feed Properties</h5>           
              <form>
                <div class="mb-3">
                  <label for="feed_title" class="form-label">Feed Title</label>
                  <input type="text" class="form-control" id="feed_title" aria-describedby="title_help">
                  <div id="title_help" class="form-text">The title of the message you would like to appear in your feed.</div>
                </div> 

                <div class="mb-3">
                  <label for="feed_body" class="form-label">Feed Body</label>
                  <input type="text" class="form-control" id="feed_body" aria-describedby="body_help">
                  <div id="body_help" class="form-text">The main content of the message.</div>
                </div> 

                <div class="mb-3">
                  <label for="feed_image_url" class="form-label">Feed Image URL</label>
                  <input type="text" class="form-control" id="feed_image_url" aria-describedby="image_help" value="<?php echo WEBROOT;?>/logo.png">
                  <div id="image_help" class="form-text">An image url attached to the feed item.</div>
                </div>

                <div class="row">
                  <div class="col">
                    <div class="mb-3">
                      <label for="feed_text_colour" class="form-label">Text Colour</label>
                      <input type="color" class="form-control" id="feed_text_colour" aria-describedby="text_colour_help" value='#000000'>
                      <div id="text_colour_help" class="form-text">Choose a text colour for the notification.</div>
                    </div>
                  </div>

                  <div class="col">
                    <div class="mb-3">
                      <label for="feed_bg_colour" class="form-label">Background Colour</label>
                      <input type="color" class="form-control" id="feed_bg_colour" aria-describedby="background_colour_help" value='#FFFFFF'>
                      <div id="background_colour_help" class="form-text">Choose a background colour for the notification.</div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="feed_target_url" class="form-label">Target URL</label>
                  <input type="text" class="form-control" id="feed_target_url" aria-describedby="target_help" value="<?php echo WEBROOT;?>/whoami.php">
                  <div id="target_help" class="form-text">The URL to redirect to when the user clicks the button.</div>
                </div>

                <button type="button" id="create_feed_item" class="btn btn-primary">Create Feed Item</button>
              </form>            
            </div>    
          <div class="card-footer text-muted"><?php echo FOOTER;?></div>
        </div>
      </div>

      <div class="col mb-3">
        <div class="card text-center" >
          <div class="card-header">JSON Output</div>
          <div class="card-body">
            <p class="card-text" id='response_output'></p>
          </div>    
          <div class="card-footer text-muted"><?php echo FOOTER;?></div>
        </div>
      </div>

    </div>


    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
    </main>    
  </body>
  <script src='assets/jquery.js'></script>
  <script  src='assets/feed_items.js'></script>
</html>
