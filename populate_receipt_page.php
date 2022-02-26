<?php
	
	require "conn.php";
	$op['name'] = get_data($conn, "preferred_name");

  if( isset($_POST['limit']) ) {
    $limit = $_POST['limit'];
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