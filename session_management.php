<?php
	//Some functions are exempt from session
	if( !isset($SESSION_EXEMPT) ) {
		session_start();
		$AUTH_OK = 0;
		if( isset($_SESSION['authenticated']) ) {
			if( $_SESSION['authenticated'] == 1 ) {
				$AUTH_OK = 1;
			}
		}

		if( !$AUTH_OK) {
			destroy_session();
			require "credentials.php";
			header("Location: " . WEBROOT . "/local_login.php");
			die();
		}
	}

	function validate_session() {
		//User has passed an authentication process (either locally / remotely)
		session_start();
		$_SESSION['authenticated'] = 1;
	}

	function destroy_session() {
		session_start();
		session_unset();
		session_destroy();
	}

?>