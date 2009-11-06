<?php
	$link = mysql_connect("localhost:3000", "root", "") or die("Error: connection failure: " . mysql_error());
	mysql_select_db("col2005ac") or die("Error: could not select database");
?>