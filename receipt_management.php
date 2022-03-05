<?php

  $PAGE_TITLE = "Monzo Receipt Management";
  require "conn.php";
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
      file_name:  receipt_management.php
      function:   manage receipt data (add, view,
                   edit, delete)
      arguments (default first):  
        - nil
  */
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
      #receipt_holder {
        width: 400px;
      }     
      .controls, #overwrite_holder, #subtotal_incorrect_holder {
        display: none;
      }
    </style>
  </head>
  <body class="text-center">    
    <main class="container">
      <img class="mb-4" src="assets/brand/rpi_cloud.svg" alt="" width="72" height="72">
      <h1 class="display-5 mb-3 fw-normal"><?php echo TITLE;?></h1>
      <p class="lead"><?php echo $PAGE_TITLE;?></p>     

      <div class="row" >
        <!--CREATE RECEIPT-->
        <div class="col mb-3" id="create_receipt_holder">
          <div class="card text-center">
            <div class="card-header">Create Receipt</div>
            <div class="card-body">
              <p class='card-text'>Use this option to create a new receipt to attach to a transaction</p>
              <div class="mb-3">
                <select id='transaction_select' class='form-select'>
                  <option selected>Select a transaction</option>
                </select>
              </div>
              <div class="mb-3" id="overwrite_holder">
                <p>There is an existing receipt for that transaction. Click below to overwrite it.</p>
                <button class="btn btn-warning" id="overwrite_receipt">Overwrite Receipt</button>
              </div>
              <div class='controls' id='create_controls'>         
                <hr/>
                <p class='card-text'>Use the following form to add items to the above receipt</p>
                <div class="mb-3">
                  <form class="row mb-3 align-items-center">
                    <div class="col-sm ">
                      <label class="" for="receipt_description">Item Name</label>
                      <input type="text" class="form-control" id="receipt_description" placeholder="Description">
                    </div>
                    <div class="col-sm ">
                      <label class="" for="receipt_quantity">Quantity</label>
                      <input type="number" class="form-control" id="receipt_quantity" placeholder="Quantity" value="1">
                    </div>
                  </form>
                  <form class="row mb-3 align-items-center ">
                    <div class="col-sm">
                      <label class="" for="receipt_units">Units</label>
                      <select class='form-select' id='receipt_units'>
                        <option value=''>Items</option>
                        <option value='kg'>kg</option>
                        <option value='g'>g</option>
                      </select>
                    </div>
                    <div class="col-sm ">
                      <label class="" for="receipt_amount">Price per unit</label>
                      <input type="number" class="form-control" id="receipt_amount" placeholder="Price">
                    </div>
                  </form>
                </div>               
                <div class="row">
                  <div class="mb-3">
                    <button class="btn btn-info" id='add_item' value='Add Item to Receipt'>Add Item to Receipt</button>                  
                  </div>
                  <div class="mb-3" id="subtotal_incorrect_holder">
                    <p>The subtotal does not match the total.</p>
                    <p><span class="badge bg-warning" id='subtotal_difference'></span></p>
                  </div>
                  <div class="mb-3">
                    <button class="btn btn-primary" id='subtotal_receipt' value='Subtotal Receipt' >Subtotal Receipt</button>
                    <button class="btn btn-success"  id='create_receipt' value="Create Receipt">Create Receipt</button>                
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer text-muted"><?php echo FOOTER;?></div>
          </div>
        </div>

        <!--MANAGE RECEIPT-->
        <div class="col mb-3" id="manage_receipt_holder">
          <div class="card text-center">
            <div class="card-header">Manage Receipt</div>
            <div class="card-body">
              <p class='card-text'>Use this option to manage an existing receipt that you have already attached to a transaction</p> 
              <div class="mb-3">
                <select id='receipt_select' class='form-select'>
                  <option selected>Select a receipt</option>
                </select>
              </div>
              <div class='controls' id='manage_controls'>   
                <hr/>
                <p class='card-text'>Use the following options to view, validate, or remove the receipt.</p>
                <div class="row">
                  <div class="mb-3">
                    <button class="btn btn-warning" id='receipt_validate' value='Validate Receipt'>Validate Receipt</button>
                    <button class="btn btn-outline-danger" id='receipt_delete' value="Delete Receipt">Delete Receipt</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer text-muted"><?php echo FOOTER;?></div>
          </div>
        </div>

        <!--Receipt Holder-->
        <div class="col mb-3" id="receipt_holder">
          <div class="card text-center" >
            <div class="card-header">Receipt</div>
            <div class="card-body">
              <pre class="card-text" id='items_readout'></pre>
            </div>    
            <div class="card-footer text-muted"><?php echo FOOTER;?></div>
          </div>
        </div>
      </div>

      <div class="row">
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
  <script  src='assets/receipts.js'></script>
</html>
