<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Search results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script language="JavaScript" type="text/javascript">
function scrollDownToRecord(recordID,numberOfRecordsToShow) {
	if (numberOfRecordsToShow > 10 && recordID != "" && numberOfRecordsToShow != "") {
		var theRowToShow = 'record_' + recordID ;
		if (document.all) {
			eval ("document.all." + theRowToShow + ".scrollIntoView()") ;
		} else {
			eval ("document.getElementById('" + theRowToShow +"').scrollIntoView(true)") ;
		}
	}
}
function startAtRecord (thisPage) {
	document.start_at_record.first_record_shown.value = thisPage ;
	document.start_at_record.submit() ;
}
</SCRIPT>
<?php
	function compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$kingdom,$author) {
		if ($genus . $species . $infraspecies == "") {
			return "" ;
		} else if ($kingdom == "Viruses") {
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
				$scientific_name .= " $author"  ;
			}
		}
		return $scientific_name ;
	}
	
	$search_kingdom = "" ;
	$search_phylum = "" ;
	$search_class = "" ;
	$search_order = "" ;
	$search_family = "" ;
	$search_genus = "" ;
	$search_species = "" ;
	$search_infraspecies = "" ;
	$search_common_name = "" ;
	$search_distribution = "" ;
	$search_simple = "" ;
	$search_mode = "" ;
	$sort_by_column = 1 ;
	$record_id  = "" ;
	if (isset($_REQUEST["search_kingdom"])) {
		$search_kingdom = trim(urldecode($_REQUEST["search_kingdom"])) ;
	}
	if (isset($_REQUEST["search_phylum"])) {
		$search_phylum = trim(urldecode($_REQUEST["search_phylum"])) ;
	}
	if (isset($_REQUEST["search_class"])) {
		$search_class = trim(urldecode($_REQUEST["search_class"])) ;
	}
	if (isset($_REQUEST["search_order"])) {
		$search_order = trim(urldecode($_REQUEST["search_order"])) ;
	}
	if (isset($_REQUEST["search_family"])) {
		$search_family = trim(urldecode($_REQUEST["search_family"])) ;
	}
	if (isset($_REQUEST["search_genus"])) {
		$search_genus = trim(urldecode($_REQUEST["search_genus"])) ;
	}
	if (isset($_REQUEST["search_species"])) {
		$search_species = trim(urldecode($_REQUEST["search_species"])) ;
	}
	if (isset($_REQUEST["search_infraspecies"])) {
		$search_infraspecies = trim(urldecode($_REQUEST["search_infraspecies"])) ;
	}
	if (isset($_REQUEST["search_common_name"])) {
		$search_common_name = trim(urldecode($_REQUEST["search_common_name"])) ;
	}
	if (isset($_REQUEST["search_distribution"])) {
		$search_distribution = trim(urldecode($_REQUEST["search_distribution"])) ;
	}
	if (isset($_REQUEST["search_simple"])) {
		$search_simple = trim(urldecode($_REQUEST["search_simple"])) ;
	}
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = urldecode($_REQUEST["search_mode"]) ;
	}
	
	if (isset($_REQUEST["sort_by_column"])) {
		$sort_by_column = $_REQUEST["sort_by_column"] ;
	}
	
	$number_of_records_shown_per_page = 10 ;
	$first_record_shown = 0 ;
	$number_of_records_found = 0 ;
	$query_number_of_hits = 0 ;
	$search_page = "" ;
	$search_query = "" ;
	$error = "" ;
	if (isset($_REQUEST["number_of_records_shown_per_page"])) {
		$number_of_records_shown_per_page = $_REQUEST["number_of_records_shown_per_page"] ;
	}
	if (isset($_REQUEST["first_record_shown"])) {
		$first_record_shown = $_REQUEST["first_record_shown"] ;
	}
	if (isset($_REQUEST["number_of_records_found"])) {
		$number_of_records_found = $_REQUEST["number_of_records_found"] ;
	}
	if (isset($_REQUEST["search_page"])) {
		$search_page = urldecode($_REQUEST["search_page"]) ;
	} else if (isset($_SERVER['HTTP_REFERER'])) {
		$search_page = $_SERVER['HTTP_REFERER'] ;
	}
	if ($search_page == "") {
		$search_page = "search.php" ;
	}
	if (isset($_REQUEST["search_query"])) {
	
		//use existing query
		
		$search_query = urldecode($_REQUEST["search_query"]) ;
		if (isset($_REQUEST["record_id"])) {
			$record_id = $_REQUEST["record_id"] ;
		}
		
	} else {
	
		//new search
			
		// change * to % in search string
		
		$search_kingdom = str_replace("*","%",$search_kingdom) ;
		$search_phylum = str_replace("*","%",$search_phylum) ;
		$search_class = str_replace("*","%",$search_class) ;
		$search_order = str_replace("*","%",$search_order) ;
		$search_family = str_replace("*","%",$search_family) ;
		$search_genus = str_replace("*","%",$search_genus) ;
		$search_species = str_replace("*","%",$search_species) ;
		$search_infraspecies = str_replace("*","%",$search_infraspecies) ;
		$search_common_name = str_replace("*","%",$search_common_name) ;
		$search_distribution = str_replace("*","%",$search_distribution) ;
		$search_simple = str_replace("*","%",$search_simple) ;
		
		if ($search_simple . $search_distribution . $search_common_name .
		  $search_query . $search_kingdom . $search_phylum . $search_class . $search_order .
		  $search_family . $search_genus . $search_species . $search_infraspecies == "" ) {
			$error = "Error: you did not enter a search string." ;
		} else if (strlen($search_simple) == 1 || strlen($search_distribution) == 1 || strlen($search_common_name) == 1 ) {
			$error = "Please enter a search string of at least two characters." ;
		} 
		
		if ($error == "") {
			//compile query
			
			$target = "search results" ;
			include "compile_query.php" ;
			
			$search_query = "" ;
			if ($select != "" ) {
				if ($search_simple == "") {
				
					$search_query = "SELECT " . $select . " 
									 FROM " . $from . " 
									 WHERE " . $where . " 
									 ORDER BY " . $order_by ;
				} else {
					$search_query = "SELECT $select FROM $from WHERE $where 
							   UNION SELECT $select2 FROM $from2 WHERE $where2
									 ORDER BY $order_by" ;
				}
			}
		}
		
	}
	
	if ($error == "") {
	
			include "connect_to_database.php" ;
			if ($query_number_of_hits != "") {
				$result = mysql_query($query_number_of_hits) or die("Query failed : " . mysql_error());
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_row($result);
					$number_of_records_found = $row[0] ;
				}
				mysql_free_result($result) ;
				mysql_close($link) ;
			}
			if ($number_of_records_found == "") {
				$result = mysql_query($search_query) or die("Query failed : " . mysql_error());
				$number_of_records_found = mysql_num_rows($result) ;
				if ($number_of_records_found < $number_of_records_shown_per_page) {
					$number_of_records_shown_per_page = $number_of_records_found ;
				}
				$number_of_records_to_show = $number_of_records_shown_per_page ;
			} else {
				$partial_query = $search_query . " LIMIT $first_record_shown,$number_of_records_shown_per_page";
				include "connect_to_database.php" ;
				$result = mysql_query($partial_query) or die("Query failed : " . mysql_error());
				$number_of_records_to_show = mysql_num_rows($result);

			}
			
		if ($number_of_records_found > 0 ) {
			$first_record_of_previous_set = $first_record_shown - $number_of_records_shown_per_page ;
			if ($first_record_of_previous_set < 0) {
				$first_record_of_previous_set = 0 ;
			}
			$first_record_of_next_set = $first_record_shown + $number_of_records_shown_per_page ;
			if ($first_record_of_next_set > $number_of_records_found) {
				$first_record_of_next_set = $number_of_records_found ;
			}
		} else {
			$number_of_records_to_show = 0 ;
		}
	}
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="JavaScript:scrollDownToRecord('<?php echo $record_id ?>','<?php echo $number_of_records_to_show ?>'); moveMenu();" onScroll="moveMenu();">
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
	if ($search_simple != "") {
		echo "Search results for \"" . stripslashes($search_simple) . "\"" ;
	} else if ($search_common_name != "") {
		echo "Search results for common names" ;
	} else if ($search_distribution != "") {
		echo "Search results for distribution" ;
	} else if ($search_kingdom . $search_phylum . $search_class . $search_order .  $search_family . 
	    $search_genus . $search_species . $search_infraspecies != "") {
		echo "Search results for scientific names" ;
	} else {
		echo "Search results" ;
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
		echo "<a href='JavaScript:document.new_search.submit()'>New search</a></p>" ;
	} else if ($search_query == "") {
	  	echo "<p>Could not perform search because you did not enter any search criteria.</p>\n" ;
		echo "<p align=center><br><br>\n" ;
		echo "<a href='JavaScript:document.new_search.submit()'>New search</a></p>" ;
	} else {
		// display results
		echo "<script language='JavaScript' type='text/javascript'>\n" ;
		echo "document.write(\"<p>Records found: $number_of_records_found<p>\");\n" ;
		echo "</SCRIPT>\n" ;
	}
?>
                              </td>
                              <td align=right> 
                                <?php
	if ($search_query != "") {
		if ($number_of_records_found > 1) {
			echo "<form name='change_number_of_records_shown_per_page' method='post' action='search_results.php'>\n" ;
			echo "<input type='hidden' name='search_page' value='" . urlencode($search_page) . "'>\n" ;
			echo "<input type='hidden' name='search_query' value='" . urlencode($search_query) . "'>\n" ;
			echo "<input type='hidden' name='first_record_shown' value='0'>\n" ;
			echo "<input type='hidden' name='number_of_records_found' value='$number_of_records_found'>\n" ;
			echo "<input type='hidden' name='search_kingdom' value='" . urlencode($search_kingdom) . "'>\n" ;
			echo "<input type='hidden' name='search_phylum' value='" . urlencode($search_phylum) . "'>\n" ;
			echo "<input type='hidden' name='search_class' value='" . urlencode($search_class) . "'>\n" ;
			echo "<input type='hidden' name='search_order' value='" . urlencode($search_order) . "'>\n" ;
			echo "<input type='hidden' name='search_family' value='" . urlencode($search_family) . "'>\n" ;
			echo "<input type='hidden' name='search_genus' value='" . urlencode($search_genus) . "'>\n" ;
			echo "<input type='hidden' name='search_species' value='" . urlencode($search_species) . "'>\n" ;
			echo "<input type='hidden' name='search_infraspecies' value='" . urlencode($search_infraspecies) . "'>\n" ;
			echo "<input type='hidden' name='search_common_name' value='" . urlencode($search_common_name) . "'>\n" ;
			echo "<input type='hidden' name='search_distribution' value='" . urlencode($search_distribution) . "'>\n" ;
			echo "<input type='hidden' name='search_simple' value='" . urlencode($search_simple) . "'>\n" ;
			echo "<input type='hidden' name='search_mode' value='$search_mode'>\n" ;
			echo "<input type='hidden' name='sort_by_column' value='$sort_by_column'>\n" ;
			echo "<p>Show <input type='text' name='number_of_records_shown_per_page' size='4' value='$number_of_records_shown_per_page'> " ;
			echo "records per page&nbsp;&nbsp;<input type=\"submit\" name=\"Submit\" class=\"formbutton\" value=\"Update\"></p>\n" ;
			echo "</form>\n" ;
		} else {
			echo "<p><br><br></p>\n" ;
		}
	}
?>
                              </td>
                            </tr>
                            <tr> 
                              <td colspan=2> 
                                <?php
	if ($search_query != "") {
		if ($number_of_records_found > 0) {
			echo "<table border=0 cellspacing=0 cellpadding=1 width='100%' bgcolor='#EDEBEB'>\n<tr>\n<td>\n" ;
			echo "<table border=0 cellspacing=0 cellpadding=0 width='100%' bgcolor='#FAFCFE'>\n<tr>\n<td>\n" ;
			echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n" ;
			if ($search_simple != "") {
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
			} else if ($search_common_name != "") {
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
			} else if ($search_distribution != "") {
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
				if ($search_simple != "") {
					$record_id = $row[0] ;
					$found_name = $row[1] ;
					$found_taxon = $row[2] ;
					$name_code = $row[3] ;
					$status = $row[5] ;
					$source_db = $row[7] ;
					$is_accepted_name = $row[8] ;
				} else if ($search_common_name != "") {
					$common_name = $row[0] ;
					$genus = $row[1] ;
					$species = $row[2] ;
					$infraspecies_marker = $row[3] ;
					$infraspecies = $row[4] ;
					$author = $row[5] ;
					$name_code = $row[6] ;
					$source_db = $row[7] ;
					$scientific_name = "<i>$genus $species</i>" ;
					if ($infraspecies_marker != "") {
						$scientific_name .= " $infraspecies_marker" ;
					}
					if ($infraspecies != "") {
						$scientific_name .= " <i>$infraspecies</i>" ;
					}
					if ($author != "") {
						$scientific_name .= " $author" ;
					}
				} else if ($search_distribution != "") {
					$record_id = $row[0] ;
					$genus = $row[1] ;
					$species = $row[2] ;
					$infraspecies_marker = $row[3] ;
					$infraspecies = $row[4] ;
					$author = $row[5] ;
					$name_code = $row[6] ;
					$kingdom = $row[7] ;
					$source_db = $row[8] ;
					$scientific_name = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$kingdom,$author) ;
				} else {
					$record_id = $row[0] ;
					$genus = $row[1] ;
					$species = $row[2] ;
					$infraspecies_marker = $row[3] ;
					$infraspecies = $row[4] ;
					$author = $row[5] ;
					$name_code = $row[6] ;
					$accepted_name_code = $row[7] ;
					$sp2000_status = $row[8] ;
					$source_db = $row[9] ;
					$family_id = $row[10] ;
					
					$kingdom = "" ;
					if ($family_id  != "") {
						$kingdom_query = "SELECT `kingdom` FROM `families` WHERE `record_id` = '$family_id' " ;
						$kingdom_result = mysql_query($kingdom_query) or die("Query failed : " . mysql_error());	
						if (mysql_num_rows($kingdom_result) > 0) {
							$kingdom_row = mysql_fetch_row($kingdom_result);
							$kingdom = $kingdom_row[0] ;
						}
					}
					
					$scientific_name = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$kingdom,$author) ;
				}
				
				if ($search_simple != "") {
					if ($status == "zzzzzz") {
						$status = "" ;
					}
					if ($status == "common name") {
						$languages = "" ;
						$language_query = "SELECT DISTINCT `language`
								  FROM `common_names` 
								  WHERE `language` != ''
								    AND `common_name` = '" . addslashes($found_name) . "' 
									AND `name_code` = '$name_code' 
								  ORDER by `language`" ;
						$language_result = mysql_query($language_query) or die("Query failed : " . mysql_error());
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
								  WHERE `name_code` = '$name_code' " ;
							$scientific_name_result = mysql_query($scientific_name_query) or die("Query failed : " . mysql_error());
							$row2 = mysql_fetch_row($scientific_name_result);
							$scientific_genus = $row2[0] ;
							$scientific_species = $row2[1] ;
							$scientific_infraspecies_marker = $row2[2] ;
							$scientific_infraspecies = $row2[3] ;
							$scientific_author = $row2[4] ;
							$kingdom_query = "SELECT `families`.`kingdom`  
								  FROM `families`,`scientific_names` 
								  WHERE `scientific_names`.`name_code` = '$name_code' 
									AND `scientific_names`.`family_id` = `families`.`record_id` " ;
							$kingdom_result = mysql_query($kingdom_query) or die("Query failed : " . mysql_error());
							$row2 = mysql_fetch_row($kingdom_result);
							$kingdom = $row2[0] ;
							
							$scientific_name = compileScientificName($scientific_genus,$scientific_species,$scientific_infraspecies_marker,$scientific_infraspecies,$kingdom,$scientific_author) ;
							if ($scientific_name != "") {
								$status .= " for $scientific_name" ; 
							}
						}
						$this_link = "JavaScript:showCommonNameDetails('" . urlencode(addslashes($found_name)) . "')" ;
						$found_name .= $languages ;
					} else if ($found_taxon == "Species" || $found_taxon == "Infraspecies") {
						$species_query = "SELECT `record_id`, `author` 
							  FROM `scientific_names` 
							  WHERE `name_code` = '" . addslashes($name_code) . "'" ;
						$species_result = mysql_query($species_query) or die("Query failed : " . mysql_error());
						$row2 = mysql_fetch_row($species_result);
						$species_id = $row2[0] ;
						$species_author = $row2[1] ;
						
						$kingdom_query = "SELECT `kingdom`
							  FROM `families` ,`scientific_names` 
							  WHERE `scientific_names`.`name_code` = '" . addslashes($name_code) . "' 
							    AND `scientific_names`.`family_id` = `families`.`record_id` " ;
						$kingdom_result = mysql_query($kingdom_query) or die("Query failed : " . mysql_error());
						$row3 = mysql_fetch_row($kingdom_result);
						$kingdom = $row3[0] ;
						
						if ($species_author != "" && $kingdom != "Viruses") {
							$found_name .= " $species_author" ;
						}
						$this_link = "JavaScript:showSpeciesDetails('$species_id')" ;
						if ($status != "accepted name" && $status != "provisionally accepted name") {
							$accepted_name = "" ;
							$accepted_name_code_query = "SELECT `accepted_name_code` 
								  FROM `scientific_names` 
								  WHERE `record_id` = '$species_id' " ;
							$accepted_name_code_result = mysql_query($accepted_name_code_query) or die("Query failed : " . mysql_error());
							$row2 = mysql_fetch_row($accepted_name_code_result);
							$accepted_name_code = $row2[0] ;
							if ($accepted_name_code == "") {
								$accepted_name_code = "unknown" ;
							} else {
								$accepted_name_query = "SELECT `genus` , `species` , `infraspecies_marker`, `infraspecies` , `author` 
									  FROM `scientific_names` 
									  WHERE `name_code` = '$accepted_name_code' " ;
								$accepted_name_result = mysql_query($accepted_name_query) or die("Query failed : " . mysql_error());
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
								$kingdom_result = mysql_query($kingdom_query) or die("Query failed : " . mysql_error());
								$row2 = mysql_fetch_row($kingdom_result);
								$kingdom = $row2[0] ;
								$accepted_name = compileScientificName($accepted_genus,$accepted_species,$accepted_infraspecies_marker,$accepted_infraspecies,$kingdom,$accepted_author) ;
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
							$this_link = "JavaScript:showTaxonomicTree('$record_id')" ;
						} else {
							$this_link = "JavaScript:alert('Sorry, can\'t show $found_taxon " . 
								addslashes(strip_tags($found_name)) . " in the " .
								"taxonomic tree because this taxon does not contain any accepted names " .
								"(only synonyms and/or misapplied names).')" ;
						}
					}
					
					$search_simple_short = str_replace("%","",$search_simple) ;
					$found_name_lc = strtolower($found_name) ;
					$search_simple_short_lc = strtolower($search_simple_short) ;
					$searchStringLength = strlen($search_simple_short) ;
					$searchStringStartOffset = strpos($found_name_lc,$search_simple_short_lc) ;
					if ($found_name_lc == $search_simple_short_lc) {
						$searchStringStartOffset  = 0 ;
					} else if ($found_name_lc == "<i>$search_simple_short_lc</i>") {
						$searchStringStartOffset  = 3 ;
					} else if ($search_mode == "whole words" ) {
						if (substr($found_name_lc,0,$searchStringLength+1) == "$search_simple_short_lc ") {
							$searchStringStartOffset  = 0 ;
						} else if (substr($found_name_lc,0,$searchStringLength+4) == "<i>$search_simple_short_lc " 
						  || substr($found_name_lc,0,$searchStringLength+8) == "<i>$search_simple_short_lc</i> ") {
							$searchStringStartOffset  = 3 ;
						} else if (strpos($found_name_lc," $search_simple_short_lc ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," $search_simple_short_lc ") + 1 ;
						} else if (strpos($found_name_lc," <i>$search_simple_short_lc ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," <i>$search_simple_short_lc ") + 4 ;
						} else if (strpos($found_name_lc," $search_simple_short_lc</i> ") !== FALSE) {
							$searchStringStartOffset  = strpos($found_name_lc," $search_simple_short_lc</i> ") + 1 ;
						} else if (substr($found_name_lc,-($searchStringLength+1)) == " $search_simple_short_lc") {
							$searchStringStartOffset  = strlen($found_name) - $searchStringLength ;
						} else if (substr($found_name_lc,-($searchStringLength+5)) == " $search_simple_short_lc</i>" 
						  || substr($found_name_lc,-($searchStringLength+8)) == " <i>$search_simple_short_lc</i>") {
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
				} else if ($search_common_name != "") {
				
					$languages = "" ;
					$language_query = "SELECT DISTINCT `language`
							  FROM `common_names` 
							  WHERE `language` != ''
								AND `common_name` = '" . addslashes($common_name) . "' 
								AND `name_code` = '$name_code' 
							  ORDER by `language`" ;
					$language_result = mysql_query($language_query) or die("Query failed : " . mysql_error());
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
					     "<span class='fieldheader'><a href='JavaScript:showCommonNameDetails(\"" . urlencode(addslashes($common_name)) . "\")'>$common_name</span></a>" . $languages ;
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
					echo "<td valign=top align=right ><p><a href='JavaScript:showCommonNameDetails(\"" . urlencode(addslashes($common_name)) . "\")'>Show details</a></p></td>\n" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
				} else if ($search_distribution != "") {
					$distribution = $row[6] ;
					$search_distribution_short = str_replace("%","",$search_distribution) ;
					$distribution_lc = strtolower($distribution) ;
					$search_distribution_short_lc = strtolower($search_distribution_short) ;
					$searchStringLength = strlen($search_distribution_short) ;
					$searchStringStartOffset = strpos($distribution_lc,$search_distribution_short_lc) ;
					
					if ($searchStringStartOffset === FALSE) {
						// do nothing
					} else {
						$searchStringEndOffset = $searchStringStartOffset - 1 + $searchStringLength ;
						$distributionLength = strlen($distribution) ;
						$distributionStartString = substr($distribution,0,$searchStringStartOffset) ;
						$distributionMiddleString = substr($distribution,$searchStringStartOffset,$searchStringLength) ;
						$distributionEndString = substr($distribution,$searchStringEndOffset+1, 
							  $distributionLength-$searchStringEndOffset-1) ;
						$distribution = $distributionStartString
									  . "<span class='fieldheader'><u>" . $distributionMiddleString . "</u></span>"  
									  . $distributionEndString  ;
					}
					echo "<tr bgcolor='$row_color' id='record_$record_id'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td valign=top><p class=\"fieldheaderblack\">" .
					     "<a href='JavaScript:showSpeciesDetails($record_id)'>$distribution</a>" ;
					echo "</p></td>\n" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top><p class=\"fieldheaderblack\">" .
					     "<a href='JavaScript:showSpeciesDetails($record_id)'>$scientific_name</a>" ;
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					
					$image_path = "images/db_logos/" . str_replace(" ","_",$source_db). ".gif" ;
					if (file_exists($image_path)) {
						echo "<td valign=top><p><img src=\"$image_path\" border=0 title=\"Source database: $source_db\"></p></td>" ;
					} else {
						echo "<td valign=top><p>$source_db</p></td>" ;
					}
					
					echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
					echo "<td valign=top align=right><p><a href='JavaScript:showSpeciesDetails($record_id)'>Show details</a></p></td>\n" ;
					echo "</tr>\n" ;
					echo "<tr bgcolor='$row_color'>\n" ;
					echo "<td colspan=7><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
					echo "</tr>\n" ;

				} else {
					if ($accepted_name_code == "") {
						$accepted_name_code = $name_code ;
					}
					if ($name_code == $accepted_name_code) {
						$accepted_genus = $genus ;
						$accepted_species = $species ;
						$accepted_infraspecies_marker = $infraspecies_marker ;
						$accepted_infraspecies = $infraspecies ;
						$accepted_author = $author ;
					} else {
						$accepted_name_query = "SELECT `genus` ,
													   `species` ,
													   `infraspecies_marker`,
													   `infraspecies`,
													   `author`
							  					FROM `scientific_names` 
												WHERE `name_code` = '" . addslashes($accepted_name_code) . "'" ;
						$accepted_name_result = mysql_query($accepted_name_query) or die("Query failed : " . mysql_error());
						$row3 = mysql_fetch_row($accepted_name_result);
						$accepted_genus = $row3[0] ;
						$accepted_species = $row3[1] ;
						$accepted_infraspecies_marker = $row3[2] ;
						$accepted_infraspecies = $row3[3] ;
						$accepted_author = $row3[4] ;
					}
					
					if ($sp2000_status != "accepted name" && $sp2000_status != "provisionally accepted name") {
						$accepted_name = compileScientificName($accepted_genus,$accepted_species,$accepted_infraspecies_marker,$accepted_infraspecies,$kingdom,$accepted_author) ;
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
						$species_link = "JavaScript:showSpeciesDetails($record_id)" ;
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
	if ($search_query != "") {
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
			echo "<a href='JavaScript:document.new_search.submit()'>New search</a></p>" ;
		} else {
			echo "<p><a href='JavaScript:document.new_search.submit()'>New search</a></p>" ;
		}
	}
	
	function styleColumnHeader ($column_name, $current_column, $selected_column) {
		if ($current_column == $selected_column) {
			return "<u>$column_name</u>" ;
		} else {
			return "<a href='JavaScript:sortByColumn($current_column)'>$column_name</a>" ;
		}
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
  <form name="start_at_record" method="post" action="search_results.php">
    <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
    <input type="hidden" name="search_query" value="<?php echo urlencode($search_query) ?>">
    <input type="hidden" name="first_record_shown">
    <input type="hidden" name="number_of_records_shown_per_page" value="<?php echo $number_of_records_shown_per_page ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
    <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
    <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
    <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
    <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
    <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
    <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
    <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
    <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
    <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
    <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
    <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
    <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
  </form>
  <form name="sort_by_column" method="post" action="search_results.php">
    <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
    <input type="hidden" name="number_of_records_shown_per_page" value="<?php echo $number_of_records_shown_per_page ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
    <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
    <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
    <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
    <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
    <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
    <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
    <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
    <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
    <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
    <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
    <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
    <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
    <input type="hidden" name="sort_by_column">
  </form>
  <form name="show_species_details" method="post" action="show_species_details.php">
    <input type="hidden" name="record_id">
    <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
    <input type="hidden" name="search_query" value="<?php echo urlencode($search_query) ?>">
    <input type="hidden" name="first_record_shown" value="<?php echo $first_record_shown ?>">
    <input type="hidden" name="number_of_records_shown_per_page" value="<?php echo $number_of_records_shown_per_page ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
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
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
  </form>
  <form name="show_common_name" method="post" action="show_common_name_details.php">
    <input type="hidden" name="common_name">
    <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
    <input type="hidden" name="search_query" value="<?php echo urlencode($search_query) ?>">
    <input type="hidden" name="first_record_shown" value="<?php echo $first_record_shown ?>">
    <input type="hidden" name="number_of_records_shown_per_page" value="<?php echo $number_of_records_shown_per_page ?>">
    <input type="hidden" name="number_of_records_found" value="<?php echo $number_of_records_found ?>">
    <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
    <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
    <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
  </form>
  <form name="new_search" method="post" action="<?php echo $search_page ?>">
    <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
    <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
    <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
    <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
    <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
    <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
    <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
    <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
    <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
    <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
    <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
    <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
  </form>
  <form name="export_results" method="post" action="export_search_results.php">
    <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
    <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
    <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
    <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
    <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
    <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
    <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
    <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
    <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
    <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
    <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
    <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
    <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
  </form>
  <form name="show_tree" method="post" action="browse_taxa.php">
    <input type="hidden" name="selected_taxon" value="">
  </form>
</div>
</body>
</html>
