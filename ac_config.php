<?php
	// online (web) or offline(CD-ROM) version
	$online_or_offline_version = "online" ;
	
	// SQL settings
	if ($online_or_offline_version == "online") {
		$sql_server = "localhost" ;
		$sql_user_name = "webuser" ;
		$sql_password = "codatanl" ;
		$sql_database = "col2007ac" ;
	} else if ($online_or_offline_version == "offline") {
		$sql_server = "localhost:3000" ;
		$sql_user_name = "root" ;
		$sql_password = "" ;
		$sql_database = "col2007ac" ;
	} else {
		die ("Error: config file does not specify online or offline version") ;
	}
?>
