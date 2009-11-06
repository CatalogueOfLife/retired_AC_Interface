<?php
	session_start(); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2008 Annual Checklist : Search results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script language="JavaScript" type="text/javascript">
function startAtRecord (thisPage) {
	document.start_at_record.first_record_shown.value = thisPage ;
	document.start_at_record.submit() ;
}
</SCRIPT>
<?php
	function compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies, 
	  $this_kingdom,$this_author) {
		if ($this_genus . $this_species . $this_infraspecies == "") {
			return "" ;
		} else if ($this_kingdom == "Viruses" || $this_kingdom == "Subviral agents") {
			$scientific_name = $this_species ;
		} else {
			$scientific_name = "<i>$this_genus $this_species</i>" .
			  (($this_infraspecies_marker != "") ? " $this_infraspecies_marker" : "") .
			  (($this_infraspecies != "") ? " <i>$this_infraspecies</i>" : "") .
			  (($this_author != "") ? " $this_author" : "") ;
		}
		return $scientific_name ;
	}
	
	function addCommas($value) {
		$length = strlen($value) ;
		$counter = 0 ;
		$new_value = "" ;
		for ($i = 0; $i < $length; $i++) {
			$counter ++ ;
			if ($counter == 4) {
				$counter = 1 ;
				$new_value = "," .$new_value  ;
			}
			$new_value = substr($value,$length-1-$i,1) . $new_value ;
		}
		return $new_value ;
	}
	
	function styleColumnHeader ($column_name, $current_column, $selected_column) {
		if ($current_column == $selected_column) {
			return "<u>$column_name</u>" ;
		} else {
			return "<a href='JavaScript:sortByColumn($current_column)'>$column_name</a>" ;
		}
	}
	
	$variables_to_get = Array(
	  "search_type", 
	  "search_string", 
	  "kingdom", 
	  "phylum", 
	  "tax_class", 
	  "order", 
	  "superfamily",
	  "family", 
	  "genus", 
	  "species", 
	  "infraspecies", 
	  "common_name", 
	  "area", 
	  "match_whole_words", 
	  "sort_by_column", 
	  "number_of_records_found", 
	  "number_of_records_shown_per_page", 
	  "first_record_shown", 
	  "number_of_records_to_show" ) ;
	foreach($variables_to_get as $variable){
		if (isset($_REQUEST[$variable])) {
			$$variable = $_REQUEST[$variable] ;
		} else {
			$$variable = "" ;
		}
		$$variable = trim(stripslashes(urldecode($$variable))) ;
	}
	
	if (isset($_REQUEST["number_of_records_shown_per_page"]) === false && isset($_SESSION["ac_number_of_records_shown_per_page"])) {
		$number_of_records_shown_per_page = $_SESSION["ac_number_of_records_shown_per_page"] - 0 ;
	}
	
	if ($match_whole_words == "") {
		$match_whole_words = "off" ;
	} else if ($match_whole_words != "on" && $match_whole_words  != "off") {
		die("<p>Error: invalid parameter value. Match whole words must be on or off.</p>") ;
	}
	$sort_by_column = $sort_by_column-0 ;
	$number_of_records_shown_per_page = $number_of_records_shown_per_page-0 ;
	$first_record_shown = $first_record_shown-0 ;
	$number_of_records_found = $number_of_records_found-0 ;
	$number_of_records_to_show = $number_of_records_to_show-0 ;
	
	if ($sort_by_column == 0) {
		$sort_by_column = 1 ;
	}
	if ($number_of_records_shown_per_page == 0) {
		$number_of_records_shown_per_page = 10 ;
	}
	
	if ($search_string != "") {
		$search_results_header = "Search results for \"" . stripslashes($search_string) . "\"" ;
	} else if ($common_name != "") {
		$search_results_header = "Search results for common names" ;
	} else if ($area != "") {
		$search_results_header = "Search results for distribution" ;
	} else if ($kingdom . $phylum . $tax_class . $order .  $family . 
	    $genus . $species . $infraspecies != "") {
		$search_results_header = "Search results for scientific names" ;
	} else {
		$search_results_header = "Search results" ;
	}
	
	// change * to % in search string
	
	$kingdom = str_replace(array("*","?"),"%",$kingdom) ;
	$phylum = str_replace(array("*","?"),"%",$phylum) ;
	$tax_class = str_replace(array("*","?"),"%",$tax_class) ;
	$order = str_replace(array("*","?"),"%",$order) ;
	$superfamily = str_replace(array("*","?"),"%",$superfamily) ;
	$family = str_replace(array("*","?"),"%",$family) ;
	$genus = str_replace(array("*","?"),"%",$genus) ;
	$species = str_replace(array("*","?"),"%",$species) ;
	$infraspecies = str_replace(array("*","?"),"%",$infraspecies) ;
	$common_name = str_replace(array("*","?"),"%",$common_name) ;
	$area = str_replace(array("*","?"),"%",$area) ;
	$search_string = str_replace(array("*","?"),"%",$search_string) ;
	
	$new_search_url = (($search_type == "") ? "search.php" : urldecode($search_type) . ".php") ;
	
	$error = "" ;
	if ($search_string . $area . $common_name . $kingdom . $phylum . $tax_class . $order . $superfamily . 
	  $family . $genus . $species . $infraspecies == "" ) {
		$error = "Error: you did not enter a search string." ;
	} else if (strlen($search_string) == 1 || strlen($area) == 1 || strlen($common_name) == 1 ) {
		$error = "Please enter a search string of at least two characters." ;
	} 
		
	if ($error == "") {
		//compile query
		
		$target = "search results" ;
		include "compile_query.php" ;

		if ($select != "" ) {
			if ($number_of_records_found === 0) {
				$find_total = "SQL_CALC_FOUND_ROWS" ;
			} else {
				$find_total = "" ;
			}
			if ($search_string == "") {
				$search_query = "SELECT $find_total $select FROM $from WHERE $where ORDER BY $order_by" ;
			} else {
				$search_query = "SELECT $find_total $select FROM $from WHERE $where 
						   UNION SELECT $select2 FROM $from2 WHERE $where2
								 ORDER BY $order_by" ;
			}
			
		}
		
		
		
		include "connect_to_database.php" ;
		$partial_query = $search_query . " LIMIT $first_record_shown,$number_of_records_shown_per_page";
//echo  $partial_query  . "<br>";
		$result = mysql_query($partial_query) or die("Error: MySQL query failed");
		
		if ($number_of_records_found === 0) {
			$number_of_records_query = "SELECT FOUND_ROWS()" ;
			$number_of_records_result = mysql_query($number_of_records_query) or die(mysql_error());
			$row = mysql_fetch_row($number_of_records_result) ;
			$number_of_records_found = $row[0] ;
		}
		if ($number_of_records_found > $number_of_records_shown_per_page) {
			$number_of_records_to_show = $number_of_records_shown_per_page;
		} else {
			$number_of_records_to_show = $number_of_records_found ;
		}
		if ($number_of_records_to_show >  mysql_num_rows($result)) {
			$number_of_records_to_show = mysql_num_rows($result) ;
		}
		$first_record_of_previous_set = $first_record_shown - $number_of_records_shown_per_page ;
		if ($first_record_of_previous_set < 0) {
			$first_record_of_previous_set = 0 ;
		}
		$first_record_of_next_set = $first_record_shown + $number_of_records_shown_per_page ;
		if ($first_record_of_next_set > $number_of_records_found) {
			$first_record_of_next_set = $number_of_records_found ;
		}
	}
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "ac_config.php" ;
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
      <td valign=top width="100%"> 
        <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100%">
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
	echo $search_results_header ;
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
                    <table border="0" cellspacing="0" cellpadding="10" width="100%">
                      <tr> 
                        <td> 
                          <table width='100%' border=0 cellspacing=0 cellpadding=0>
                            <tr> 
                              <td> 
                                <?php
	if ($error != "") {
	  	echo "<p>$error</p>\n" ;
		echo "<p align=center><br><br>\n" ;
		echo "<a href='$new_search_url'>New search</a></p>" ;
	} else if ($search_query == "") {
	  	echo "<p>Could not perform search because you did not enter any search criteria.</p>\n" ;
		echo "<p align=center><br><br>\n" ;
		echo "<a href='$new_search_url'>New search</a></p>" ;
	} else {
		// display results
		echo "<script language='JavaScript' type='text/javascript'>\n" ;
		echo "document.write(\"<p>Records found: " . addCommas($number_of_records_found). "<p>\");\n" ;
		echo "</SCRIPT>\n" ;
	}
?>
                              </td>
                              <td align=right> 
<?php
	if ($error == "" && $number_of_records_found > 1) {
		echo "<form name='change_number_of_records_shown_per_page' method='get' action='search_results.php'>\n" ;
		echo "<input type='hidden' name='search_type' value='" . urlencode($search_type) . "'>\n" ;
		echo "<input type='hidden' name='number_of_records_found' value='$number_of_records_found'>\n" ;
		echo "<input type='hidden' name='search_string' value='" . urlencode($search_string) . "'>\n" ;
		echo "<input type='hidden' name='kingdom' value='" . urlencode($kingdom) . "'>\n" ;
		echo "<input type='hidden' name='phylum' value='" . urlencode($phylum) . "'>\n" ;
		echo "<input type='hidden' name='class' value='" . urlencode($tax_class) . "'>\n" ;
		echo "<input type='hidden' name='order' value='" . urlencode($order) . "'>\n" ;
		echo "<input type='hidden' name='family' value='" . urlencode($family) . "'>\n" ;
		echo "<input type='hidden' name='genus' value='" . urlencode($genus) . "'>\n" ;
		echo "<input type='hidden' name='species' value='" . urlencode($species) . "'>\n" ;
		echo "<input type='hidden' name='infraspecies' value='" . urlencode($infraspecies) . "'>\n" ;
		echo "<input type='hidden' name='common_name' value='" . urlencode($common_name) . "'>\n" ;
		echo "<input type='hidden' name='area' value='" . urlencode($area) . "'>\n" ;
		echo "<input type='hidden' name='match_whole_words' value='$match_whole_words'>\n" ;
		echo "<input type='hidden' name='sort_by_column' value='$sort_by_column'>\n" ;
		echo "<input type='hidden' name='first_record_shown' value='0'>\n" ;
		echo "<p>Show <input type='text' name='number_of_records_shown_per_page' size='4' value='$number_of_records_shown_per_page'> " ;
		echo "records per page&nbsp;&nbsp;<input type=\"submit\" name=\"Submit\" class=\"formbutton\" value=\"Update\"></p>\n" ;
		echo "</form>\n" ;
	} else {
		echo "<p><br><br></p>\n" ;
	}
?>
                              </td>
                            </tr>
                            <tr> 
                              <td colspan=2> 
                                <?php
	if ($error == "" && $search_query != "") {
		if ($number_of_records_found > 0) {
			echo "<table border=0 cellspacing=0 cellpadding=1 width='100%' bgcolor='#EDEBEB'>\n<tr>\n<td>\n" ;
			echo "<table border=0 cellspacing=0 cellpadding=0 width='100%' bgcolor='#FAFCFE'>\n<tr>\n<td>\n" ;
			echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n" ;
			if ($search_string != "") {
				$column_1 = styleColumnHeader("Name",1,$sort_by_column) ;
				$column_2 = styleColumnHeader("Rank",2,$sort_by_column) ;
				$column_3 = styleColumnHeader("Name status",3,$sort_by_column) ;
				$column_4 = styleColumnHeader("Source database",4,$sort_by_column) ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td valign=top align=left><span class='tableheader'>$column_1</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top align=left><span class='tableheader'>$column_2</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_3</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_4</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td><img src='images/blank.gif' width=120 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=70 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=100 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=110 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
			} else if ($common_name != "") {
				$column_1 = styleColumnHeader("Common name",1,$sort_by_column) ;
				$column_2 = styleColumnHeader("Accepted scientific name",2,$sort_by_column) ;
				$column_3 = styleColumnHeader("Source database",3,$sort_by_column) ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_1</span></td>" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
				echo "<td valign=top><span class='tableheader'>$column_2</span></td>" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_3</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>" ;
				echo "</tr>\n" ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td><img src='images/blank.gif' width=120 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=170 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=120 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
			} else if ($area != "") {
				$column_1 = styleColumnHeader("Distribution",1,$sort_by_column) ;
				$column_2 = styleColumnHeader("Accepted scientific name",2,$sort_by_column) ;
				$column_3 = styleColumnHeader("Source database",3,$sort_by_column) ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td valign=top align=left><span class='tableheader'>$column_1</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_2</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_3</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td><img src='images/blank.gif' width=120 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=170 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=120 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
			} else {
				$column_1 = styleColumnHeader("Scientific name",1,$sort_by_column) ;
				$column_2 = styleColumnHeader("Name status",2,$sort_by_column) ;
				$column_3 = styleColumnHeader("Source database",3,$sort_by_column) ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_1</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_2</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td valign=top><span class='tableheader'>$column_3</span></td>\n" ;
				echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>\n" ;
				echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
				echo "<tr bgcolor='#FAFCFE'>\n" ;
				echo "<td><img src='images/blank.gif' width=100 height=1 border=0></td>\n" ;
				echo "<td colspan=6><img src='images/blank.gif' width=1 height=1 border=0></td>\n" ;
				echo "</tr>\n" ;
			}
			$row_color = "#EAF2F7" ;
			for ($i = 1; $i <= $number_of_records_to_show; $i++) {
				$row = mysql_fetch_row($result);
				if ($search_string != "") {
					$record_id = $row[0] ;
					$found_name = $row[1] ;
					$found_taxon = $row[2] ;
					$name_code = $row[3] ;
					$status = $row[5] ;
					$source_db = $row[7] ;
					$is_accepted_name = $row[8] ;
				} else if ($common_name != "") {
					$this_common_name = $row[0] ;
					$this_genus = $row[1] ;
					$this_species = $row[2] ;
					$this_infraspecies_marker = $row[3] ;
					$this_infraspecies = $row[4] ;
					$this_author = $row[5] ;
					$name_code = $row[6] ;
					$source_db = $row[7] ;
					$scientific_name = "<i>$this_genus $this_species</i>" ;
					if ($this_infraspecies_marker != "") {
						$scientific_name .= " $this_infraspecies_marker" ;
					}
					if ($this_infraspecies != "") {
						$scientific_name .= " <i>$this_infraspecies</i>" ;
					}
					if ($this_author != "") {
						$scientific_name .= " $this_author" ;
					}
				} else if ($area != "") {
					$record_id = $row[0] ;
					$this_genus = $row[1] ;
					$this_species = $row[2] ;
					$this_infraspecies_marker = $row[3] ;
					$this_infraspecies = $row[4] ;
					$this_author = $row[5] ;
					$name_code = $row[6] ;
					$this_kingdom = $row[7] ;
					$source_db = $row[8] ;
					$scientific_name = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_kingdom,$this_author) ;
				} else {
					$record_id = $row[0] ;
					$this_genus = $row[1] ;
					$this_species = $row[2] ;
					$this_infraspecies_marker = $row[3] ;
					$this_infraspecies = $row[4] ;
					$this_author = $row[5] ;
					$name_code = $row[6] ;
					$accepted_name_code = $row[7] ;
					$sp2000_status = $row[8] ;
					$source_db = $row[9] ;
					$family_id = $row[10] ;
					
					$this_kingdom = "" ;
					if ($family_id  != "") {
						$kingdom_query = "SELECT `kingdom` FROM `families` WHERE `record_id` = '$family_id' " ;
						$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");	
						if (mysql_num_rows($kingdom_result) > 0) {
							$kingdom_row = mysql_fetch_row($kingdom_result);
							$this_kingdom = $kingdom_row[0] ;
						}
					}
					
					$scientific_name = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_kingdom,$this_author) ;
				}
				
				if ($search_string != "") {
					if ($status == "zzzzzz") {
						$status = "" ;
					}
					if ($status == "common name") {
						$languages = "" ;
						$language_query = "SELECT DISTINCT `language`
								  FROM `common_names` 
								  WHERE `language` != '' AND `language` IS NOT NULL
								    AND `common_name` = '" . addslashes($found_name) . "' 
									AND `name_code` = '$name_code' 
									AND `name_code` LIKE BINARY '$name_code' 
								  ORDER by `language`" ;
						$language_result = mysql_query($language_query) or die("Error: MySQL query failed");
						$number_of_languages = mysql_num_rows($language_result);
						if ($number_of_languages  > 0) {
							$languages .= " (" ;
							for ($j = 0; $j < $number_of_languages; $j++) {
								$row2 = mysql_fetch_row($language_result);
								if ($j > 0) {
									$languages .= ", " ;
								}
								$languages .= $row2[0] ;
							}
							$languages .= ")" ;
						}

						if ($name_code != "") {
							$scientific_name_query = "SELECT `genus` , `species` , `infraspecies_marker`, `infraspecies` , `author` 
								  FROM `scientific_names` 
								  WHERE `name_code` = '$name_code' 
								    AND `name_code` LIKE BINARY '$name_code' " ;
							$scientific_name_result = mysql_query($scientific_name_query) or die("Error: MySQL query failed");
							$row2 = mysql_fetch_row($scientific_name_result);
							$scientific_genus = $row2[0] ;
							$scientific_species = $row2[1] ;
							$scientific_infraspecies_marker = $row2[2] ;
							$scientific_infraspecies = $row2[3] ;
							$scientific_author = $row2[4] ;
							$kingdom_query = "SELECT `families`.`kingdom`  
								  FROM `families`,`scientific_names` 
								  WHERE `scientific_names`.`name_code` = '$name_code' 
								    AND `scientific_names`.`name_code` LIKE BINARY '$name_code' 
									AND `scientific_names`.`family_id` = `families`.`record_id` " ;
							$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");
							$row2 = mysql_fetch_row($kingdom_result);
							$this_kingdom = $row2[0] ;
							
							$scientific_name = compileScientificName($scientific_genus,$scientific_species,$scientific_infraspecies_marker,$scientific_infraspecies,$this_kingdom,$scientific_author) ;
							if ($scientific_name != "") {
								$status .= " for $scientific_name" ; 
							}
						}
						$this_link = "show_common_name_details.php?name=" . urlencode($found_name) ;
						$found_name .= $languages ;
					} else if ($found_taxon == "Species" || $found_taxon == "Infraspecies") {
						$species_query = "SELECT `record_id`, `author` 
							  FROM `scientific_names` 
							  WHERE `name_code` = '" . addslashes($name_code) . "'
							    AND `name_code` LIKE BINARY '" . addslashes($name_code) . "'" ;
						$species_result = mysql_query($species_query) or die("Error: MySQL query failed");
						$row2 = mysql_fetch_row($species_result) ;
						$species_id = $row2[0] ;
						$species_author = $row2[1] ;
						
						$kingdom_query = "SELECT `kingdom`
							  FROM `families` ,`scientific_names` 
							  WHERE `scientific_names`.`name_code` = '" . addslashes($name_code) . "' 
							    AND `scientific_names`.`name_code` LIKE BINARY '" . addslashes($name_code) . "' 
							    AND `scientific_names`.`family_id` = `families`.`record_id` " ;
						$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");
						$row3 = mysql_fetch_row($kingdom_result);
						$this_kingdom = $row3[0] ;
						
						if ($species_author != "" && $this_kingdom != "Viruses" && $this_kingdom != "Subviral agents") {
							$found_name .= " $species_author" ;
						}
						$this_link = "show_species_details.php?record_id=$species_id" ;
						if ($status != "accepted name" && $status != "provisionally accepted name") {
							$accepted_name = "" ;
							$accepted_name_code_query = "SELECT `accepted_name_code` 
								  FROM `scientific_names` 
								  WHERE `record_id` = '$species_id' " ;
							$accepted_name_code_result = mysql_query($accepted_name_code_query) or die("Error: MySQL query failed");
							$row2 = mysql_fetch_row($accepted_name_code_result);
							$accepted_name_code = $row2[0] ;
							if ($accepted_name_code == "") {
								$accepted_name_code = "unknown" ;
							} else {
								$accepted_name_query = "SELECT `genus` , `species` , `infraspecies_marker`, `infraspecies` , `author` 
									  FROM `scientific_names` 
									  WHERE `name_code` = '$accepted_name_code' 
									    AND `name_code` LIKE BINARY '$accepted_name_code'" ;
								$accepted_name_result = mysql_query($accepted_name_query) or die("Error: MySQL query failed");
								$row2 = mysql_fetch_row($accepted_name_result);
								$accepted_genus = $row2[0] ;
								$accepted_species = $row2[1] ;
								$accepted_infraspecies_marker = $row2[2] ;
								$accepted_infraspecies = $row2[3] ;
								$accepted_author = $row2[4] ;
								$kingdom_query = "SELECT `families`.`kingdom`  
									  FROM `families`,`scientific_names` 
									  WHERE `scientific_names`.`record_id` = '$species_id' 
									    AND `scientific_names`.`family_id` = `families`.`record_id` " ;
								$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");
								$row2 = mysql_fetch_row($kingdom_result);
								$this_kingdom = $row2[0] ;
								$accepted_name = compileScientificName($accepted_genus,$accepted_species,$accepted_infraspecies_marker,$accepted_infraspecies,$this_kingdom,$accepted_author) ;
								if ($accepted_name != "") {
									$status .= " for $accepted_name" ; 
								}
							}
							if ($accepted_name == "") {
								$this_link = "JavaScript:alert('Sorry, no accepted name found for $status " . addslashes(strip_tags($found_name)) . "');" ;
							}
						}
						
					} else {
						$status = "" ;
						if ($is_accepted_name == 1) {
							$this_link = "browse_taxa.php?selected_taxon=$record_id" ;
						} else {
							$this_link = "JavaScript:alert('Sorry, can\'t show $found_taxon " . 
								addslashes(strip_tags($found_name)) . " in the " .
								"taxonomic tree because this taxon does not contain any accepted names " .
								"(only synonyms and/or misapplied names).')" ;
						}
					}
					
					$search_string_short = str_replace("%","",$search_string) ;
					$found_name_lc = strtolower($found_name) ;
					$search_string_short_lc = strtolower($search_string_short) ;
					$searchStringLength = strlen($search_string_short) ;
					$searchStringStartOffset = strpos($found_name_lc,$search_string_short_lc) ;
					if ($found_name_lc == $search_string_short_lc) {
						$searchStringStartOffset  = 0 ;
					} else if ($found_name_lc == "<i>$search_string_short_lc</i>") {
						$searchStringStartOffset  = 3 ;
					} else if ($match_whole_words == "on" ) {
						if (substr($found_name_lc,0,$searchStringLength+1) == "$search_string_short_lc ") {
							$searchStringStartOffset  = 0 ;
						} else if (substr($found_name_lc,0,$searchStringLength+4) == "<i>$search_string_short_lc " 
						  || substr($found_name_lc,0,$searchStringLength+8) == "<i>$search_string_short_lc</i> ") {
							$searchStringStartOffset  = 3 ;
						} else if (strpos($found_name_lc," $search_string_short_lc ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," $search_string_short_lc ") + 1 ;
						} else if (strpos($found_name_lc," <i>$search_string_short_lc ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," <i>$search_string_short_lc ") + 4 ;
						} else if (strpos($found_name_lc," $search_string_short_lc</i> ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," $search_string_short_lc</i> ") + 1 ;
						} else if (substr($found_name_lc,-($searchStringLength+1)) == " $search_string_short_lc") {
							$searchStringStartOffset  = strlen($found_name) - $searchStringLength ;
						} else if (substr($found_name_lc,-($searchStringLength+5)) == " $search_string_short_lc</i>" 
						  || substr($found_name_lc,-($searchStringLength+8)) == " <i>$search_string_short_lc</i>") {
							$searchStringStartOffset  = strlen($found_name) - $searchStringLength - 4 ;
						}
					}
					
					if ($searchStringStartOffset === FALSE) {
						// do nothing
					} else {
						$searchStringEndOffset = $searchStringStartOffset - 1 + $searchStringLength ;
						$nameLength = strlen($found_name) ;
						$nameStartString = substr($found_name,0,$searchStringStartOffset) ;
						$nameMiddleString = substr($found_name,$searchStringStartOffset,$searchStringLength) ;
						$nameEndString = substr($found_name,$searchStringEndOffset+1, 
							  $nameLength-$searchStringEndOffset-1) ;
						$found_name = $nameStartString
									  . "<span class='fieldheader'><u>" . $nameMiddleString . "</u></span>"  
									  . $nameEndString  ;
					}
					$found_name_full = "<p class='fieldheaderblack'><a href=\"$this_link\">" . $found_name . "</a></p>" ; 
					$found_taxon_full = "<p class='fieldheaderblack'><a href=\"$this_link\">" . $found_taxon . "</a></p>" ; 
					echo "<tr bgcolor='$row_color' id='record_$record_id'>\n" ;
					echo "<td colspan=9><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td valign=top>$found_name_full</td>\n" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top>$found_taxon_full</td>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top><p>$status</p></td>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					$image_path = "images/db_logos/" . str_replace(" ","_",$source_db). ".gif" ;
					if (file_exists($image_path)) {
						echo "<td valign=top><p><img src=\"$image_path\" border=0 title=\"Source database: $source_db\"></p></td>" ;
					} else {
						echo "<td valign=top><p>$source_db</p></td>" ;
					}
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					if ($status == "") {
						echo "<td valign=top align=right><p><a href=\"$this_link\">Show tree</a></p></td>\n" ;
					} else {
						echo "<td valign=top align=right><p><a href=\"$this_link\">Show details</a></p></td>\n" ;
						
					}
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=9><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
				} else if ($common_name != "") {
				
					$languages = "" ;
					$language_query = "SELECT DISTINCT `language`
							  FROM `common_names` 
							  WHERE `language` != '' AND `language` IS NOT NULL
								AND `common_name` = '" . addslashes($this_common_name) . "' 
								AND `name_code` = '$name_code' 
								AND `name_code` LIKE BINARY '$name_code' 
							  ORDER by `language`" ;
					$language_result = mysql_query($language_query) or die("Error: MySQL query failed");
					$number_of_languages = mysql_num_rows($language_result);
					if ($number_of_languages  > 0) {
						$languages .= " (" ;
						for ($j = 0; $j < $number_of_languages; $j++) {
							$row2 = mysql_fetch_row($language_result);
							if ($j > 0) {
								$languages .= ", " ;
							}
							$languages .= $row2[0] ;
						}
						$languages .= ")" ;
					}
						
					echo "<tr bgcolor='$row_color' id='record_$i'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td valign=top><p>" . 
					     "<span class='fieldheader'><a href='show_common_name_details.php?name=" . urlencode($this_common_name) . "'>$this_common_name</span></a>" . $languages ;
					echo "</p></td>\n" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top><p>$scientific_name</p></td>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					$image_path = "images/db_logos/" . str_replace(" ","_",$source_db). ".gif" ;
					if (file_exists($image_path)) {
						echo "<td valign=top><p><img src=\"$image_path\" border=0 title=\"Source database: $source_db\"></p></td>" ;
					} else {
						echo "<td valign=top><p>$source_db</p></td>" ;
					}
					
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top align=right ><p><a href='show_common_name_details.php?name=" . urlencode($this_common_name) . "'>Show details</a></p></td>\n" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
				} else if ($area != "") {
					$this_distribution = $row[6] ;
					$area_short = str_replace("%","",$area) ;
					$this_distribution_lc = strtolower($this_distribution) ;
					$area_short_lc = strtolower($area_short) ;
					$searchStringLength = strlen($area_short) ;
					$searchStringStartOffset = strpos($this_distribution_lc,$area_short_lc) ;
					
					if ($searchStringStartOffset === FALSE) {
						// do nothing
					} else {
						$searchStringEndOffset = $searchStringStartOffset - 1 + $searchStringLength ;
						$this_distributionLength = strlen($this_distribution) ;
						$this_distributionStartString = substr($this_distribution,0,$searchStringStartOffset) ;
						$this_distributionMiddleString = substr($this_distribution,$searchStringStartOffset,$searchStringLength) ;
						$this_distributionEndString = substr($this_distribution,$searchStringEndOffset+1, 
							  $this_distributionLength-$searchStringEndOffset-1) ;
						$this_distribution = $this_distributionStartString
									  . "<span class='fieldheader'><u>" . $this_distributionMiddleString . "</u></span>"  
									  . $this_distributionEndString  ;
					}
					echo "<tr bgcolor='$row_color' id='record_$record_id'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td valign=top><p class=\"fieldheaderblack\">" .
					     "<a href='show_species_details.php?record_id=$record_id'>$this_distribution</a>" ;
					echo "</p></td>\n" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top><p class=\"fieldheaderblack\">" .
					     "<a href='show_species_details.php?record_id=$record_id'>$scientific_name</a>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					$image_path = "images/db_logos/" . str_replace(" ","_",$source_db). ".gif" ;
					if (file_exists($image_path)) {
						echo "<td valign=top><p><img src=\"$image_path\" border=0 title=\"Source database: $source_db\"></p></td>" ;
					} else {
						echo "<td valign=top><p>$source_db</p></td>" ;
					}
					
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top align=right><p><a href='show_species_details.php?record_id=$record_id'>Show details</a></p></td>\n" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;

				} else {
					if ($accepted_name_code == "") {
						$accepted_name_code = $name_code ;
					}
					if ($name_code == $accepted_name_code) {
						$accepted_record_id = $record_id ;
						$accepted_genus = $this_genus ;
						$accepted_species = $this_species ;
						$accepted_infraspecies_marker = $this_infraspecies_marker ;
						$accepted_infraspecies = $this_infraspecies ;
						$accepted_author = $this_author ;
					} else {
						$accepted_name_query = "SELECT `record_id` ,
													   `genus` ,
													   `species` ,
													   `infraspecies_marker`,
													   `infraspecies`,
													   `author`
							  					FROM `scientific_names` 
												WHERE `name_code` = '" . addslashes($accepted_name_code) . "' 
												  AND `name_code` LIKE BINARY '" . addslashes($accepted_name_code) . "'" ;
						$accepted_name_result = mysql_query($accepted_name_query) or die("Error: MySQL query failed");
						$row3 = mysql_fetch_row($accepted_name_result);
						$accepted_record_id = $row3[0] ;
						$accepted_genus = $row3[1] ;
						$accepted_species = $row3[2] ;
						$accepted_infraspecies_marker = $row3[3] ;
						$accepted_infraspecies = $row3[4] ;
						$accepted_author = $row3[5] ;
					}
					
					if ($sp2000_status != "accepted name" && $sp2000_status != "provisionally accepted name") {
						$accepted_name = compileScientificName($accepted_genus,$accepted_species,$accepted_infraspecies_marker,$accepted_infraspecies,$this_kingdom,$accepted_author) ;
						if ($accepted_name != "") {
							$sp2000_status .= " for " . $accepted_name ;
						}
					} else {
						$accepted_name = $scientific_name ;
					}
					
					echo "<tr bgcolor='$row_color' id='record_$record_id'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					if ($accepted_name == "") {
						$species_link = "JavaScript:alert(\"Sorry, no accepted name found for $sp2000_status " . addslashes(strip_tags($scientific_name)) . "\");" ;
					} else {
						$species_link = "show_species_details.php?record_id=$record_id" ;
					}
					
					echo "<td valign=top><p><span class='fieldheader'>" .
						 "<a href='$species_link'>$scientific_name</a></span></p></td>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top><p>$sp2000_status</p></td>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					$image_path = "images/db_logos/" . str_replace(" ","_",$source_db). ".gif" ;
					if (file_exists($image_path)) {
						echo "<td valign=top><p><img src=\"$image_path\" border=0 title=\"Source database: $source_db\"></p></td>" ;
					} else {
						echo "<td valign=top><p>$source_db</p></td>" ;
					}
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top align=right><p><a href='$species_link'>Show details</a></p></td>\n" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
				}
				
				if ($row_color == "#EAF2F7") {
					$row_color = "#FAFCFE" ;
				} else {
					$row_color = "#EAF2F7" ;
				}
			}
			mysql_free_result($result) ;
			mysql_close($link) ;
			echo "</table>\n" ;
			echo "</td></tr></table>\n" ;
		}
	}
?>
                              </td>
                            </tr>
                          </table>
                          <br>
<?php
	if ($error == "" && $search_query != "") {
		if ($number_of_records_found > 1) {
			
			// page number links
			$max_number_of_pages_shown = 15 ;
			$last_record_shown = $first_record_shown + $number_of_records_shown_per_page - 1  ;
			if ( $last_record_shown > $number_of_records_found ) {
				$last_record_shown = $number_of_records_found ;
			}
			$number_of_pages = $number_of_records_found / $number_of_records_shown_per_page;
			if ( $number_of_pages != floor($number_of_pages) ) {
				$number_of_pages = ceil($number_of_pages) ;
			}
			$current_page = ceil ($first_record_shown / $number_of_records_shown_per_page) + 1;
			$number_of_pages_not_shown_at_beginning = 0 ;
			$number_of_pages_not_shown_at_end = 0 ;
			if ($number_of_pages > $max_number_of_pages_shown) {
				$number_of_pages_not_shown =  $number_of_pages - $max_number_of_pages_shown ;
				$first_page_shown = $current_page - floor($max_number_of_pages_shown / 2) ;
				if ($first_page_shown < 1) {
					$first_page_shown = 1 ;
				}
				$last_page_shown = $first_page_shown + $max_number_of_pages_shown ;
				if ($last_page_shown > $number_of_pages) {
					$last_page_shown = $number_of_pages ;
					$first_page_shown = $last_page_shown - $max_number_of_pages_shown + 1 ;
					
				}
				$last_page_shown = $first_page_shown + $max_number_of_pages_shown - 1 ;
				if ($last_page_shown > $number_of_pages) {
					$last_page_shown = $number_of_pages ;
					$first_page_shown = $last_page_shown - $max_number_of_pages_shown + 1 ;
					if ($first_page_shown < 1) {
						$first_page_shown = 1 ;
					}
				}
			} else {
				$first_page_shown = 1 ;
				$last_page_shown = $number_of_pages;
			}
			
			$page_number_links = "" ;
			if ($first_page_shown > 1) {
				$page_number_links .= "...&nbsp" ;
			}
			for ($i = $first_page_shown; $i <= $last_page_shown; $i++) {
				$first_title_of_this_set = 0 ;
				if ($i > 1) {
					$first_title_of_this_set += $number_of_records_shown_per_page * ($i-1) ;
				}
				if ($i == $current_page) {
					$page_number_links .= "<b>$i</b>&nbsp;" ;
				} else {
					$page_number_links .= "<a href=\"JavaScript:startAtRecord($first_title_of_this_set)\">$i</a>&nbsp;" ;
				}
			}
			if ($last_page_shown < $number_of_pages) {
				$page_number_links .= "...&nbsp" ;
			}
			
			// next and previous links
			$first_record_of_previous_set = $first_record_shown -$number_of_records_shown_per_page ;
			$first_record_of_next_set = $first_record_shown + $number_of_records_shown_per_page ;
			
			if ($number_of_records_found > $number_of_records_to_show) {
				echo "<input type='hidden' name='search_query' value='" . urlencode($search_query) . "'>" ;
				echo "<input type='hidden' name='first_record_shown' value='$first_record_shown'>" ;
				echo "<input type='hidden' name='number_of_records_shown_per_page' value='$number_of_records_shown_per_page'>" ;
				echo "<input type='hidden' name='number_of_records_found' value='$number_of_records_found'>" ;
				echo "<div align=center>\n" ;
				echo "<p>" ;
				if ($first_record_shown > 0)  {
					echo "<a href='JavaScript:startAtRecord($first_record_of_previous_set)'><b><span class='nextpreviousarrows'><<</span> Previous</b></a>&nbsp;|&nbsp;" ;
				} else {
					echo "Page " ;
				}
				echo $page_number_links ;
				if ($first_record_shown + $number_of_records_to_show < $number_of_records_found ) {
					echo "|&nbsp;<a href='JavaScript:startAtRecord($first_record_of_next_set)'><b>Next <span class='nextpreviousarrows'>>></span></b></a>" ;
				}
				echo "</p>" ;
				echo "</div>\n" ;
			}
			
			echo "<p align=center><br>\n" ;
			if ($number_of_records_found > 0) {
				echo "<a href='JavaScript:document.export_results.submit()'>Export search results</a> | " ;
			}
			echo "<a href='$new_search_url'>New search</a></p>" ;
		} else {
			echo "<p><a href='$new_search_url'>New search</a></p>" ;
		}
	}
	
	$variables_to_store = Array(
	  "search_type", 
	  "search_string", 
	  "kingdom", 
	  "phylum", 
	  "tax_class", 
	  "order", 
	  "superfamily",
	  "family", 
	  "genus", 
	  "species", 
	  "infraspecies", 
	  "common_name", 
	  "area", 
	  "match_whole_words", 
	  "sort_by_column", 
	  "number_of_records_found", 
	  "number_of_records_shown_per_page", 
	  "first_record_shown", 
	  "number_of_records_to_show" ) ;
	foreach($variables_to_store as $variable){
		$_SESSION["ac_$variable"] = $$variable ;
	}
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
  <form name="start_at_record" method="get" action="search_results.php">
    <input type="hidden" name="search_type" value="<?php echo urlencode($search_type) ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
    <input type="hidden" name="search_string" value="<?php echo urlencode($search_string) ?>">
    <input type="hidden" name="kingdom" value="<?php echo urlencode($kingdom) ?>">
    <input type="hidden" name="phylum" value="<?php echo urlencode($phylum) ?>">
    <input type="hidden" name="tax_class" value="<?php echo urlencode($tax_class) ?>">
    <input type="hidden" name="order" value="<?php echo urlencode($order) ?>">
    <input type="hidden" name="superfamily" value="<?php echo urlencode($superfamily) ?>">
    <input type="hidden" name="family" value="<?php echo urlencode($family) ?>">
    <input type="hidden" name="genus" value="<?php echo urlencode($genus) ?>">
    <input type="hidden" name="species" value="<?php echo urlencode($species) ?>">
    <input type="hidden" name="infraspecies" value="<?php echo urlencode($infraspecies) ?>">
    <input type="hidden" name="common_name" value="<?php echo urlencode($common_name) ?>">
    <input type="hidden" name="area" value="<?php echo urlencode($area) ?>">
    <input type="hidden" name="match_whole_words" value="<?php echo $match_whole_words ?>">
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
    <input type="hidden" name="first_record_shown">
  </form>
  <form name="sort_by_column" method="get" action="search_results.php">
    <input type="hidden" name="search_type" value="<?php echo urlencode($search_type) ?>">
    <input type="hidden" name="kingdom" value="<?php echo urlencode($kingdom) ?>">
    <input type="hidden" name="phylum" value="<?php echo urlencode($phylum) ?>">
    <input type="hidden" name="tax_class" value="<?php echo urlencode($tax_class) ?>">
    <input type="hidden" name="order" value="<?php echo urlencode($order) ?>">
    <input type="hidden" name="superfamily" value="<?php echo urlencode($superfamily) ?>">
    <input type="hidden" name="family" value="<?php echo urlencode($family) ?>">
    <input type="hidden" name="genus" value="<?php echo urlencode($genus) ?>">
    <input type="hidden" name="species" value="<?php echo urlencode($species) ?>">
    <input type="hidden" name="infraspecies" value="<?php echo urlencode($infraspecies) ?>">
    <input type="hidden" name="common_name" value="<?php echo urlencode($common_name) ?>">
    <input type="hidden" name="area" value="<?php echo urlencode($area) ?>">
    <input type="hidden" name="search_string" value="<?php echo urlencode($search_string) ?>">
    <input type="hidden" name="match_whole_words" value="<?php echo $match_whole_words ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
    <input type="hidden" name="sort_by_column">
  </form>
  <form name="export_results" method="get" action="export_search_results.php">
    <input type="hidden" name="kingdom" value="<?php echo urlencode($kingdom) ?>">
    <input type="hidden" name="phylum" value="<?php echo urlencode($phylum) ?>">
    <input type="hidden" name="tax_class" value="<?php echo urlencode($tax_class) ?>">
    <input type="hidden" name="order" value="<?php echo urlencode($order) ?>">
    <input type="hidden" name="superfamily" value="<?php echo urlencode($superfamily) ?>">
    <input type="hidden" name="family" value="<?php echo urlencode($family) ?>">
    <input type="hidden" name="genus" value="<?php echo urlencode($genus) ?>">
    <input type="hidden" name="species" value="<?php echo urlencode($species) ?>">
    <input type="hidden" name="infraspecies" value="<?php echo urlencode($infraspecies) ?>">
    <input type="hidden" name="common_name" value="<?php echo urlencode($common_name) ?>">
    <input type="hidden" name="area" value="<?php echo urlencode($area) ?>">
    <input type="hidden" name="search_string" value="<?php echo urlencode($search_string) ?>">
    <input type="hidden" name="match_whole_words" value="<?php echo $match_whole_words ?>">
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
    <input type="hidden" name="search_type" value="<?php echo urlencode($search_type) ?>">
   </form>
</div>
</body>
</html>
