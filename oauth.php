<?php
	require "conn.php";
	$client_secret = get_data($conn, "client_secret");;

	#Data comes back in Server:
	if( isset($_GET['code']) ) {
		#This is the first return from the website
		$temporary_code = $_GET['code'];
		if( isset($_GET['state']) ) {
			$validate_state = $_GET['state'];
		}else {
			$validate_state = "Not found in request";
		}
		$current_state = get_data($conn, "state");
		if( $current_state == $validate_state ) {
			#State is correct, store token for next step
			send_data($conn, "temporary_code", $temporary_code);
			echo "<br/> That all seems okay - move onto the next step<br/>";
			echo "<a href='get_access_token.php'>Click here to exchange temporary code for an access token</a>";
		}else {
			echo "<br/>The state tokens do not match $validate_state was not accepted";
		}
	}

?>
