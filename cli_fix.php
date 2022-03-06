<?php
	//CLI fix so that arguments can be imported
	parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);

?>