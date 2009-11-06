<?php
header('Content-Type: text/xml');

function getResult($query) {
	$query = stripslashes(urldecode($query)) ;
	if ($query == "") {
		return true;
	}
	
	require_once "ac_config.php" ;
	require_once "connect_to_database.php" ;
	$result = mysql_query($query) or die (mysql_error());
	$number_of_records = mysql_num_rows($result) ;
	if ($number_of_records == 0) {
		return "<record>-</record>";
	} else {
		$records = "" ;
		for ($i = 0 ; $i < $number_of_records ; $i++) {
			$row = mysql_fetch_row($result) ;
			$records .= "<record>".$row[0]."</record>";
		}
		return $records;
	}
}

?>
<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'; ?>
<response>
  <records>
  <?php echo getResult($_GET['q']);?>
  </records>
</response>