<?php
	$link = mysql_connect("localhost", "webuser", "codatanl") or die("Error: connection failure: " . mysql_error());
	mysql_select_db("col2005ac") or die("Error: could not select database");
?>