<?php
	include "ac_config.php" ;
	$link = mysql_connect($sql_server,$sql_user_name,$sql_password) or die("Error: connection failure: " . mysql_error());
	mysql_select_db($sql_database) or die("Error: could not select database");
?>