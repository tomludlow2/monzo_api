<?php
	/*
		=======================================================
		Monzo API & PHP Integration
			-GH:			https://github.com/tomludlow2/monzo_api
			-Monzo:			https://docs.monzo.com/

		Created By:  	Tom Ludlow   tom.m.lud@gmail.com
		Date:			Feb 2022

		Tools / Frameworks / Acknowledgements 
			-Bootstrap (inc Icons):	MIT License, (C) 2018 Twitter 
				(https://getbootstrap.com/docs/5.1/about/license/)
			-jQuery:		MIT License, (C) 2019 JS Foundation 
				(https://jquery.org/license/)
			-Monzo Developer API
		========================================================
			file_name:  conn.php
			this file holds a variety of database connection
			functions that are critical.
	*/

	//Import credentials (from credentials.example.php)
	if( php_sapi_name() == "cli" ) {
		require("cli_fix.php");
		$SESSION_EXEMPT = 1;
	}
	require("credentials.php");
	require("session_management.php");

	//Define system constants
	define("TITLE", "RPi Monzo Integration");
	define("FOOTER", "Monzo API Integration");

	$conn = mysqli_connect($conn_db, $conn_user, $conn_pass, "money");

	function send_data($conn,$key, $val) {	
		$rtn = null;
		$select_query = "SELECT * FROM `money`.`monzo_auth` WHERE `monzo_key`='$key'";
		$select_res = mysqli_query($conn, $select_query);
		if( $select_res ) {
			$num = mysqli_num_rows($select_res);
			$next_query = "";
			if( $num == 0 ) {
				$insert_query = "INSERT INTO `money`.`monzo_auth` (`monzo_key`,`monzo_val`) VALUES ('$key','$val')";
				$next_query = $insert_query;
			}else {
				$update_query = "UPDATE `money`.`monzo_auth` SET `monzo_val`='$val' WHERE `monzo_key`='$key'";
				$next_query = $update_query;
			}
			$next_res = mysqli_query($conn, $next_query);
			if( $next_res ) {
				$rtn = 1;
			}else {
				$rtn = 0;
			}
		}else {
			$rtn = 0;
		}
		return $rtn;
	}

	function get_data($conn,$key) {
		$select_query = "SELECT * FROM `monzo_auth` WHERE `monzo_key`='$key'";
		$select_res = mysqli_query($conn, $select_query);
		$rtn = null;
		if( $select_res ) {
			$num = mysqli_num_rows($select_res);
			if( $num == 1 ) {
				while($r = mysqli_fetch_assoc($select_res) ) {
					$rtn = $r['monzo_val'];
				}
			}else {
				$rtn = 0;
			}
			
		}else {
			$rtn = "error";
			$rtn .= $select_query ."ERR:" . mysqli_error($conn);
		}
		return $rtn;
	}

	function send_daily_balance($conn, $acc_id, $balance, $total_balance) {
		$rtn = null;
		$date = date("Y-m-d");
		$select_query = "SELECT * FROM `money`.`monzo_daily_balances` WHERE `date`='$date' AND `account_id`='$acc_id'";
		$select_res = mysqli_query($conn, $select_query);
		if( $select_res ) {
			$num = mysqli_num_rows($select_res);
			$next_query = "";
			if( $num == 0 ) {
				$insert_query = "INSERT INTO `money`.`monzo_daily_balances` (`account_id`,`date`, `balance`, `total_balance`) VALUES ('$acc_id','$date', '$balance', '$total_balance')";
				$next_query = $insert_query;
			}else {
				$update_query = "UPDATE `money`.`monzo_daily_balances` SET `balance`='$balance', `total_balance`='$total_balance' WHERE `date`='$date' AND `account_id`='$acc_id'";
				$next_query = $update_query;
			}
			$next_res = mysqli_query($conn, $next_query);
			if( $next_res ) {
				$rtn = 1;
			}else {
				$rtn = 0;
			}
		}else {
			$rtn = 0;
		}
		return $rtn;
	}

	function send_daily_pot_balance($conn, $acc_id, $pot_id, $balance) {
		$rtn = null;
		$date = date("Y-m-d");
		$select_query = "SELECT * FROM `money`.`monzo_pots_daily_balances` WHERE `date`='$date' AND `account_id`='$acc_id' AND `pot_id`='$pot_id'";
		$select_res = mysqli_query($conn, $select_query);
		if( $select_res ) {
			$num = mysqli_num_rows($select_res);
			$next_query = "";
			if( $num == 0 ) {
				$insert_query = "INSERT INTO `money`.`monzo_pots_daily_balances` (`account_id`, `pot_id`,`date`, `balance`) VALUES ('$acc_id', '$pot_id', '$date', '$balance')";
				$next_query = $insert_query;
			}else {
				$update_query = "UPDATE `money`.`monzo_pots_daily_balances` SET `balance`='$balance' WHERE `date`='$date' AND `account_id`='$acc_id' AND `pot_id`='$pot_id'";
				$next_query = $update_query;
			}
			$next_res = mysqli_query($conn, $next_query);
			if( $next_res ) {
				$rtn = 1;
			}else {
				$rtn = 0;
			}
		}else {
			$rtn = 0;
		}
		return $rtn;
	}

	function send_transaction($conn, $acc_id, $date_c, $date_s, $amount, $description, $merchant_id, $category, $trans_id, $notes) {
		#To be tested
		$query = "INSERT INTO `monzo_transactions` (`id`, `account_id`, `date_created`, `date_settled`, `amount`, `description`, `merchant_id`, `category`, `transaction_id`, `notes`) VALUES ";
		$query .= "(NULL, '$acc_id', '$date_c', '$date_s', '$amount', '" . mysqli_real_escape_string($conn, $description) . "', '$merchant_id', '$category', '$trans_id', '" . mysqli_real_escape_string($conn, $notes) . "')";
		$res = mysqli_query($conn, 	$query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function send_transaction_obj($conn, $tx) {
		#To be tested
		$query = "INSERT INTO `monzo_transactions` (`id`, `account_id`, `date_created`, `date_settled`, `amount`, `description`, `merchant_id`, `category`, `transaction_id`, `notes`) VALUES ";
		$query .= "(NULL, '" . $tx['account_id'] . "', '" . $tx['date_created'] . "' , '" . $tx['date_settled'] . "', '" . $tx['amount']. "', '" . mysqli_real_escape_string($conn, $tx['description']) . "', '" . $tx['merchant_id'] . "', '" . $tx['category'] . "', '" . $tx['transaction_id'] . "', '" . mysqli_real_escape_string($conn, $tx['notes']) .  "')";
		$res = mysqli_query($conn, 	$query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function send_all_transactions($conn, $db) {
		#Only used when the all_transactions.php file sends over the data
		#Assumes an empty table
		$rtn = null;
		$query_header = "INSERT INTO `monzo_transactions` (`id`, `account_id`, `date_created`, `date_settled`, `amount`, `description`, `merchant_id`, `category`, `transaction_id`, `notes`) VALUES ";
		foreach ($db as $tx) {
			$line = "(NULL, '" . $tx['account_id'] . "', '" . $tx['date_created'] . "' , '" . $tx['date_settled'] . "', '" . $tx['amount']. "', '" . mysqli_real_escape_string($conn, $tx['description']) . "', '" . $tx['merchant_id'] . "', '" . $tx['category'] . "', '" . $tx['transaction_id'] . "', '" . mysqli_real_escape_string($conn, $tx['notes']) .  "')\n,";
			$query_header .= $line;
		}

		$query_header = substr($query_header, 0 ,-1);
		$res = mysqli_query($conn, $query_header);

		//For debug purposes - write out some files:
		$file_1 = fopen("/var/www/api/banking/monzo/debug/sql_escape.sql", "w") or die(print_r(error_get_last(),true));
		fwrite($file_1, $query_header);
		fclose($file_1);

		$file_3 = fopen("/var/www/api/banking/monzo/debug/transactions.json", "w");
		fwrite($file_3, json_encode($db));
		fclose($file_3);

		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);			
		}

	}

	function get_last_transaction($conn) {
		$query = "SELECT `transaction_id` FROM `monzo_transactions` ORDER BY `date_created` DESC LIMIT 1";
		$res = mysqli_query($conn, $query);
		$rtn = [];
		if($res) {
			$rtn['query_success'] = 1;
			while($r = mysqli_fetch_assoc($res) ) {
				$rtn['transaction_id'] = $r['transaction_id'];
			}
		}else {
			$rtn['query_success'] = 0;
			$rtn['error'] = mysqli_error($conn);
		}

		return $rtn;
	}

	function get_recent_transactions($conn, $limit) {
		$query = "SELECT `date_created`, `amount`, `description`, `transaction_id` FROM `monzo_transactions` WHERE `amount`<0 AND `description` NOT REGEXP 'pot_' ORDER BY `date_created` DESC LIMIT $limit";
		$res = mysqli_query($conn, $query);
		$rtn = [];
		if($res) {
			$rtn['query_success'] = 1;
			$transactions = [];
			while($r = mysqli_fetch_assoc($res) ) {
				$tx = [];
				$tx['created'] = $r['date_created'];
				$tx['amount'] = $r['amount'];
				$tx['description'] = $r['description'];
				$tx['trans_id'] = $r['transaction_id'];
				array_push($transactions, $tx);
			}
			$rtn['transactions'] = $transactions;
		}else {
			$rtn['query_success'] = 0;
			$rtn['error'] = mysqli_error($conn);
		}
		return array_reverse($rtn);
	}

	function get_recent_receipts($conn, $limit) {
		$query = "SELECT `monzo_receipts`.`receipt_id`, `monzo_receipts`.`transaction_id`, `monzo_transactions`.`amount`, `monzo_transactions`.`date_created`, `monzo_receipts`.`content`, `monzo_transactions`.`description` FROM `monzo_receipts` INNER JOIN `monzo_transactions` ON `monzo_receipts`.`transaction_id` = `monzo_transactions`.`transaction_id` ORDER BY `monzo_transactions`.`date_created` DESC LIMIT $limit";
		$res = mysqli_query($conn, $query);
		$rtn = [];
		if($res) {
			$rtn['query_success'] = 1;
			$transactions = [];
			while($r = mysqli_fetch_assoc($res) ) {
				$tx = [];
				$tx['created'] = $r['date_created'];
				$tx['amount'] = $r['amount'];
				$tx['description'] = $r['description'];
				$tx['trans_id'] = $r['transaction_id'];
				$tx['receipt_id'] = $r['receipt_id'];
				$tx['content'] = $r['content'];
				array_push($transactions, $tx);
			}
			$rtn['transactions'] = $transactions;
		}else {
			$rtn['query_success'] = 0;
			$rtn['error'] = mysqli_error($conn);
		}
		return ($rtn);
	}

	function send_failed_webhook($conn, $date, $type, $data) { 
		$query = "INSERT INTO `monzo_webhooks` (`id`, `date`, `type`, `data`) VALUES (NULL, '$date', '$type', '";
		$query .= mysqli_real_escape_string($conn, $data) . "')";
		$res = mysqli_query($conn, $query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function send_receipt($conn, $receipt_id, $transaction_id, $content) {
		$query = "INSERT INTO `monzo_receipts` (`id`, `receipt_id`, `transaction_id`, `content`) VALUES (NULL, '$receipt_id', '$transaction_id', '";
		$query .= mysqli_real_escape_string($conn, $content) . "') ON DUPLICATE KEY UPDATE `content`='" . mysqli_real_escape_string($conn, $content) . "';";
		$res = mysqli_query($conn, $query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function nullify_receipt($conn, $receipt_id, $item) {
		$query = "UPDATE `monzo_receipts` SET `content`= '" . mysqli_real_escape_string($conn, $item) . "' WHERE `receipt_id` ='$receipt_id'";
		$res = mysqli_query($conn, $query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function delete_receipt($conn, $receipt_id) {
		$query = "DELETE FROM `monzo_receipts` WHERE `receipt_id`='$receipt_id')";
		$res = mysqli_query($conn, $query);
		if( $res ) {
			$rtn = 1;
		}else {
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}


	function get_balance_table($conn) {
		//Custom balance query including LEFT JOIN to collect comments

		$query = "SELECT B.`id`, B.`date`, B.`balance`, B.`total_balance`, C.`comment` FROM `monzo_daily_balances` B LEFT JOIN (
    SELECT `comment`, `table_id` FROM `monzo_table_comments` WHERE `table_name`='daily_balance')
    C on B.`id`= C.`table_id` ORDER BY B.`date` DESC";
		$res = mysqli_query($conn, $query);
		if( $res ) {
			$rtn['status'] = 200;
			$rows = [];
			while ($r = mysqli_fetch_assoc($res) ) {
				$r['human_date'] = date("d M Y", strtotime($r['date']));
				array_push($rows, $r);

			}
			$rtn['count'] = count($rows);
			$rtn['rows'] = $rows;
		}else {
			$rtn['status'] = 500;
			$rtn = mysqli_error($conn);
		}
		return $rtn;
	}

	function update_comment($conn,$t_id, $i_id, $c) {
		$query = "SELECT `id` FROM `monzo_table_comments` WHERE `table_name`='$t_id' AND `table_id`='$i_id'";
		$res_1 = mysqli_query($conn, $query);
		$rtn = [];

		if($res_1) {
			if(mysqli_num_rows($res_1) > 0 ) {
				$q2 = "UPDATE `monzo_table_comments` SET `comment`='$c' WHERE `table_name`='$t_id' AND `table_id`='$i_id'";
			}else {
				$q2 = "INSERT INTO `monzo_table_comments` (`id`, `table_name`, `table_id`, `comment`) VALUES (NULL, '$t_id', '$i_id', '$c')";
			}
			$res2 = mysqli_query($conn, $q2);
			if($res2) {
				$rtn['status'] = 200;
			}else {
				$rtn['status'] = 500;
				$rtn['error'] = "Q2" .mysqli_error($conn);
			}
		}else {
			$rtn['status'] = 500;
			$rtn['error'] = "Q1" .mysqli_error($conn);
		}
		return $rtn;
	}


	function get_all_transactions($conn, $limit) {
		$pots_lookup = [];
		global $GENERATE_TABLE_RAW_POTS;
		foreach($GENERATE_TABLE_RAW_POTS as $pot) {
			$pots_lookup[$pot->pot_id] = $pot->pot_name;
		};
		$query = "SELECT T.`id` as `row_id`, T.`transaction_id`, T.`date_created`, T.`date_settled`, T.`amount`, T.`description`, T.`merchant_id`, T.`category`, T.`notes`, C.`comment`  FROM `monzo_transactions` T LEFT JOIN (SELECT `comment`, `table_id` FROM `monzo_table_comments` WHERE `table_name`='monzo_transactions')C on T.`transaction_id`= C.`table_id` ORDER BY T.`date_created` DESC LIMIT $limit";
		$res = mysqli_query($conn, $query);
		$rtn = [];
		$rtn['query'] = $query;		
		if($res) {
			$rtn['status'] = 200;
			$rtn['total_rows'] = mysqli_num_rows($res);
			$transactions = [];
			while($r = mysqli_fetch_assoc($res) ) {
				if( preg_match('/^pot_[a-zA-Z0-9]{22}/', $r['description']) ) {
					$old_desc = $r['description'];
					$r['description'] = $pots_lookup[$old_desc];
					$r['pot_transaction'] = 1;
				}else {
					$r['pot_transaction'] = 0;
				}
				$r['human_amount'] = "£" . number_format($r['amount']/100, 2, '.', '');
				array_push($transactions, $r);
			}
			$rtn['rows'] = $transactions;
		}else {
			$rtn['status'] = 500;
			$rtn['error'] = mysqli_error($conn);
		}		
		return $rtn;
	}

	$GENERATE_TABLE_RAW_POTS = json_decode(get_data($conn, "pots_list"));

?>
