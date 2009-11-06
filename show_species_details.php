<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
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

	function compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom) {
		if ($kingdom == "Viruses") {
			$scientific_name = $species ;
		} else {
			$scientific_name = "<i>$genus $species</i>" ;
			if ($infraspecies != "") {
				if ($infraspecies_marker != "") {
					$scientific_name .= " $infraspecies_marker" ;
				}
				$scientific_name .= " <i>$infraspecies</i>"  ;
			}
			if ($author != "") {
				$scientific_name .= " $author" ;
			}
		}
		return $scientific_name ;
	}
	
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
	if (isset($_REQUEST["current_hit"])) {
		$current_hit = $_REQUEST["current_hit"] ;
	} else {
		$current_hit = "" ;
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
	if (isset($_REQUEST["search_simple"])) {
		$search_simple = $_REQUEST["search_simple"] ;
	} else {
		$search_simple = "" ;
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
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = $_REQUEST["search_mode"] ;
	} else {
		$search_mode = "" ;
	}
	if (isset($_REQUEST["name_code"])) {
		$name_code = $_REQUEST["name_code"] ;
	} else {
		$name_code = "" ;
	}
	if (isset($_REQUEST["record_id"])) {
		$record_id = $_REQUEST["record_id"] ;
	} else {
		$record_id = "" ;
	}
	
	// connect to database
	
	include "connect_to_database.php" ;
	
	// get record id, if only name code is given
	
	$accepted_name = "" ;
	if ($name_code != "" && $record_id == "") {
		$query = "SELECT `record_id` 
				  FROM `scientific_names` WHERE `name_code` = '$name_code' " ;
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		$row = mysql_fetch_row($result);
		mysql_free_result($result) ;
		$record_id = $row[0] ;
	}
	
	// check if it is a synonym
	
	$selected_synonym_header = "" ; 
	$query = "SELECT  `scientific_names`.`genus` , 
					 `scientific_names`.`species` , 
					 `scientific_names`.`infraspecies_marker` , 
					 `scientific_names`.`infraspecies` ,
					 `scientific_names`.`author` ,
					 `scientific_names`.`name_code` , 
					 `scientific_names`.`accepted_name_code` , 
					 `sp2000_statuses`.`sp2000_status`
			  FROM `scientific_names` , `sp2000_statuses` 
			  WHERE `scientific_names`.`record_id` = '$record_id' 
			    AND `sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id`" ;
				
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$genus               = $row[0] ;
	$species             = $row[1] ;
	$infraspecies_marker = $row[2] ;
	$infraspecies        = $row[3] ;
	$author 			 = $row[4] ;
	$name_code  		 = $row[5] ;
	$accepted_name_code  = $row[6] ;
	$status				 = $row[7] ;
	if ($status != "accepted name" && $status != "provisionally accepted name") {
		$selected_synonym_name = "<i>$genus $species</i>" ; 
		if ($infraspecies_marker != "") {
			$selected_synonym_name .= " $infraspecies_marker" ; 
		}
		if ($infraspecies != "") {
			$selected_synonym_name .= " <i>$infraspecies</i>" ; 
		}
		if ($author != "") {
			$selected_synonym_name .= " $author" ; 
		}
		$selected_synonym_header = "You selected $selected_synonym_name" .
			((substr($selected_synonym_name,-1) == ".") ? " " : ". ") . 
			"This is a" . ((strpos("aehiou",substr($status,0,1)) === false) ? " " : "n ") . 
			" $status for:" ;
		
		$query = "SELECT `record_id` 
				  FROM `scientific_names` WHERE `name_code` = '$accepted_name_code' " ;
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		$row = mysql_fetch_row($result);
		mysql_free_result($result) ;
		$record_id = $row[0] ;
	}
	
	// collect fields from database
	
	if ($record_id == "") {
		die ("<p style='font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px;'>Sorry, no accepted name found for $selected_synonym_name.<br><br><a href='JavaScript:history.back();'>Back</a></p>") ;
	}
	$query = "SELECT `scientific_names`.`genus` , 
					 `scientific_names`.`species` , 
					 `scientific_names`.`infraspecies_marker` , 
					 `scientific_names`.`infraspecies` , 
					 `scientific_names`.`accepted_name_code` , 
					 `sp2000_statuses`.`sp2000_status`, 
					 `scientific_names`.`author` , 
					 `scientific_names`.`comment` , 
					 `scientific_names`.`database_id` , 
					 `scientific_names`.`specialist_id` , 
					 `scientific_names`.`web_site` , 
					 `scientific_names`.`name_code` ,
					 DATE_FORMAT(  `scientific_names`.`last_modified`  , '%M %D, %Y' ) ,
					 `scientific_names`.`family_id`
			  FROM `scientific_names` , `sp2000_statuses` 
			  WHERE `scientific_names`.`record_id` = '$record_id' 
				AND `sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id`" ;
				
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$genus               = $row[0] ;
	$species             = $row[1] ;
	$infraspecies_marker = $row[2] ;
	$infraspecies        = $row[3] ;
	$accepted_name_code  = $row[4] ;
	$status              = ucfirst($row[5]) ;
	$author              = $row[6] ;
	$remarks             = $row[7] ;
	$db_id               = $row[8] ;
	$specialist_id       = $row[9] ;
	$web_site     	     = $row[10] ;
	$name_code           = $row[11] ;
	$last_modified		 = $row[12] ;
	$family_id		     = $row[13] ;
	
	if ($genus == "") {
		$genus = "not assigned" ;
	}
	if ($accepted_name_code == "") {
		$accepted_name_code = $name_code ;
	}
	if ($remarks == "") {
		$remarks = "&#150" ;
	}
	
	if ($name_code == $accepted_name_code) {
		$accepted_genus = $genus ;
		$accepted_species = $species ;
		$accepted_infraspecies_marker = $infraspecies_marker ;
		$accepted_infraspecies = $infraspecies ;
	} else {
		$query = "SELECT `genus` ,
						 `species` , 
						 `infraspecies_marker`,
						 `infraspecies`
				  FROM `scientific_names` 
				  WHERE `name_code` = '" . addslashes($accepted_name_code) . "'" ;
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		$row = mysql_fetch_row($result);
		$accepted_genus = $row[0] ;
		$accepted_species = $row[1] ;
		$accepted_infraspecies_marker = $row[2] ;
		$accepted_infraspecies = $row[3] ;
	}
	
	// get taxonomic classification
	
	$classification = "" ;
	$kingdom = "" ;
	
	$parent_id = "" ;
	$query = "SELECT `record_id` FROM `taxa` WHERE `name_code` = '$name_code'" ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$number_of_rows = mysql_num_rows($result);
	if ($number_of_rows > 0) {
		$row = mysql_fetch_row($result);
		$parent_id = $row[0] ;
	}
	mysql_free_result($result) ;
	
	if ($parent_id != "" ) {
		$parents = "" ;
		$found_parent = TRUE ;
		while($found_parent != FALSE) {
			$query = "SELECT `parent_id` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
			$number_of_rows = mysql_num_rows($result);
			if ($number_of_rows == 0) {
				$found_parent = FALSE ;
			} else {
				$row = mysql_fetch_row($result);
				if ($row[0] == "") {
					$found_parent = FALSE ;
				} else if ($parents == "") {
					$parents = $row[0] ;
				} else  {
					$parents = $row[0] . "," . $parents;
				}
				$parent_id = $row[0] ;
				if ($row[0] == "0") {
					$found_parent = FALSE ;
				}
			}
			mysql_free_result($result) ;
		}
		
		if ($parents != "") {
			$parents = split(",", $parents);
			for ($i = 0; $i < count($parents); $i++) {
				$parent_id = $parents[$i] ;
				$query = "SELECT `taxon`,`name_with_italics`,`parent_id` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
				$result = mysql_query($query) or die("Query failed : " . mysql_error());
				$number_of_rows = mysql_num_rows($result);
				if ($number_of_rows > 0) {
					$row = mysql_fetch_row($result);
					$parent_rank = $row[0] ;
					$parent_name = $row[1] ;
					if ($parent_rank == "Kingdom") {
						$kingdom = $parent_name  ;
					}
					if ($parent_name != "Not assigned") {
						$parent_name = "<a href=\"JavaScript:showTaxonomicTree('$parent_id');\">$parent_name</a>" ;
						if ($parent_rank != "Kingdom") {
							$parent_name = "$parent_rank $parent_name" ;
						}
						$classification .= "<p class='fieldvalue'>$parent_name</p>\n" ;
					}
				}
				mysql_free_result($result) ;
			}
		}
	}
		
	// compile scientific name
	
	$scientific_name = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom) ;
	
	// collect synonyms
	
	$number_of_synonyms = 0 ;
	$query = "SELECT DISTINCT `scientific_names`.`genus` ,
							  `scientific_names`.`species` , 
							  `scientific_names`.`infraspecies_marker` , 
							  `scientific_names`.`infraspecies` , 
							  `scientific_names`.`author` , 
							  `sp2000_statuses`.`sp2000_status` 
			 FROM  `scientific_names` ,  `sp2000_statuses` 
			 WHERE  `scientific_names`.`accepted_name_code` = '$name_code' 
			   AND  `scientific_names`.`name_code` != `scientific_names`.`accepted_name_code` 
			   AND  `sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` " ;
			   
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$number_of_synonyms = mysql_num_rows($result);
	$synonyms = array() ;
	for ($i = 1; $i <= $number_of_synonyms; $i++) {
		$row = mysql_fetch_row($result) ;
		$synonym_genus = $row[0];
		$synonym_species = $row[1];
		$synonym_infraspecies_marker = $row[2];
		$synonym_infraspecies = $row[3];
		$synonym_author = $row[4];
		$synonym_status = $row[5];
		
		$synonyms[$i] = compileScientificName($synonym_genus,$synonym_species,$synonym_infraspecies_marker,$synonym_infraspecies,$synonym_author,$kingdom) ;
		$synonyms_genus[$i] = $synonym_genus ;
		$synonyms_species[$i] = $synonym_species ;
		$synonyms_infraspecies_marker[$i] = $synonym_infraspecies_marker ;
		$synonyms_infraspecies[$i] = $synonym_infraspecies ;
		$synonyms_author[$i] = $synonym_author ;
		$synonyms_status[$i] = $synonym_status ;
		
		$query2 = "SELECT `name_code` 
				   FROM `scientific_names` , `sp2000_statuses`
				   WHERE `scientific_names`.`genus` = '" . addslashes($synonym_genus) . "' 
				     AND `scientific_names`.`species` = '" . addslashes($synonym_species) . "' 
					 AND `scientific_names`.`infraspecies_marker`  = '" . addslashes($synonym_infraspecies_marker) . "'
					 AND `scientific_names`.`infraspecies` = '" . addslashes($synonym_infraspecies) . "' 
					 AND `scientific_names`.`author` = '" . addslashes($synonym_author) . "' 
					 AND `sp2000_statuses`.`sp2000_status` = '" . addslashes($synonym_status) . "' " ;
		$result2 = mysql_query($query2) or die("Query failed : " . mysql_error());
		$number_of_name_codes = mysql_num_rows($result2);
		
		$synonyms_references[$i] = 0 ;
		for ($j = 0 ; $j < $number_of_name_codes ; $j++) {
			$row2 = mysql_fetch_row($result2) ;
			$synonym_name_code = $row2[0] ;
			$query3 = "SELECT DISTINCT `scientific_name_references` . `reference_id`
					  FROM `scientific_name_references` 
					  WHERE `scientific_name_references`.`name_code` = '" . addslashes($synonym_name_code) . "' 
						AND (`scientific_name_references`.`reference_type` = 'AuthorRef' 
						  OR `scientific_name_references`.`reference_type` = 'StatusRef')" ;
						  
			$result3 = mysql_query($query3) or die("Query failed : " . mysql_error());
			$synonyms_references[$i] += mysql_num_rows($result3);
		}
					 
	}
	mysql_free_result($result) ;
	
	
	// collect infraspecies for a species
	
	$infraspecies_for_species = "" ;
	if ($infraspecies == "") {
		$query = "SELECT `scientific_names`.`record_id` , 
						 `scientific_names`.`infraspecies_marker` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author`,
						 `scientific_names`.`name_code`,
						 `scientific_names`.`accepted_name_code`
				  FROM `scientific_names` , `sp2000_statuses` 
				  WHERE `scientific_names`.`genus` = '" . addslashes(strip_tags($genus)) . "'  
					AND `scientific_names`.`species` = '" . addslashes(strip_tags($species)) . "'    
					AND `scientific_names`.`infraspecies` != ''
					AND `sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
					AND LOCATE('accepted name', `sp2000_statuses`.`sp2000_status`) > 0" ; 
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		$number_of_infraspecies_for_species = mysql_num_rows($result);
		for ($i = 1; $i <= $number_of_infraspecies_for_species; $i++) {
			$row = mysql_fetch_row($result) ;
			$this_id = $row[0] ;
			$this_infraspecies_marker = $row[1] ;
			$this_infraspecies = $row[2] ;
			$this_author = $row[3] ;
			$this_name_code = $row[4] ;
			$this_accepted_name_code = $row[5] ;
			if ($this_accepted_name_code == "") {
				$this_accepted_name_code = $this_name_code ;
			}
			if ($this_accepted_name_code == $this_name_code) {
				$infraspecies_for_species .= "<p class=\"fieldvalue\"><a href=\"JavaScript:showSpeciesDetails($this_id)\">" ;
				$infraspecies_for_species .= compileScientificName($genus,$species,$this_infraspecies_marker,$this_infraspecies,$this_author,$kingdom) ;	
				$infraspecies_for_species .= "</a></p>\n" ;
			}
		}
	}
	
	$query = "SELECT `database_name` , 
					 `database_full_name` , 
					 `version` 
			  FROM   `databases` 
			  WHERE  `record_id` = '$db_id' " ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$db_name = $row[0] ;
	$db_fullname = $row[1] ;
	$db_version = $row[2] ;
	
	$query = "SELECT `specialist_name` 
			  FROM `specialists` WHERE `record_id` = '$specialist_id'" ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$specialist_name = $row[0] ;
	
	$query = "SELECT `distribution` 
			  FROM `distribution` WHERE `name_code` = '$name_code' " ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$distribution = $row[0] ;
	if ($distribution == "") {
		$distribution = "&#150" ;
	}
	
	$query = "SELECT DISTINCT (`common_name`)
			  FROM `common_names` 
			  WHERE `name_code` = '$name_code' " ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$number_of_common_names = mysql_num_rows($result);
	for ($i = 1; $i <= $number_of_common_names; $i++) {
		$row = mysql_fetch_row($result);
		$common_names_names[$i] = $row[0] ;
		$common_names_languages[$i] = "" ;
	}
	mysql_free_result($result) ;
	
	if ($number_of_common_names > 0)  {
		for ($i = 1; $i <= $number_of_common_names; $i++) {
			$common_name = addslashes ($common_names_names[$i]) ;
			$query = "SELECT DISTINCT `language`
					  FROM `common_names` 
					  WHERE `language` != '' 
						AND `common_name` = '$common_name' 
					  ORDER by `language`" ;
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
			$number_of_languages = mysql_num_rows($result);
			if ($number_of_languages  > 0) {
				$common_names_languages[$i] .= " " ;
				for ($j = 0; $j < $number_of_languages; $j++) {
					$row = mysql_fetch_row($result);
					if ($j > 0) {
						$common_names_languages[$i] .= ", " ;
					}
					$common_names_languages[$i] .= $row[0] ;
				}
			}
		}
		mysql_free_result($result) ;
	}
	
	$query = "SELECT `reference_id`
			  FROM `scientific_name_references` 
			  WHERE `name_code` = '$name_code'
			    AND (`reference_type` = 'AuthorRef' OR `reference_type` = 'StatusRef') " ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$number_of_author_references = mysql_num_rows($result);
	
	mysql_free_result($result) ;
	mysql_close($link) ;
	
	$image_path = "images/db_logos/" . str_replace(" ","_",$db_name). ".jpg" ;
	if (file_exists($image_path)) {
		$image_path = "<p style='margin-bottom:12px'><a href=\"JavaScript:showDatabaseDetails('$db_name')\">" . 
			"<img src=\"$image_path\" border=0 title='Source database for this record: $db_name'></a></p>" ;
	} else {
		$image_path = "" ;
	}
	
?>

<title>Catalogue of Life : 2005 Annual Checklist : <?php
	echo strip_tags($scientific_name) ;
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="en-GB" />
<meta name="keywords" content="<?php echo strip_tags($scientific_name) ?> biodiversity species 2000 itis taxonomy taxa" />
<meta name="description" content="The Species 2000/ITIS Catalogue of Life : 2005 Annual Checklist 
  is a comprehensive index of all known plants, animals, fungi and micro-organisms. 
  It can be used to search multiple databases simultaneously for the scientific name of an 
  organism." />
<meta name="language" content="en-GB" />
<meta name="robots" content="all" />
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
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
                        <p class="formheader" align="center">
<?php
	if ($infraspecies != "") {
		echo "Infraspecies details" ;
	} else {
		echo "Species details" ;
	}
?>
						</p>
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
					  <?php echo $image_path ; ?>
<?php
	if ($selected_synonym_header != "") {
		echo "<p style='margin-bottom:10px'>$selected_synonym_header</p>\n";
	}
?>
                        <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#EDEBEB">
                          <tr> 
                            <td> 
                              <table border="0" cellspacing="0" cellpadding="3" width="100%">
							     <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Accepted scientific name:</p>
                                  </td>
                                  <td valign=top> 
								  
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
		<p class="fieldvalue"><?php echo "$scientific_name" ?>
	</td>
    <td align=right>
<?php
	if ($number_of_author_references > 0) {
		if ($number_of_author_references == 1) {
			$label = "1 literature reference" ;
		} else {
			$label = "$number_of_author_references literature references" ;
		}
		$label .= " for " . addslashes(strip_tags($scientific_name)) ;
		$label = str_replace ("\"","",$label) ;
		$label = str_replace ("\'","",$label) ;
		$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' title='$label'>" ;

		echo "<a href=\"JavaScript:showReferenceDetails('" . urlencode($scientific_name) . "','" .
															 urlencode($accepted_genus) . "','" .
															 urlencode($accepted_species) . "','" .
															 urlencode($accepted_infraspecies_marker) . "','" .
															 urlencode($accepted_infraspecies) . "','" .
															 urlencode($author) . "','" .
															 urlencode($status) . "')\" " .
		  "onMouseOver=\"return showStatus('$label')\" " .
		  "onMouseOut=\"return showStatus('')\">$book_icon</a>" ;
	}
?>
	</td>
  </tr>
</table>
   							
									
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Name status:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $status ?>
                                    </p>
                                  </td>
                                </tr>
                              <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px"> 
 <?php		  
	if ($status == "Accepted name" || $status == "Provisionally accepted name") {
		echo "<p class=\"fieldheader\">Synonym" ;
		if ( $number_of_synonyms != 1 ) {
			echo "s" ;
		}
		echo ":</p>" ;
	} else {
		echo "<p class=\"fieldheader\">Accepted name:</p>" ;
	}
?>
                                  </td>
                                  <td valign=top> 
 <?php
	if ($status == "Accepted name" || $status == "Provisionally accepted name") {
		if ($number_of_synonyms == 0) {
			echo "<p class=\"fieldvalue\">&#150;</p>\n" ;
		} else {
			for ($i = 1; $i <= $number_of_synonyms; $i++) {
				$synonym = $synonyms[$i] ;
				$synonym_genus = $synonyms_genus[$i] ;
				$synonym_species = $synonyms_species[$i] ;
				$synonym_infraspecies_marker = $synonyms_infraspecies_marker[$i] ;
				$synonym_infraspecies = $synonyms_infraspecies[$i] ;
				$synonym_author = $synonyms_author[$i] ;
				$synonym_status = $synonyms_status[$i] ;
					
				echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n" ;
				echo "<tr>\n" ;
				echo "<td valign=top>\n" ;
				echo "<p class=\"fieldvalue\">$synonym ($synonym_status)</p>\n" ;
				echo "</td>\n" ;
				
				if ( $synonyms_references[$i] != "0") {
					echo "<td valign=top align=right>\n" ;
					
					if ($synonyms_references[$i] == 1) {
						$label = "1 literature reference" ;
					} else {
						$label = $synonyms_references[$i] . " literature references" ;
					}
					$label .= " for " . addslashes(strip_tags($synonym)) ;
					$label = str_replace ("\"","",$label) ;
					$label = str_replace ("\'","",$label) ;
					
					$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' title='$label'>" ;
					
					$synonym_header = $synonym  . ", a" . 
						((strpos("aehiou",substr($synonym_status,0,1)) === false) ? " " : "n ") . 
						"$synonym_status for $scientific_name" ;
					echo "<a href=\"JavaScript:showReferenceDetails('" . urlencode("$synonym_header") . "','" .
																		 urlencode($synonym_genus) . "','" .
																		 urlencode($synonym_species) . "','" .
																		 urlencode($synonym_infraspecies_marker) . "','" .
																		 urlencode($synonym_infraspecies) . "','" .
																		 urlencode($synonym_author) . "','" .
																		 urlencode($synonym_status) . "')\" " .
						"onMouseOver=\"return showStatus('$label')\" " .
						"onMouseOut=\"return showStatus('')\">$book_icon</a>" ;
				}
				echo "</td>\n" ;
				echo "</tr>\n" ;
				echo "</table>\n" ;
			}
		}
		echo "</td>\n" ;
		echo "</tr>\n" ;
	} else {
		echo "<p class=\"fieldvalue\">" . $accepted_name. "</p>\n" ;
	}
?>
                                  </td>
                                </tr>
<?php
	
	if ($infraspecies_for_species != "") {
		echo "<tr bgcolor=\"" ;
		$table_row_color=getTableRowColor($table_row_color) ;
		echo "\">\n" ;
		echo "<td valign=\"top\" width=\"150px\">\n" ;
		echo "<p class=\"fieldheader\">Infraspecies:</p>\n" ;
        echo "</td>\n" ;
		echo "<td valign=\"top\">$infraspecies_for_species</td>\n" ;
		echo "</tr>\n" ;
	}
?>
                       
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Common name<?php if ($number_of_common_names != 1) { echo "s" ; } ?>:</p>
                                  </td>
                                  <td valign=top> 
<?php
	if ($number_of_common_names == 0) {
		echo "<p class='fieldvalue'>&#150;</p>" ;
	} else {			
		for ($i = 1; $i <= $number_of_common_names; $i++) {
			$common_name = $common_names_names[$i] ;
			$language = $common_names_languages[$i] ;
			echo "<p class='fieldvalue'>" ;
			echo "<a href='JavaScript:showCommonNameDetails(\"" . urlencode(addslashes($common_name)) . "\")'>" ; 
			echo $common_name . "</a>" . $language . "</p>" ;
		}
	}
?>
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Classification:</p>
                                  </td>
                                   <td valign=top>
                                      <?php echo $classification ?>
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Distribution:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $distribution ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor=<?php $table_row_color=getTableRowColor($table_row_color) ; ?>> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Additional data:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $remarks ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Source database:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"><?php echo "<a href='JavaScript:showDatabaseDetails(\"$db_name\")'>$db_fullname</a>, $db_version" ; ?></p>
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Latest taxonomic scrutiny:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
<?php
	if ($specialist_name != "" && $last_modified != "") {
		echo "$specialist_name, $last_modified" ;
	} else if ($specialist_name != "") {
		echo $specialist_name ;
	} else if ($last_modified != "") {
		echo $last_modified ;
	} else {
		echo "&#150" ;
	}
?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
                                  <td valign=top width="150px"> 
                                    <p class="fieldheader">Online resource:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
<?php
	if ( substr($web_site,0,1) == "#" ) {
		$web_site = substr($web_site,1,strlen($web_site)-1) ;
	}
	if ( substr($web_site,0,7) == "http://" && strlen($web_site) >= 8 ) {
		$first_slash = strpos($web_site,"/",8) ;
		if ($first_slash != FALSE ) {
			echo "<a href='$web_site' TARGET='_blank'>" . substr($web_site,0,$first_slash) . "/...</a>" ;
		} else {
			echo "<a href='$web_site' TARGET='_blank'>$web_site</a>" ;
		}
	} elseif ($web_site == "") {
		echo "&#150" ;
	} else {
		echo $web_site ;
	}
?>
                                    </p>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
<?php
	echo "<p align=center>" ;
	echo "<br>" ;
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referring_page = $_SERVER['HTTP_REFERER'] ;
	} else {
		$referring_page = "" ;
	}
	if (strpos($referring_page,"search_results") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to search results</a> | " .
			  "<a href='JavaScript:document.new_search.submit()'>New search</a>" ;
	} else if (strpos($referring_page,"show_common_name_details") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to common name details</a>" ;
	} else if (strpos($referring_page,"browse_taxa") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to taxonomic tree</a>" ;
	} else {
		echo "<a href='JavaScript:history.back()'>Back to last page</a>" ;
	}
	echo "</p>" ;
?>
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
<form name="new_search" method="post" action="<?php echo urldecode($search_page) ?>">
  <input type="hidden" name="search_kingdom" value="<?php echo $search_kingdom ?>">
  <input type="hidden" name="search_phylum" value="<?php echo $search_phylum ?>">
  <input type="hidden" name="search_class" value="<?php echo $search_class ?>">
  <input type="hidden" name="search_order" value="<?php echo $search_order ?>">
  <input type="hidden" name="search_family" value="<?php echo $search_family ?>">
  <input type="hidden" name="search_genus" value="<?php echo $search_genus ?>">
  <input type="hidden" name="search_species" value="<?php echo $search_species ?>">
  <input type="hidden" name="search_infraspecies" value="<?php echo $search_infraspecies ?>">
  <input type="hidden" name="search_simple" value="<?php echo $search_simple ?>">
  <input type="hidden" name="search_common_name" value="<?php echo $search_common_name ?>">
  <input type="hidden" name="search_distribution" value="<?php echo $search_distribution ?>">
  <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
</form>
<form name="show_species_details" method="post" action="show_species_details.php">
  <input type="hidden" name="record_id">
  <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
  <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
  <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
  <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
  <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
  <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
  <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
  <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
  <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
  <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
  <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
</form>
<form name="show_reference_details" method="post" action="show_reference_details.php">
  <input type="hidden" name="name">
  <input type="hidden" name="genus">
  <input type="hidden" name="species">
  <input type="hidden" name="infraspecies_marker">
  <input type="hidden" name="infraspecies">
  <input type="hidden" name="author">
  <input type="hidden" name="status">
</form>
<form name="show_database" method="post" action="show_database_details.php">
  <input type="hidden" name="database_name">
</form>
<form name="show_common_name" method="post" action="show_common_name_details.php">
  <input type="hidden" name="common_name">
</form>
<form name="show_tree" method="post" action="browse_taxa.php">
<input type="hidden" name="selected_taxon" value="">
</form>
</div>
</body>
</html>
