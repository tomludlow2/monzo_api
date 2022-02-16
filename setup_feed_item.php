<?php
	
	require "conn.php";
	$name = get_data($conn, "preferred_name");


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>RPI-Monzo - Feed Items</title>

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

      #feed_bg_colour, #feed_text_colour{
        width:    60%;
        margin:   auto;
        height:   60px;
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
    
  <main class="container">
    <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
    <h1 class="display-5 mb-3 fw-normal">Monzo API Integration</h1>
    <p class="lead">Monzo Create Feed</p>
      

    <div class="row">
      <div class="col mb-3">
      <div class="card text-center">
        <div class="card-header">Create Feed Item</div>
        <div class="card-body">
          <h5 class="card-title">Welcome <?php echo $name; ?></h5>
          <p class="card-text">
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
                <input type="text" class="form-control" id="feed_image_url" aria-describedby="image_help" value="https://api.tomludlow.co.uk/banking/monzo/logo.png">
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
                <input type="text" class="form-control" id="feed_target_url" aria-describedby="target_help" value="https://api.tomludlow.co.uk/banking/monzo/whoami.php">
                <div id="target_help" class="form-text">The URL to redirect to when the user clicks the button.</div>
              </div>

              <button type="button" id="create_feed_item" class="btn btn-primary">Create Feed Item</button>
            </form>
          </p>
        </div>    
        <div class="card-footer text-muted">Monzo API Integration</div>
      </div>
    </div>

    <div class="col mb-3">
      <div class="card text-center" >
        <div class="card-header">JSON Output</div>
        <div class="card-body">
          <p class="card-text" id='response_output'>...awaiting request...</p>
        </div>    
        <div class="card-footer text-muted">Monzo API Integration</div>
      </div>
    </div>

  </div>


    <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
  </main>    
  </body>
  <script src='assets/jquery.js'></script>
  <script  src='assets/feed_items.js'></script>
</html>
