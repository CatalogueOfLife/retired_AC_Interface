<?php
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
	
	// get commmon name to display
	$this_common_name = "" ;
	if (isset($_REQUEST["name"])) {
		$this_common_name = trim(stripslashes(urldecode($_REQUEST["name"]))) ;
	}
	if ($this_common_name == "") {
		die("<p>Error: invalid or missing parameter</p>") ;
	}
	
	if (isset($_REQUEST["search_type"])) {
		$search_type = trim(stripslashes(urldecode($_REQUEST["search_type"]))) ;
	} else if (isset($_SESSION["ac_search_type"])) {
		$search_type = trim(stripslashes(urldecode($_SESSION["ac_search_type"]))) ;
	} else {
		$search_type = "" ;
	}
	$new_search_url = (($search_type == "") ? "search.php" : urldecode($search_type) . ".php") ;

	// connect to database
	include "includes/db_connect.php" ;
	
	$this_common_names_query = "SELECT DISTINCT `common_names`.`common_name` ,
								  `common_names`.`language`,
								  `common_names`.`country`,
								  `common_names`.`name_code`,
								  `scientific_names`.`record_id` ,
								  `scientific_names`.`genus` ,
								  `scientific_names`.`species` ,
								  `scientific_names`.`infraspecies_marker` ,
								  `scientific_names`.`infraspecies` ,
								  `databases`.`database_name` ,
								  `databases`.`database_full_name`
	  FROM `common_names` ,
	  	   `scientific_names` ,
		   `databases`
	  WHERE `common_names`.`common_name` = '" . addslashes($this_common_name) . "'
	    AND `common_names`.`name_code` = `scientific_names`.`name_code`
	    AND `common_names`.`name_code` LIKE BINARY `scientific_names`.`name_code`
		AND `common_names`.`database_id` = `databases`.`record_id`
		AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code`
		AND `scientific_names`.`name_code` LIKE BINARY `scientific_names`.`accepted_name_code`
	  ORDER BY `scientific_names`.`genus`,`scientific_names`.`species` ,`scientific_names`.`infraspecies` ,`common_names`.`language`,`common_names`.`country`,`databases`.`database_full_name`" ;
	$result = mysql_query($this_common_names_query) or die("Error: MySQL query failed");
	$number_of_records = mysql_num_rows($result);
	if ($number_of_records == 0) {
		die("<p>Error: invalid or missing parameter</p>") ;
	}
?>
<title>Catalogue of Life : 2009 Annual Checklist : <?php echo ucfirst($this_common_name) ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "includes/config.php" ;
	if ($online_or_offline_version == "offline") {
		include "cd_rom_version_icon.php" ;
	}
?>
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top>
<?php
	require_once "menu.php" ;
?>
    </td>
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0">
    </td>
    <td valign=top>
      <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366">
        <tr>
          <td>
            <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
              <tr>
                <td>
                  <table width="100%" border="0" cellspacing="0" cellpadding="10">
                    <tr>
                      <td>
                        <p class="formheader" align="center">Common name details</p>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                          <tr>
                            <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td><img src="images/blank.gif" border="0" height="2" width="1"></td>
                    </tr>
                  </table>
                  <table border="0" cellspacing="0" cellpadding="10">
                    <tr>
                      <td>
<?php
	if ($number_of_records > 1) {
		echo "<p><br>$number_of_records records found for $this_common_name:</p>" ;
		echo "<table><tr><td></td><img src=\"images/blank.gif\" width=\"1\" height=\"2\"></tr></table>" ;
	} else {
		echo "<br>" ;
	}
	
	$table_row_color = "" ;
	function getTableRowColor($old_color) {
		if ( $old_color != "#FAFCFE" ) {
			$new_color = "#FAFCFE" ;
		} else {
			$new_color = "#EAF2F7" ;
		}
		echo $new_color ;
		return $new_color ;
	}
	
	for ($i = 1; $i <= $number_of_records; $i++) {
		$result = mysql_query($this_common_names_query . " LIMIT " . ($i-1) . ",1") or die("Error: MySQL query failed");
		$row = mysql_fetch_row($result);
		$name = $row[0] ;
		$language = $row[1] ;
		$country = $row[2] ;
		$name_code = $row[3] ;
		$record_id = $row[4] ;
		$this_genus = $row[5] ;
		$this_species = $row[6] ;
		$this_infraspecies_marker = $row[7] ;
		$this_infraspecies = $row[8] ;
		$db_name = $row[9] ;
		$db_fullname = $row[10] ;
		
		$scientific_name = "<a href='show_species_details.php?record_id=$record_id'>" ;
		$scientific_name .= "<i>" . $this_genus . " " . $this_species . "</i>" ;
		if ($this_infraspecies == "none") {
			$this_infraspecies = "" ;
		}
		if ($this_infraspecies != "") {
			if ($this_infraspecies_marker != "") {
				$scientific_name .= " " . $this_infraspecies_marker ;
			}
			$scientific_name .= " <i>" . $this_infraspecies . "</i>" ;
		}
		$scientific_name .= "</a>" ;
		
		if ($db_name != $db_fullname) {
			$db_fullname .= " [$db_name]" ;
		}
		$database = "<a href='show_database_details.php?database_name=" . urlencode($db_name) . "'>$db_fullname</a>" ;

		$references_query = "SELECT `common_names`.`reference_id`
		  FROM `common_names`
		  WHERE `common_name` = '" . addslashes($this_common_name) . "'
		    AND `language` = '" . addslashes($language) . "'
		    AND `country` = '" . addslashes($country) . "'
		    AND `name_code` = '" . addslashes($name_code) . "'
		    AND `name_code` LIKE BINARY '" . addslashes($name_code) . "'" ;
		$references_result = mysql_query($references_query) or die("Error: MySQL query failed");
		$number_of_references = mysql_num_rows($references_result);
		$reference = "" ;
		if ($number_of_references > 0) {
			if ($number_of_references == 1) {
				$label = "literature reference" ;
			} else {
				$label = "$number_of_references literature references" ;
			}
			$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' align=right title='Click here to show the $label'>" ;
			$reference_url = "show_reference_details.php?name=" . urlencode($name) .
			  "&amp;" . "language=" . urlencode($language) .
			  "&amp;" . "country=" . urlencode($country) .
			  "&amp;" . "name_code=" . urlencode($name_code) ;
			$reference = "<a href=\"$reference_url\" onmouseover=\"return showStatus('$label')\" " .
			  "onmouseout=\"return showStatus('')\">$book_icon</a>" ;
		}

		if ($language == "") {
			$language = "&#150;" ;
		}
		if ($country == "") {
			$country = "&#150;" ;
		}
		include "show_common_name_table.php" ;
		if ($i < $number_of_records) {
			echo "<br>" ;
		}
	}
	mysql_close($link) ;
?>
                        <p align=center>
                        <br />
<script language="JavaScript" type="text/javascript">
	showBackLink() ;
</script>
<?php
	echo "<a href=\"$new_search_url\">New search</a>" ;
?>
                        </p>
                        <p><img src="images/blank.gif" width="505px" height="1" border="0"></p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
      </table>
    </td>
  </tr>
</table>
</div>
<?php include_once 'includes/gax.php'; ?>
</body>
</html>