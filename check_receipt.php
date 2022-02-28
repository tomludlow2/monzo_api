<?php
	$PAGE_TITLE = "Check Receipt";
	
	/*
		=======================================================
		Monzo API & PHP Integration
			-GH:		https://github.com/tomludlow2/monzo_api
			-Monzo:		https://docs.monzo.com/

		Created By:  	Tom Ludlow   tom.m.lud@gmail.com
		Date:			Feb 2022

		Tools / Frameworks / Acknowledgements 
			-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
				(https://getbootstrap.com/docs/5.1/about/license/)
			-jQuery:		MIT License, (C) 2019 JS Foundation 
				(https://jquery.org/license/)
			-Monzo Developer API
		========================================================
			file_name:  check_receipt.php
			function:	checks a receipt status locally + remotely
			arguments (default first):	
				-transaction_id:	The transaction ID
			response:
				-json encoded data with relevant transaction
				-look for receipt_found=true and status=200
	*/


	//Connect and load info
	require "conn.php";
	$access_token = get_data($conn, "access_token");
	$op = [];
	$op['function'] = "check_receipt";
	if( isset($_REQUEST['transaction_id'])) {
		$id = $_REQUEST['transaction_id'];
		$op['transaction_id'] = $id;
		if( preg_match('/tx_[a-zA-Z0-9]{22}/', $id) ) {

		}else {
			$op['error'] = "Invalid transaction_id format";
			$op['status'] = 400;
			die(json_encode($op));
		}	
	}else {
		$op['error'] = "No transaction_id provided";
		$op['status'] = 400;
		die(json_encode($op));
	}

	//Query the local DB for the local information held about the transaction
	$query = "SELECT `monzo_receipts`.`receipt_id`, `monzo_receipts`.`transaction_id`, `monzo_transactions`.`amount`, `monzo_transactions`.`date_created`, `monzo_receipts`.`content`, `monzo_transactions`.`description` FROM `monzo_receipts` INNER JOIN `monzo_transactions` ON `monzo_receipts`.`transaction_id` = `monzo_transactions`.`transaction_id` WHERE `monzo_receipts`.`transaction_id`='" . $op['transaction_id'] . "'";
	$res = mysqli_query($conn, $query);
	if( $res ) {
		if( mysqli_num_rows($res) >= 1 ) {
			$op['receipt_found'] = true;							
			while($r = mysqli_fetch_assoc($res)) {
				$receipt_id = $r['receipt_id'];
				$op['receipt_id'] = $r['receipt_id'];
				$op['content'] = $r['content'];
				$op['local_db'] = "success";
				$op['human_amount'] = "£" . number_format($r['amount']*-0.01, 2);
				$op['trans_id'] = $r['transaction_id'];
				$op['created'] = $r['date_created'];
				$op['description'] = $r['description'];
				$op['amount'] = $r['amount'];
			}
		}else {
			$receipt_id = null;
			$op['receipt_found'] = false;
		}
	}else{
		$op['error'] = "first_db_error";
		$op['status'] = 500;
		$op['mysqli'] = mysqli_error($conn);
		die(json_encode($op));
	}

	if( $op['receipt_found'] ) {
		//Now check that the information on monzo matches. 
		$authorisation = "Authorization: Bearer $access_token";
		$url = "https://api.monzo.com/transaction-receipts/?external_id=$receipt_id";
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$headers = array(
	   		"Accept: application/json",
	   		'Content-Type:application/x-www-form-urlencoded',
	   		$authorisation,
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($curl);
		$resp = json_decode($response, true);
		$op['status'] = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
		curl_close($curl);

		$op['monzo_response'] = $resp;
	}
	echo json_encode($op);
	
?>