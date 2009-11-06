<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Common name details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<?php
	if (isset($_REQUEST["search_query"])) {
		$search_query = $_REQUEST["search_query"] ;
	} else {
		$search_query = "" ;
	}
	if (isset($_REQUEST["search_page"])) {
		$search_page = $_REQUEST["search_page"] ;
	} else {
		$search_page = "" ;
	}
	if (isset($_REQUEST["first_record_shown"])) {
		$first_record_shown = $_REQUEST["first_record_shown"] ;
	} else {
		$first_record_shown = "" ;
	}
	if (isset($_REQUEST["number_of_records_shown_per_page"])) {
		$number_of_records_shown_per_page = $_REQUEST["number_of_records_shown_per_page"] ;
	} else {
		$number_of_records_shown_per_page = "" ;
	}
	if (isset($_REQUEST["number_of_records_found"])) {
		$number_of_records_found = $_REQUEST["number_of_records_found"] ;
	} else {
		$number_of_records_found = "" ;
	}
	if (isset($_REQUEST["search_kingdom"])) {
		$search_kingdom = $_REQUEST["search_kingdom"] ;
	} else {
		$search_kingdom = "" ;
	}
	if (isset($_REQUEST["search_phylum"])) {
		$search_phylum = $_REQUEST["search_phylum"] ;
	} else {
		$search_phylum = "" ;
	}
	if (isset($_REQUEST["search_class"])) {
		$search_class = $_REQUEST["search_class"] ;
	} else {
		$search_class = "" ;
	}
	if (isset($_REQUEST["search_order"])) {
		$search_order = $_REQUEST["search_order"] ;
	} else {
		$search_order = "" ;
	}
	if (isset($_REQUEST["search_family"])) {
		$search_family = $_REQUEST["search_family"] ;
	} else {
		$search_family = "" ;
	}
	if (isset($_REQUEST["search_genus"])) {
		$search_genus = $_REQUEST["search_genus"] ;
	} else {
		$search_genus = "" ;
	}
	if (isset($_REQUEST["search_species"])) {
		$search_species = $_REQUEST["search_species"] ;
	} else {
		$search_species = "" ;
	}
	if (isset($_REQUEST["search_infraspecies"])) {
		$search_infraspecies = $_REQUEST["search_infraspecies"] ;
	} else {
		$search_infraspecies = "" ;
	}
	if (isset($_REQUEST["search_common_name"])) {
		$search_common_name = $_REQUEST["search_common_name"] ;
	} else {
		$search_common_name = "" ;
	}
	if (isset($_REQUEST["search_distribution"])) {
		$search_distribution = $_REQUEST["search_distribution"] ;
	} else {
		$search_distribution = "" ;
	}
	if (isset($_REQUEST["search_simple"])) {
		$search_simple = $_REQUEST["search_simple"] ;
	} else {
		$search_simple = "" ;
	}
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = $_REQUEST["search_mode"] ;
	} else {
		$search_mode = "" ;
	}
	
	// get commmon name to display
	if (isset($_REQUEST["common_name"])) {
		$common_name = stripslashes(urldecode($_REQUEST["common_name"])) ;
	} else {
		$common_name = "" ;
	}
	
	// connect to database
	include "connect_to_database.php" ;
	
	$common_names_query = "SELECT `common_names`.`common_name` , 
								  `common_names`.`language`, 
								  `common_names`.`country`, 
								  `common_names`.`reference_id`, 
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
	  WHERE `common_names`.`common_name` = '" . addslashes($common_name) . "' 
	    AND `common_names`.`name_code` = `scientific_names`.`name_code` 
		AND `common_names`.`database_id` = `databases`.`record_id` " ;
	$result = mysql_query($common_names_query) or die("Query failed : " . mysql_error());
	$number_of_records = mysql_num_rows($result);
	mysql_free_result($result) ;
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="moveMenu();" onScroll="moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td valign=top> 
      <?php
	include "menu.php" ;
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
		echo "<p><br>$number_of_records records found for $common_name:</p>" ;
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
	
	$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' align=right title='Click here to show literature reference(s)'>" ;
	
	for ($i = 1; $i <= $number_of_records; $i++) {
		$result = mysql_query($common_names_query . " LIMIT " . ($i-1) . ",1") or die("Query failed : " . mysql_error());
		$row = mysql_fetch_row($result);
		mysql_free_result($result) ;
		$name = $row[0] ;
		$language = $row[1] ;
		$country = $row[2] ;
		$reference_id = $row[3] ;
		$record_id = $row[4] ;
		$genus = $row[5] ;
		$species = $row[6] ;
		$infraspecies_marker = $row[7] ;
		$infraspecies = $row[8] ;
		$db_name = $row[9] ;
		$db_fullname = $row[10] ;
		
		if ($language == "") {
			$language = "&#150;" ;
		}
		if ($country == "") {
			$country = "&#150;" ;
		}
		$scientific_name = "<a href='JavaScript:showSpeciesDetails(\"$record_id\")'>" ;
		$scientific_name .= "<i>" . $genus . " " . $species . "</i>" ;
		if ($infraspecies == "none") {
			$infraspecies = "" ;
		}
		if ($infraspecies != "") {
			if ($infraspecies_marker != "") {
				$scientific_name .= " " . $infraspecies_marker ;
			}
			$scientific_name .= " <i>" . $infraspecies . "</i>" ;
		}
		$scientific_name .= "</a>" ;
		
		if ($db_name != $db_fullname) {
			$db_fullname .= " [$db_name]" ;
		}
		$database = "<a href='JavaScript:showDatabaseDetails(\"$db_name\")'>$db_fullname</a>" ;

		$reference = "" ;
		$number_of_references = 0 ;
		if ($reference_id != "") {
			$reference_query = "SELECT *
								FROM `references`
								WHERE `references`.`record_id` = '$reference_id' " ;
			$result = mysql_query($reference_query) or die("Query failed : " . mysql_error());
			$number_of_references = mysql_num_rows($result);
			mysql_free_result($result) ;
			
		}
		if ($number_of_references > 0) {
			
			$label = $number_of_references . " literature reference" ;
			if ($number_of_references != 1) {
				$label .= "s" ;
			}
			$reference = "<a href=\"JavaScript:showCommonNameReferenceDetails($reference_id,'$name')\" " .
			  "onMouseOver=\"return showStatus('$label')\" " .
			  "onMouseOut=\"return showStatus('')\">$book_icon</a>" ;
		}

		include "show_common_name_table.php" ;
		if ($i < $number_of_records) {
			echo "<br>" ;
		}
	}
	
	mysql_close($link) ;
?>
                        <p><br>
                        </p>
                        <p align=center>
<?php
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referring_page = $_SERVER['HTTP_REFERER'] ;
	} else {
		$referring_page = "" ;
	}
	if (strpos($referring_page,"search_results") > 0) {
		echo "<a href='JavaScript:document.back_to_search_results.submit()'>Back to search results</a> | " .
			  "<a href='JavaScript:document.new_search.submit()'>New search</a>" ;
	} elseif (strpos($referring_page,"show_database_details") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to database details</a>" ;
	} else if (strpos($referring_page,"show_species_details.php") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to species details</a>" ;
	} else {
		echo "<a href='JavaScript:history.back()'>Back to last page</a>" ;
	}
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
<form name="show_database" method="post" action="show_database_details.php">
  <input type="hidden" name="database_name">
</form>
<form name="show_species_details" method="post" action="show_species_details.php">
  <input type="hidden" name="record_id">
</form>
<form name="show_reference_details" method="post" action="show_reference_details.php">
  <input type="hidden" name="reference_id">
  <input type="hidden" name="name">
</form>
<form name="back_to_search_results" method="post" action="search_results.php">
  <input type="hidden" name="search_page" value="<?php echo $search_page ?>">
  <input type="hidden" name="search_query" value="<?php echo $search_query ?>">
  <input type="hidden" name="first_record_shown" value="<?php echo $first_record_shown ?>">
  <input type="hidden" name="number_of_records_shown_per_page" value="<?php echo $number_of_records_shown_per_page ?>">
  <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
  <input type="hidden" name="search_kingdom" value="<?php echo $search_kingdom ?>">
  <input type="hidden" name="search_phylum" value="<?php echo $search_phylum ?>">
  <input type="hidden" name="search_class" value="<?php echo $search_class ?>">
  <input type="hidden" name="search_order" value="<?php echo $search_order ?>">
  <input type="hidden" name="search_family" value="<?php echo $search_family ?>">
  <input type="hidden" name="search_genus" value="<?php echo $search_genus ?>">
  <input type="hidden" name="search_species" value="<?php echo $search_species ?>">
  <input type="hidden" name="search_infraspecies" value="<?php echo $search_infraspecies ?>">
  <input type="hidden" name="search_common_name" value="<?php echo $search_common_name ?>">
  <input type="hidden" name="search_distribution" value="<?php echo $search_distribution ?>">
  <input type="hidden" name="search_simple" value="<?php echo $search_simple ?>">
  <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
</form>
<form name="new_search" method="post" action="<?php echo urldecode($search_page) ?>">
  <input type="hidden" name="search_kingdom" value="<?php echo $search_kingdom ?>">
  <input type="hidden" name="search_phylum" value="<?php echo $search_phylum ?>">
  <input type="hidden" name="search_class" value="<?php echo $search_class ?>">
  <input type="hidden" name="search_order" value="<?php echo $search_order ?>">
  <input type="hidden" name="search_family" value="<?php echo $search_family ?>">
  <input type="hidden" name="search_genus" value="<?php echo $search_genus ?>">
  <input type="hidden" name="search_species" value="<?php echo $search_species ?>">
  <input type="hidden" name="search_infraspecies" value="<?php echo $search_infraspecies ?>">
  <input type="hidden" name="search_common_name" value="<?php echo $search_common_name ?>">
  <input type="hidden" name="search_distribution" value="<?php echo $search_distribution ?>">
  <input type="hidden" name="search_simple" value="<?php echo $search_simple ?>">
  <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
</form>
</div>
</body>
</html>