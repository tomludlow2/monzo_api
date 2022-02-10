<?php
	
	require "conn.php";
	$client_id = get_data($conn, "client_id");
	$redirect_uri = get_data($conn, "redirect_uri");
	$response_type = "code";
	$state = get_data($conn, "state");

	$url = "https://auth.monzo.com/?client_id=$client_id&redirect_uri=$redirect_uri&response_type=code&state=$state";
?>

<html>

	<head>

	</head>

	<body>
		<h1>Monzo RPi Integration</h1>
		<p>Use this webpage to launch the authentication system for monzo access to the raspberry pi API</p>
		<a href='<?php echo $url;?>'>Click Here to Begin</a>

	</body>

</html>