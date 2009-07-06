<?php
	$online_or_offline_version = "offline";
	// SQL settings
	if ($online_or_offline_version == "online") {
		$sql_server = "biodiversity.cs.cf.ac.uk" ;
		$sql_user_name = "sp2000" ;
		$sql_password = "Or0bus" ;
		$sql_database = "CoL2007AC" ;
	} else if ($online_or_offline_version == "offline") {
		$sql_server = "localhost:3000" ;
		$sql_user_name = "root" ;
		$sql_password = "" ;
		$sql_database = "col2008ac" ;
	} else {
		die ("Error: config file does not specify online or offline version") ;
	}
?>
