<?php
	require "conn.php";

	/*
	This handles incoming "Transaction Created" webhook requests
	*/

	$op = [];
	$op['response'] = "success";
	$op['post_data'] = $_POST['data'];
	$input = file_get_contents("php://input");
	$hook = json_decode($input, true);

	$op['php_input'] = $input;

	//Hook contains type
	if( $hook['type'] == "transaction.created") {
		$op['type'] = "transaction.created";
		$tx = $hook['data'];
		$created = date("Y-m-d H:i:s", strtotime($tx['created']));
		$settled = date("Y-m-d H:i:s", strtotime($tx['settled']));
		$send = send_transaction($conn, $tx['account_id'], $created, $settled, $tx['amount'], $tx['description'], $tx['merchant'], $tx['category'], $tx['id'], $tx['notes']);
		$op['sent_to_table'] = $send;

	}else {
		$op['type'] = "unknown";
		$op['info'] = "This API did now know what to do to with this webhook - input has been parsed and not found to match the schema";
		//$date = date("Y-m-d\TH:i:s\Z"); - 3339 format

		//Send the failed webhook to database (for debugging)
		$date = date("Y-m-d H:i:s");
		$type = $hook['type'];
		$data = json_encode($hook);
		$send_failed_webhook = send_failed_webhook($conn, $date, $type, $data);
		if( $send_failed_webhook ) {
			$op['saved'] = "Webhook data was saved";
		}else {
			$op['saved'] = "Webhook data could not be saved";
		}
	}

	$f = fopen("/var/www/api/banking/monzo/debug/webhook_debug.json", "w");
	if( fwrite($f, json_encode($op, JSON_PRETTY_PRINT)) ) {
		$op['written'] = "File written";
 	}else {
		$op['written'] = "Could not write";
	}
	fclose($f);

	echo json_encode($op, JSON_PRETTY_PRINT);

?>
