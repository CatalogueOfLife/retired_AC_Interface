<?php
	function compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies, 
	  $this_kingdom,$this_author) {
		if ($this_kingdom == "Viruses" || $this_kingdom = "Subviral agents") {
			$scientific_name = $this_species ;
		} else {
			$scientific_name = "$this_genus $this_species" ;
			if ($this_infraspecies != "") {
				if ($this_infraspecies_marker != "") {
					$scientific_name .= " $this_infraspecies_marker" ;
				}
				$scientific_name .= " $this_infraspecies"  ;
			}
			if ($this_author != "") {
				$scientific_name .= " $this_author"  ;
			}
		}
		return $scientific_name ;
	}
	
	$kingdom = "" ;
	$phylum = "" ;
	$tax_class = "" ;
	$order = "" ;
	$superfamily = "" ;
	$family = "" ;
	$genus = "" ;
	$species = "" ;
	$infraspecies = "" ;
	$common_name = "" ;
	$area = "" ;
	$search_string = "" ;
	$match_whole_words = "off" ;
	$sort_by_column = "" ;
	$search_type = "" ;
	if (isset($_REQUEST["kingdom"])) {
		$kingdom = urldecode($_REQUEST["kingdom"]) ;
	}
	if (isset($_REQUEST["phylum"])) {
		$phylum = urldecode($_REQUEST["phylum"]) ;
	}
	if (isset($_REQUEST["tax_class"])) {
		$tax_class = urldecode($_REQUEST["tax_class"]) ;
	}
	if (isset($_REQUEST["order"])) {
		$order = urldecode($_REQUEST["order"]) ;
	}
	if (isset($_REQUEST["superfamily"])) {
		$superfamily = urldecode($_REQUEST["superfamily"]) ;
	}
	if (isset($_REQUEST["family"])) {
		$family = urldecode($_REQUEST["family"]) ;
	}
	if (isset($_REQUEST["genus"])) {
		$genus = urldecode($_REQUEST["genus"]) ;
	}
	if (isset($_REQUEST["species"])) {
		$species = $_REQUEST["species"] ;
	}
	if (isset($_REQUEST["infraspecies"])) {
		$infraspecies = urldecode($_REQUEST["infraspecies"]) ;
	}
	if (isset($_REQUEST["common_name"])) {
		$common_name = urldecode($_REQUEST["common_name"]) ;
	}
	if (isset($_REQUEST["area"])) {
		$area = urldecode($_REQUEST["area"]) ;
	}
	if (isset($_REQUEST["search_string"])) {
		$search_string = urldecode($_REQUEST["search_string"]) ;
	}
	if (isset($_REQUEST["match_whole_words"])) {
		$match_whole_words = urldecode($_REQUEST["match_whole_words"]) ;
	}
	if (isset($_REQUEST["sort_by_column"])) {
		$sort_by_column = urldecode($_REQUEST["sort_by_column"]) ;
	}
	if (isset($_REQUEST["search_type"])) {
		$search_type = $_REQUEST["search_type"] ;
	}
	
	//compile query 
	
	$target = "export file" ;
	include "compile_query.php" ;
	if ($search_string == "") {
		$query = "SELECT " . $select . " 
						 FROM " . $from . " 
						 WHERE " . $where . " 
						 ORDER BY " . $order_by ;
	} else {
		$query = "SELECT $select FROM $from WHERE $where 
				   UNION SELECT $select2 FROM $from2 WHERE $where2
						 ORDER BY $order_by" ;
	}
	
	//perform query
	
	include "connect_to_database.php" ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$header = "" ;
	
	if ($search_string == "") {
		$count = mysql_num_fields($result);
		for ($i = 0; $i < $count; $i++){
			$field_name = mysql_field_name($result, $i) ;
			$field_name = str_replace("_"," ",$field_name) ;
			$header .= $field_name ."\t" ;
		}
	} else {
		$header .= "Name" . "\t" . "Rank" . "\t" . "Name status" . "\t" . "Language" . "\t" . "Accepted name" . "\t" . "Source database" ;
	}
	

	# This line will stream the file to the user rather than spray it across the screen
	header("Content-type: application/ms-excel");
	header("Content-Disposition: attachment; filename=CoL_data.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $header."\n" ;
	
	$data = "" ;
	if ($search_string == "") {
		while($row = mysql_fetch_row($result)){
		  $line = '';
		  foreach($row as $value){
			if(!isset($value) || $value == ""){
			  $value = "\t";
			}else{
		# strip HTML tags
			  $value = strip_tags($value);
		# important to escape any quotes to preserve them in the data.
			  $value = str_replace('"', '""', $value);
		# needed to encapsulate data in quotes because some data might be multi line.
		# the good news is that numbers remain numbers in Excel even though quoted.
			  $value = '"' . $value . '"' . "\t";
			}
			$line .= $value;
		  }
		  $data .= trim($line)."\n";
		}
	} else {
		$number_of_records = mysql_num_rows($result);
		for ($i = 1; $i <= $number_of_records; $i++) {
			$row = mysql_fetch_row($result);
			$name = $row[1] ;
			$rank = $row[2] ;
			$name_code = $row[3] ;
			$status = $row[5] ;
			$source_db = $row[7] ;
			$languages = "" ;
			$accepted_name = "" ;
			
			if ($status == "common name") {
			
				$language_query = "SELECT DISTINCT `language`
						  FROM `common_names` 
						  WHERE `language` != ''
							AND `common_name` = '" . addslashes($name) . "' 
							AND `name_code` = '$name_code' 
						  ORDER by `language`" ;
				$language_result = mysql_query($language_query) or die("Error: MySQL query failed");
				$number_of_languages = mysql_num_rows($language_result);
				if ($number_of_languages  > 0) {
					for ($j = 0; $j < $number_of_languages; $j++) {
						$row2 = mysql_fetch_row($language_result);
						if ($j > 0) {
							$languages .= ", " ;
						}
						$languages .= $row2[0] ;
					}
				}
			
				if ($name_code != "") {
					$scientific_name_query = "SELECT `genus` , `species` , `infraspecies_marker`, `infraspecies` , `author` 
						  FROM `scientific_names` 
						  WHERE `name_code` = '$name_code' " ;
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
							AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");
					$row2 = mysql_fetch_row($kingdom_result);
					$this_kingdom = $row2[0] ;
					
					$accepted_name = compileScientificName($scientific_genus,$scientific_species,$scientific_infraspecies_marker,$scientific_infraspecies,$this_kingdom,$scientific_author) ;
				}
				
			} else if ($rank == "Species" || $rank == "Infraspecies") {
				
				$species_query = "SELECT `record_id`, `author` 
					  FROM `scientific_names` 
					  WHERE `name_code` = '" . addslashes($name_code) . "'" ;
				$species_result = mysql_query($species_query) or die("Error: MySQL query failed");
				$row2 = mysql_fetch_row($species_result);
				$species_id = $row2[0] ;
				$species_author = $row2[1] ;
				
				$kingdom_query = "SELECT `kingdom`
					  FROM `families` ,`scientific_names` 
					  WHERE `scientific_names`.`name_code` = '" . addslashes($name_code) . "' 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
				$kingdom_result = mysql_query($kingdom_query) or die("Error: MySQL query failed");
				$row3 = mysql_fetch_row($kingdom_result);
				$this_kingdom = $row3[0] ;
				
				if ($species_author != "" && $this_kingdom != "Viruses" && $this_kingdom != "Subviral agents") {
					$name .= " $species_author" ;
				}
				
				if ($status == "accepted name" || $status == "provisionally accepted name") {
					$accepted_name = $name ;
				} else {
					$accepted_name_code_query = "SELECT `accepted_name_code` 
						  FROM `scientific_names` 
						  WHERE `record_id` = '$species_id' " ;
					$accepted_name_code_result = mysql_query($accepted_name_code_query) or die("Error: MySQL query failed");
					$row2 = mysql_fetch_row($accepted_name_code_result);
					$accepted_name_code = $row2[0] ;
					if ($accepted_name_code != "") {
						$accepted_name_query = "SELECT `genus` , `species` , `infraspecies_marker`, `infraspecies` , `author` 
							  FROM `scientific_names` 
							  WHERE `name_code` = '$accepted_name_code' " ;
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
					}
				}
	
			} else {
			
				$status = "" ;
				
			}
			
		 	$line = '';
			for ($j = 1; $j <= 6; $j++) {
				if ($j == 1) {
					$value = $name ;
				} else if ($j == 2) {
					$value = $rank ;
				} else if ($j == 3) {
					$value = $status ;
				} else if ($j == 4) {
					$value = $languages ;
				} else if ($j == 5) {
					$value = $accepted_name ;
				} else if ($j == 6) {
					$value = $source_db ;
				}
				if(!isset($value) || $value == ""){
					$value = "\t";
				} else {
					# strip HTML tags
					$value = strip_tags($value);
					# important to escape any quotes to preserve them in the data.
					$value = str_replace('"', '""', $value);
					# needed to encapsulate data in quotes because some data might be multi line.
					# the good news is that numbers remain numbers in Excel even though quoted.
					$value = '"' . $value . '"' . "\t";
				}
				$line .= $value;
			}
			$data .= trim($line)."\n";
		}
	}
	mysql_free_result($result) ;
	mysql_close($link) ;
	
	# this line is needed because returns embedded in the data have "\r"
	# and this looks like a "box character" in Excel
	  $data = str_replace("\r", "", $data);
	
	# Nice to let someone know that the search came up empty.
	# Otherwise only the column name headers will be output to Excel.
	if ($data == "") {
	  $data = "\nno matching records found\n";
	}
	
	echo $data;
?>
