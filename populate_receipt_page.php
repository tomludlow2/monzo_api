<?php

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
      file_name:  populate_receipt_page.php
      function:   get the info (async) for the receipt page
      arguments (default first):  
        - limit:    25
  */

	require "conn.php";
	$op['name'] = get_data($conn, "preferred_name");

  if( isset($_REQUEST['limit']) ) {
    $limit = $_REQUEST['limit'];
  }else{
    $limit = 25;
  }

  $transactions_data = get_recent_transactions($conn, 25);
  $receipt_data = get_recent_receipts($conn, 25);
  
  foreach($transactions_data['transactions'] as $i => $t) {
    $transactions_data['transactions'][$i]['human_amount'] = "£" . number_format($t['amount']*-0.01, 2);
  };

  foreach($receipt_data['transactions'] as $i => $t) {
    $receipt_data['transactions'][$i]['human_amount'] = "£" . number_format($t['amount']*-0.01, 2);
  };

 
  $op['function'] = "populate_receipt_data";
  if( $transactions_data['query_success'] && $receipt_data['query_success'] ) $op['success'] = 1;

  $op['transactions_data'] = $transactions_data;
  $op['receipt_data'] = $receipt_data;

  echo( json_encode($op));
?>