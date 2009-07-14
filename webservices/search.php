<?php
	$start = 0 ;
	$path = "http://www.catalogueoflife.org/" ;
	$version = "1.0" ;
	
	$format = "" ;
	$name = "" ;
	$id = "" ;
	$number_of_results = 0 ;
	$total_number_of_results = 0 ;
	$error = "" ;
	
	if (isset($_GET["format"])) {
		$format = $_GET["format"] ;
		$format = strtolower(trim($format)) ;
	}
	if (isset($_GET["response"])) {
		$response = $_GET["response"] ;
		$response = strtolower(trim($response)) ;
	}
	if (isset($_GET["name"])) {
		$name = $_GET["name"] ;
		$name = addslashes(trim(urldecode($name))) ;
	}
	if (isset($_GET["id"])) {
		$id = $_GET["id"] ;
		$id = $id - 0 ;
	}
	if (isset($_GET["start"])) {
		$start = $_GET["start"] ;
		$start = $start - 0 ;
	}
	if ($format == "") {
		$format = "xml" ;
	} else if ($format != "xml" && $format != "php") {
		$error = "Unknown format: $format" ;
		$format = "xml" ;
	}
	if ($response == "") {
		$response = "terse" ;
	} else if ($response != "full" && $response != "terse") {
		$error = "Unknown response format: $response" ;
		$format = "terse" ;
	}
	
	if ($response == "terse") {
		$maximum_records_returned = 500 ;
	} else if ($response == "full") {
		$maximum_records_returned = 50 ;
	}
	$search_string = str_replace("*","%",$name) ;
	if ($error == "") {
		if ($name . $id == "") {
			$error = "No name or ID given" ;
		} else if ($name != "" && $id != "") {
			$error = "Both name and ID are given. Give either a name or an ID." ;
		} else if ($name != "" && strlen(str_replace("%","",$search_string)) < 3 ) {
			$error = "Invalid name given. The name given must consist of at least 3 characters, not counting wildcards (*)" ;
		} else if ($id != floor(abs($id)) ) {
			$error = "Invalid ID given. The ID must be a positive integer." ;
		}
	}

	if ($search_string . $id !== "" && $error == "") {
		require_once "../includes/db_connect.php";
		$query = "SELECT SQL_CALC_FOUND_ROWS 
				  `record_id` as record_id, 
				  `parent_id` as parent_id, 
				  `name` as name, 
				  `name_with_italics` as name_html, 
				  `name_code` as unique_identifier,
				  'scientific name' as name_status,
				  `taxon` as rank,
				  `is_accepted_name` as sort_order
			     FROM `taxa` " ;
		if ($id !== "") {
			if ($id === 0) {
				$query .= "WHERE `parent_id` = 0" ;
			} else {
				$query .= "WHERE `record_id` = '$id'" ;
			}
		} else {
			$query .= "WHERE `name` != 'Not assigned' AND `is_species_or_nonsynonymic_higher_taxon` = 1 " ;
			if (strpos($search_string,"%") === FALSE) {
				$query .= "AND `name` = '$search_string' " ;
			} else {
				$query .= "AND `name` LIKE '$search_string' " ;
			}
			$query .= " UNION SELECT `record_id` as record_id,
								'' as parent_id,
								`common_name` as name,
								'' as name_html,
								`name_code` as unique_identifier,
								'common name' as name_status,
								'' as rank,
								1 as sort_order
					   FROM `common_names` " ;
			if (strpos($search_string,"%") === FALSE) {
				$query .= "WHERE `common_name` = '$search_string' " ;
			} else {
				$query .= "WHERE `common_name` LIKE '$search_string' " ;
			}
		}
		$query .= " ORDER BY sort_order DESC, LOWER(name) LIMIT $start,$maximum_records_returned ; " ;
		$result = mysql_query($query) ;
		if (mysql_error() != "") {
			$error = "Database query failed" ;
		} else {
			$number_of_results = mysql_num_rows($result) ;
			$query2 = "SELECT FOUND_ROWS()" ;
			$result2 = mysql_query($query2) ;
			if (mysql_error() != "") {
				$error = "Database query failed" ;
			}
			$row2 = mysql_fetch_row($result2) ;
			$total_number_of_results = $row2[0] ;
			if ($number_of_results == 0 && $error == "") {
				$error = "No names found" ;
			}
		}
	}
	$results = array() ;
	$results["name"] = $name ;
	$results["id"] = $id ;
	$results["number_of_results_returned"] = $number_of_results ;
	$results["total_number_of_results"] = $total_number_of_results ;
	$results["start"] = $start ;
	$results["error_message"] = $error ;
	$results["version"] = $version ;
	$results["names"] = array() ;
	if ($error != "") {
		die(formatResults($results,$format)) ;
	}
	
	$results["names"] = array() ;
	for ($i = 0 ; $i < $number_of_results ; $i++) {
		$row = mysql_fetch_array($result) ;
		$record_id = $row["record_id"] ;
		$parent_id = $row["parent_id"] ;
		$name = $row["name"] ;
		$name_html = $row["name_html"] ;
		$unique_identifier = $row["unique_identifier"] ;
		$name_status = $row["name_status"] ;
		$rank = $row["rank"] ;
		
		if ($name_status === "common name") {
			$query2 = "SELECT `common_names`.`language` as language, 
						  `common_names`.`country` as country, 
						  `databases`.`database_name_displayed` as source_database,
						  `databases`.`web_site` as source_database_url
					 FROM `common_names`, `databases` 
					 WHERE `common_names`.`record_id`  = '$record_id'
					   AND `common_names`.`database_id` = `databases`.`record_id` " ;
			$result2 = mysql_query($query2) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
			}
			if ($row2 = mysql_fetch_array($result2)) {
				foreach ($row2 as $key => $value) {
					$$key = $value;
				} 
			} else {
				errorMessage("Database query failed") ;
			}
			$results["names"][$i] = array() ;
			$results["names"][$i]["name"] = $name ;
			$results["names"][$i]["name_status"] = $name_status ;
			$results["names"][$i]["url"] = $path . "show_common_name_details.php?name=" . urlencode($name) ;
			$results["names"][$i]["language"] = $language ;
			$results["names"][$i]["country"] = $country ;
			$results["names"][$i]["source_database"] = $source_database ;
			$results["names"][$i]["source_database_url"] = cleanUpLink($source_database_url) ;
			
			if ($response == "full") {
				$references = getCommonNameReferences($common_name, $language, $country, $unique_identifier) ;
				if ($references === false) {
					errorMessage("Database query failed") ;
				}
				$results["names"][$i]["references"] = $references ;
			}
		} else {
			if ($rank == "Species" || $rank === "Infraspecies") {
				$query2 = "SELECT `scientific_names`.`genus` AS genus, 
							  `scientific_names`.`species` AS species, 
							  `scientific_names`.`infraspecies_marker` AS infraspecies_marker, 
							  `scientific_names`.`infraspecies` AS infraspecies, 
							  `scientific_names`.`author` AS author, 
							  `scientific_names`.`name_code` as unique_identifier, 
							  `scientific_names`.`accepted_name_code` as accepted_name_unique_identifier, 
							  `scientific_names`.`comment` as additional_data, 
							  `scientific_names`.`web_site` AS this_link, 
							  `databases`.`database_name_displayed` as source_database,
							  `databases`.`web_site` as source_database_url,
							  `sp2000_statuses`.`sp2000_status` as name_status
						 FROM `scientific_names`, `databases`, `sp2000_statuses`
						 WHERE `scientific_names`.`name_code` = '$unique_identifier'
						   AND `scientific_names`.`name_code` LIKE BINARY '$unique_identifier'
						   AND `scientific_names`.`database_id` = `databases`.`record_id` 
						   AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` " ;
				$result2 = mysql_query($query2) ;
				if (mysql_error() != "") {
					errorMessage("Database query failed") ;
				}
				if ($row2 = mysql_fetch_array($result2)) {
					foreach ($row2 as $key => $value) {
						$$key = $value;
					} 
				} else {
					errorMessage("Database query failed") ;
				}
				if ($author != "") {
					$name_html .= " $author" ;
				}
				$distribution = getDistribution($unique_identifier) ;
				if ($distribution === false) {
					errorMessage("Database query failed") ;
				}
				
				$this_link = cleanUpLink($this_link) ;
				$source_database_url = cleanUpLink($source_database_url) ;
				
				$URL = $path . "show_species_details.php?record_id=" . $record_id ;
				
				$results["names"][$i] = array() ;
				$results["names"][$i]["id"] = $record_id ;
				$results["names"][$i]["name"] = $name ;
				$results["names"][$i]["rank"] = $rank ;
				$results["names"][$i]["name_status"] = $name_status ;
				$results["names"][$i]["name_html"] = $name_html ;
				if ($response == "full") {
					$results["names"][$i]["genus"] = $genus ;
					$results["names"][$i]["species"] = $species ;
					if ($rank !== "Species") {
						$results["names"][$i]["infraspecies_marker"] = $infraspecies_marker ;
						$results["names"][$i]["infraspecies"] = $infraspecies ;
					}
					$results["names"][$i]["author"] = $author ;
					$results["names"][$i]["additional_data"] = $additional_data ;
					$results["names"][$i]["distribution"] = $distribution ;
				}
				$results["names"][$i]["url"] = $URL ;
				$results["names"][$i]["online_resource"] = $this_link ;
				$results["names"][$i]["source_database"] = $source_database ;
				$results["names"][$i]["source_database_url"] = $source_database_url ;
				if ($response == "full") {
					$results["names"][$i]["record_scrutiny_date"] = $record_scrutiny_date ;
					$references = getReferences($unique_identifier) ;
					if ($references === false) {
						errorMessage("Database query failed") ;
					}
					$results["names"][$i]["references"] = $references ;
				}
			} else {
				// higher taxon
				$results["names"][$i] = array() ;
				$results["names"][$i]["id"] = $record_id ;
				$results["names"][$i]["name"] = $name ;
				$results["names"][$i]["name_status"] = "accepted name" ;
				$results["names"][$i]["rank"] = $rank ;
				$results["names"][$i]["name_html"] = $name_html ;
				$results["names"][$i]["url"] = $path . "browse_taxa.php?selected_taxon=$record_id" ;
				
				if ($response == "full") {
					$results["names"][$i]["classification"] = array() ;
					if ($parent_id != "" ) {
						$results["names"][$i]["classification"] = getClassification($parent_id) ;
					}
					$results["names"][$i]["child_taxa"] = getChildTaxa($record_id,$rank)  ;
				}
				continue ;
			}
		}
		
		if (strpos($results["names"][$i]["name_status"],"accepted name") !== false && $name_status !== "common name" ) {
			// accepted name 
			$is_accepted_name = true ;
		} else {
			// synonym; find accepted name
			$is_accepted_name = false ;
			
			if ($name_status === "common name") {
				$accepted_name_unique_identifier = $unique_identifier ;
			}
			$query2 = "SELECT `record_id` FROM `taxa` WHERE `name_code` = '$accepted_name_unique_identifier' AND `name_code` LIKE BINARY '$accepted_name_unique_identifier' " ;
			$result2 = mysql_query($query2) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
			}
			if ($row2 = mysql_fetch_row($result2)) {
				$record_id = $row[0] ;
			} else {
				errorMessage("Database query failed: accepted name not found") ;
			}
			
			$query2 = "SELECT    `taxa`.`record_id`, 
							 `scientific_names`.`web_site` AS this_link, 
							 `scientific_names`.`genus` AS genus, 
							 `scientific_names`.`species` AS species, 
							 `scientific_names`.`infraspecies_marker` AS infraspecies_marker, 
							 `scientific_names`.`infraspecies` AS infraspecies, 
							 `scientific_names`.`author` AS author, 
							 `scientific_names`.`comment` as additional_data, 
							 CASE WHEN `scientific_names`.`infraspecies` = '' OR `scientific_names`.`infraspecies` IS NULL THEN 'Species' ELSE 'Infraspecies' END AS rank, 
							 `scientific_names`.`scrutiny_date` AS record_scrutiny_date,  
							 `sp2000_statuses`.`sp2000_status` AS name_status,
							 `databases`.`database_name_displayed` AS source_database,
							 `databases`.`web_site` AS source_database_url,
							 `families`.`kingdom` AS kingdom
						FROM `scientific_names`, `sp2000_statuses`, `databases` , `families`, `taxa`
						WHERE `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
						  AND `scientific_names`.`database_id` = `databases`.`record_id` 
						  AND `scientific_names`.`family_id` = `families`.`record_id` 
						  AND `scientific_names`.`name_code` = '$accepted_name_unique_identifier' 
						  AND `scientific_names`.`name_code` LIKE BINARY '$accepted_name_unique_identifier'
						  AND `taxa`.`name_code` = '$accepted_name_unique_identifier' 
						  AND `taxa`.`name_code` LIKE BINARY '$accepted_name_unique_identifier' " ;
			$result2 = mysql_query($query2) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
			}
			if ($row2 = mysql_fetch_array($result2)) {
				foreach ($row2 as $key => $value) {
					$$key = $value;
				} 
				$name_html = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom, "yes") ;
				$name = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom, "no") ;
				$this_link = cleanUpLink($this_link) ;
				$source_database_url = cleanUpLink($source_database_url) ;
				$URL = $path . "show_species_details.php?record_id=" . $record_id ;
			} else {
				errorMessage("Database query failed: accepted name not found") ;
			}
			
			$distribution = getDistribution($accepted_name_unique_identifier) ;
			if ($distribution === false) {
				errorMessage("Database query failed") ;
			}
			
			$results["names"][$i]["accepted_name"] = array() ;
			$results["names"][$i]["accepted_name"]["id"] = $record_id ;
			$results["names"][$i]["accepted_name"]["name"] = $name ;
			$results["names"][$i]["accepted_name"]["rank"] = $rank ;
			$results["names"][$i]["accepted_name"]["name_status"] = $name_status ;
			$results["names"][$i]["accepted_name"]["name_html"] = $name_html ;
			if ($response == "full") {
				$results["names"][$i]["accepted_name"]["genus"] = $genus ;
				$results["names"][$i]["accepted_name"]["species"] = $species ;
				if ($rank !== "Species") {
					$results["names"][$i]["accepted_name"]["infraspecies_marker"] = $infraspecies_marker ;
					$results["names"][$i]["accepted_name"]["infraspecies"] = $infraspecies ;
				}
				$results["names"][$i]["accepted_name"]["author"] = $author ;
				$results["names"][$i]["accepted_name"]["additional_data"] = $additional_data ;
				$results["names"][$i]["accepted_name"]["distribution"] = $distribution ;
			}
			$results["names"][$i]["accepted_name"]["url"] = $URL ;
			$results["names"][$i]["accepted_name"]["online_resource"] = $this_link ;
			$results["names"][$i]["accepted_name"]["source_database"] = $source_database ;
			$results["names"][$i]["accepted_name"]["source_database_url"] = $source_database_url ;
			if ($response == "full") {
				$results["names"][$i]["accepted_name"]["record_scrutiny_date"] = $record_scrutiny_date ;
				$references = getReferences($accepted_name_unique_identifier) ;
				if ($references === false) {
					errorMessage("Database query failed") ;
				}
				$results["names"][$i]["accepted_name"]["references"] = $references ;
			}
			$unique_identifier = $accepted_name_unique_identifier ;
		} 
		
		$accepted_name_array = array() ;
		
		if ($response == "full") {
		
			// find classification
			
			$accepted_name_array["classification"] = array() ;
			$query2 = "SELECT `parent_id` 
					 FROM `taxa` 
					 WHERE `name_code` = '$unique_identifier'
					   AND `name_code` LIKE BINARY '$unique_identifier'" ;
			$result2 = mysql_query($query2) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
			}
			$number_of_rows = mysql_num_rows($result2);
			if ($number_of_rows > 0) {
				$row2 = mysql_fetch_row($result2);
				$parent_id = $row2[0] ;
				$accepted_name_array["classification"] = getClassification($parent_id) ;
			}
			
			// find children
			$accepted_name_array["child_taxa"] = getChildTaxa($record_id,$rank)  ;
			
			// find synonyms
			$accepted_name_array["synonyms"] = getSynonyms($unique_identifier) ;
			
			// find common names
			$accepted_name_array["common_names"] = getCommonNames($unique_identifier) ;
		}
		
		foreach ($accepted_name_array as $this_property => $this_value) {
			if ($is_accepted_name === true) {
				$results["names"][$i][$this_property] = $this_value ;
			} else {
				$results["names"][$i]["accepted_name"][$this_property] = $this_value ;
			}
		}
				
	}
	
			
	mysql_close($link) ;
	echo formatResults($results,$format) ;
	
	function errorMessage($error)  {
		global $results,$format ;
		$results["error_message"] = $error ;
		if (array_key_exists("names", $results)) {
			unset($results["names"]) ;
		}
		echo formatResults($results,$format) ;
		die() ;
	}
	
	function getReferences($unique_identifier) {
		global $link ;
		$references = array() ;
		
		$query = "SELECT DISTINCT `references`.`author` , 
					 		  `references`.`year` , 
					 		  `references`.`title` , 
					 		  `references`.`source`  
			  FROM `references` , `scientific_name_references` 
			  WHERE `scientific_name_references`.`reference_id` = `references`.`record_id` 
				AND (`scientific_name_references`.`reference_type` = 'NomRef' 
				  OR `scientific_name_references`.`reference_type` = 'TaxAccRef'
				  OR `scientific_name_references`.`reference_type` = ''
				  OR `scientific_name_references`.`reference_type` IS NULL) 
				AND `scientific_name_references`.`name_code` = '$unique_identifier'
				AND `scientific_name_references`.`name_code` LIKE BINARY '$unique_identifier'
			  ORDER BY  `references`.`author`, 
			  			`references`.`year`, 
						`references`.`title`, 
						`references`.`source`" ;
		$result = mysql_query($query) ;
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		$number_of_results = mysql_num_rows($result) ;
		if ($number_of_results > 0) {
			for ($i = 0 ; $i < $number_of_results ; $i++) {
				$row = mysql_fetch_array($result) ;
				foreach ($row as $key => $value) {
					$$key = $value ;
				} 
				$references[$i] = array() ;
				$references[$i]["author"] = $author ;
				$references[$i]["year"] = $year ;
				$references[$i]["title"] = $title ;
				$references[$i]["source"] = $source ;
			}
		}
		return $references ;
	}
	
	function getDistribution($name_code) {
		global $link ;
		$distribution = "" ;
		$query = "SELECT `distribution` 
				FROM `distribution` 
				WHERE `name_code` = '$name_code'
				  AND `name_code` LIKE BINARY '$name_code' " ;
		$result = mysql_query($query);
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		while ($row = mysql_fetch_row($result)) {
			$distribution .= (($distribution == "") ? "" : "; ") . trim($row[0]) ;
		}
		return $distribution ;
	}
	
	function getClassification($parent_id) {
		global $link, $path ;
		$parents = array() ;
		$number_of_parents = 0 ;
		$parent_url = $path . "browse_taxa.php?selected_taxon=$parent_id" ;
		$found_parent = TRUE ;
		while($found_parent != FALSE) {
			$query = "SELECT `parent_id`,`taxon`,`name`,`name_with_italics`,`name_code` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
			$result = mysql_query($query) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
				return false ;
			}
			if ( mysql_num_rows($result) == 0) {
				$found_parent = FALSE ;
				continue ;
			} 
			$row = mysql_fetch_row($result);
			mysql_free_result($result) ;
			$record_id = $parent_id ;
			$parent_id = $row[0] ;
			$parent_rank = $row[1] ;
			$parent_name = $row[2] ;
			$parent_name_html = $row[3] ;
			$parent_unique_identifier = $row[4] ;
			if ($parent_unique_identifier != "") {
				$query2 = "SELECT    `scientific_names`.`record_id` AS species_id, 
								 `scientific_names`.`genus` AS genus, 
								 `scientific_names`.`species` AS species, 
								 `scientific_names`.`infraspecies_marker` AS infraspecies_marker, 
								 `scientific_names`.`infraspecies` AS infraspecies, 
								 `scientific_names`.`author` AS author, 
								 `families`.`kingdom` AS kingdom
							FROM `scientific_names`, `sp2000_statuses`, `databases` , `families`
							WHERE `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
							  AND `scientific_names`.`database_id` = `databases`.`record_id` 
							  AND `scientific_names`.`family_id` = `families`.`record_id` 
							  AND `scientific_names`.`name_code` = '$parent_unique_identifier' 
							  AND `scientific_names`.`name_code` LIKE BINARY '$parent_unique_identifier' " ;
				$result2 = mysql_query($query2) ;
				if (mysql_error() != "") {
					errorMessage("Database query failed") ;
					return false ;
				}
				if ($row2 = mysql_fetch_array($result2)) {
					foreach ($row2 as $key => $value) {
						$$key = $value;
					} 
					$parent_name_html = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom, "yes") ; ;
					$parent_url = $path . "species_details.php?record_id=" . $species_id ;
				}
			}
			if ($parent_name != "Not assigned") {
				$parents[$number_of_parents] = array() ;
				$parents[$number_of_parents]["id"] = $record_id ;
				$parents[$number_of_parents]["name"] = $parent_name ;
				$parents[$number_of_parents]["rank"] = $parent_rank ;
				$parents[$number_of_parents]["name_html"] = $parent_name_html ;
				$parents[$number_of_parents]["url"] = $parent_url ;
				$number_of_parents ++ ;
			}
			$parent_url = $path . "browse_taxa.php?selected_taxon=$parent_id" ;
			if ($parent_id == "" || $parent_id == 0) {
				$found_parent = FALSE ;
				continue ;
			}
		}
		return array_reverse($parents) ;
	}
	
	function getChildTaxa($record_id,$rank) {
		global $link, $path ;
		$child_taxa_results = array() ;
		if ($rank == "Infraspecies") {
			return $child_taxa_results ;
		} else if ($rank == "Species") {
			$query = "SELECT `name` FROM `taxa` WHERE `record_id` = $record_id " ;
			$result = mysql_query($query) ;
			if (mysql_error() != "") {
				errorMessage("Database query failed") ;
				return false ;
			}	
			if ($row = mysql_fetch_row($result)) {
				$parent_name = addslashes($row[0]) ;
			} else {
				errorMessage("Database query failed") ;
				return false ;
			}
			$query = "SELECT `record_id` , 
						   `name`,
						   `name_with_italics`,
						   `name_code`,
						  `taxon`
					 FROM `taxa`
					 WHERE `name` LIKE '$parent_name %'
					   AND `taxon` = 'Infraspecies' 
					   AND `is_accepted_name` = 1 " ;
		} else {
			$query = "SELECT `record_id` , 
						   `name`,
						   `name_with_italics`,
						   `name_code`,
						  `taxon`
					 FROM `taxa`
					 WHERE `parent_id` = $record_id
					   AND `is_accepted_name` = 1 " ;
		}
		$result = mysql_query($query) ;
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		while ($row = mysql_fetch_row($result)) {
			$this_id = $row[0] ;
			$this_name = $row[1] ;
			$this_name_html = $row[2] ;
			$this_name_code = addslashes($row[3]) ;
			$this_rank = $row[4] ;
			if ($this_rank == "Species" || $this_rank == "Infraspecies") {
				$query2 = "SELECT `scientific_names`.`record_id` , 
							   `scientific_names`.`genus` , 
							   `scientific_names`.`species` , 
							   `scientific_names`.`infraspecies_marker` , 
							   `scientific_names`.`infraspecies` , 
							   `scientific_names`.`author`,
							   `families`.`kingdom` AS kingdom
						  FROM `scientific_names` , `families`
						  WHERE `scientific_names`.`name_code` = '$this_name_code'
						    AND `scientific_names`.`name_code` LIKE BINARY '$this_name_code'
						    AND `scientific_names`.`family_id` = `families`.`record_id`  " ; 
				$result2 = mysql_query($query2) ;
				if ($row2 = mysql_fetch_row($result2)) {
					$this_species_id = $row2[0] ;
					$this_genus = $row2[1] ;
					$this_species = $row2[2] ;
					$this_infraspecies_marker = $row2[3] ;
					$this_infraspecies = $row2[4] ;
					$this_author = $row2[5] ;
					$this_kingdom = $row2[6] ;
					$these_results = array() ;
					$these_results["id"] = $this_id ;
					$these_results["name"] = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_author,$this_kingdom, "no") ;
					$these_results["name_html"] = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_author,$this_kingdom, "yes") ;
					$these_results["rank"] = $this_rank ;
					$these_results["genus"] = $this_genus ;
					$these_results["species"] = $this_species ;
					if ($this_rank == "Infraspecies") {
						$these_results["infraspecies_marker"] = $this_infraspecies_marker ;
						$these_results["infraspecies"] = $this_infraspecies ;
					}
					$these_results["author"] = $this_author ;
					$these_results["url"] = $path . "show_species_details.php?record_id=" . $this_species_id ;
					array_push($child_taxa_results, $these_results) ;
				}
			} else {
				$these_results = array() ;
				$these_results["id"] = $this_id ;
				$these_results["name"] = $this_name ;
				$these_results["name_html"] = $this_name_html ;
				$these_results["rank"] = $this_rank ;
				$these_results["url"] = $path . "browse_taxa.php?selected_taxon=$this_id" ;
				array_push($child_taxa_results, $these_results) ;
			}
		}
		return $child_taxa_results ;
	}
	
	function getSynonyms($unique_identifier) {
		global $link ;
		$query2 = "SELECT `taxa`.`record_id` AS synonym_id, 
						 `scientific_names`.`web_site` AS this_link, 
						 `scientific_names`.`genus` AS genus, 
						 `scientific_names`.`species` AS species, 
						 `scientific_names`.`infraspecies_marker` AS infraspecies_marker, 
						 `scientific_names`.`infraspecies` AS infraspecies, 
						 `scientific_names`.`author` AS author, 
						 `scientific_names`.`name_code` AS synonym_unique_identifier,
						 CASE WHEN `scientific_names`.`infraspecies` = '' OR `scientific_names`.`infraspecies` IS NULL THEN 'Species' ELSE 'Infraspecies' END AS rank, 
						 `scientific_names`.`scrutiny_date` AS record_scrutiny_date,  
						 `scientific_names`.`comment` AS additional_data,  
						 `sp2000_statuses`.`sp2000_status` AS name_status,
						 `databases`.`database_name_displayed` AS source_database,
						 `databases`.`web_site` AS source_database_url,
						 `families`.`kingdom` AS kingdom
					FROM `scientific_names`, `sp2000_statuses`, `databases` , `families`, `taxa`
					WHERE `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
					  AND `scientific_names`.`database_id` = `databases`.`record_id` 
					  AND `scientific_names`.`family_id` = `families`.`record_id` 
					  AND `scientific_names`.`accepted_name_code` = '$unique_identifier' 
					  AND `scientific_names`.`accepted_name_code` LIKE BINARY '$unique_identifier' 
					  AND `scientific_names`.`name_code` != '$unique_identifier' 
					  AND `taxa`.`name_code` = `scientific_names`.`name_code`
					  AND `taxa`.`name_code` LIKE BINARY `scientific_names`.`name_code`
					ORDER BY `scientific_names`.`genus`,  `scientific_names`.`species`, `scientific_names`.`infraspecies`, `scientific_names`.`author`" ;
		$result2 = mysql_query($query2) ;
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		$synonyms = array() ;
		$number_of_synonyms = mysql_num_rows($result2)  ;
		if ($number_of_synonyms > 0) {
			for ($j = 0 ; $j < $number_of_synonyms ; $j++) {
				$synonyms[$j] = array() ;
				$row2 = mysql_fetch_array($result2) ;
				foreach ($row2 as $key => $value) {
					$$key = $value;
				} 
				$name_html = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom, "yes") ;
				$name = compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$author,$kingdom, "no") ;
				$this_link = cleanUpLink($this_link) ;
				$source_database_url = cleanUpLink($source_database_url) ;
				$URL = $path . "show_species_details.php?record_id=" . $record_id ;
				
				$synonyms[$j]["id"] = $synonym_id ;
				$synonyms[$j]["name"] = $name ;
				$synonyms[$j]["rank"] = $rank ;
				$synonyms[$j]["name_status"] = $name_status ;
				$synonyms[$j]["name_html"] = $name_html ;
				$synonyms[$j]["genus"] = $genus ;
				$synonyms[$j]["species"] = $species ;
				if ($rank !== "Species") {
					$synonyms[$j]["infraspecies_marker"] = $infraspecies_marker ;
					$synonyms[$j]["infraspecies"] = $infraspecies ;
				}
				$synonyms[$j]["author"] = $author ;
				$synonyms[$j]["additional_data"] = $additional_data ;
				$synonyms[$j]["url"] = $URL ;
				$synonyms[$j]["online_resource"] = $this_link ;
				$synonyms[$j]["source_database"] = $source_database ;
				$synonyms[$j]["source_database_url"] = $source_database_url ;
				$synonyms[$j]["record_scrutiny_date"] = $record_scrutiny_date ;
				$references = getReferences($synonym_unique_identifier) ;
				if ($references === false) {
					errorMessage("Database query failed") ;
					return false ;
				}
				$synonyms[$j]["references"] = $references ;
			}
		}
		return $synonyms ;
	}
	
	function getCommonNames($unique_identifier) {
		global $link ;
		$common_names = array() ;
		$query2 = "SELECT DISTINCT `common_name` , `language` , `country` 
				  FROM `common_names` 
				  WHERE `name_code` = '$unique_identifier' 
				    AND `name_code` LIKE BINARY '$unique_identifier' 
				  ORDER BY `common_name`, `language`, `country`" ;
		$result2 = mysql_query($query2) ;
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		$number_of_common_names = mysql_num_rows($result2) ;
		if ($number_of_common_names > 0) {
			for ($j = 0 ; $j < $number_of_common_names ; $j++) {
				$common_names[$j] = array() ;
				$row2 = mysql_fetch_array($result2) ;
				foreach ($row2 as $key => $value) {
					$$key = $value;
				}
				$common_name = $row2[0] ;
				$language = $row2[1] ;
				$common_names[$j]["name"] = $common_name ;
				$common_names[$j]["language"] = $language ;
				$common_names[$j]["country"] = $country ;
				$references = getCommonNameReferences($common_name, $language, $country, $unique_identifier) ;
				if ($references === false) {
					errorMessage("Database query failed") ;
					return false ;
				}
				$common_names[$j]["references"] = $references ;
			}
		}
		return $common_names ;
	}
	function getCommonNameReferences($name, $language, $country, $name_code) {
		global $link ;
		$references = array() ;
		$query = "SELECT DISTINCT `references`.`author` , 
					 		  `references`.`year` , 
					 		  `references`.`title` , 
					 		  `references`.`source`  
			  FROM `common_names`,`references` 
			  WHERE `common_names`.`common_name` = '" . addslashes($name) . "' 
				AND `common_names`.`language` = '" . addslashes($language) . "' 
				AND `common_names`.`country` = '" . addslashes($country) . "' 
				AND `common_names`.`name_code` = '" . addslashes($name_code) . "' 
				AND `common_names`.`name_code` LIKE BINARY '" . addslashes($name_code) . "' 
			     AND `common_names`.`reference_id` = `references`.`record_id` 
			  ORDER BY  `references`.`author`, 
			  			`references`.`year`, 
						`references`.`title`,
						`references`.`source` " ;
		$result = mysql_query($query) ;
		if (mysql_error() != "") {
			errorMessage("Database query failed") ;
			return false ;
		}
		$number_of_results = mysql_num_rows($result) ;
		if ($number_of_results > 0) {
			for ($i = 0 ; $i < $number_of_results ; $i++) {
				$row = mysql_fetch_array($result) ;
				foreach ($row as $key => $value) {
					$$key = $value ;
				} 
				$references[$i] = array() ;
				$references[$i]["author"] = $author ;
				$references[$i]["year"] = $year ;
				$references[$i]["title"] = $title ;
				$references[$i]["source"] = $source ;
			}
		}
		return $references ;
	}
	
	function compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,  
	  $this_author,$this_kingdom,$displayed) {
		if ($this_kingdom == "Viruses" || $this_kingdom == "Subviral agents") {
			$scientific_name = $this_species ;
			if ($this_infraspecies != "") {
				$scientific_name .= (($this_infraspecies_marker != "") ? " $this_infraspecies_marker" : "") . 
				  $this_infraspecies ;
			}
		} else {
			$scientific_name = (($displayed == "yes") ? "<i>$this_genus $this_species</i>" : "$this_genus $this_species" );
			if ($this_infraspecies != "") {
				if ($this_infraspecies_marker != "") {
					$scientific_name .= " $this_infraspecies_marker" ;
				}
				$scientific_name .= (($displayed == "yes") ? " <i>$this_infraspecies</i>" : " $this_infraspecies" );
			}
			if ($this_author != "" && $displayed == "yes") {
				$scientific_name .= " $this_author" ;
			}
		}
		return $scientific_name ;
	}
	
	function cleanUpLink($this_link) {
		if ( substr($this_link,0,1) == "#" ) {
			$this_link = substr($this_link,1,strlen($this_link)-1) ;
		}
		if (strpos($this_link,"#") !== FALSE) {
			$this_link = substr($this_link,0,strpos($this_link,"#")) ;
		}
		if ( substr($this_link,0,4) != "http" || strlen($this_link) < 8 ) {
			$this_link = "" ;
		}
		//$this_link = str_replace("&amp;","&",$this_link) ;
		//$this_link = str_replace("&","&amp;",$this_link) ;
		return $this_link ;
	}
	
	function formatResults($results,$format) {
//print_r($results) ;
		if ($format == "php") {
			return serialize($results) ;
		} else if ($format == "xml") {
			header('Content-Type: text/xml; charset=ISO-8859-1');
			$output = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n" ;
			$id = $results["id"] ;
			$name = $results["name"] ;
			$total_number_of_results = $results["total_number_of_results"] ;
			$start = $results["start"] ;
			$number_of_results_returned = $results["number_of_results_returned"] ;
			$error_message = $results["error_message"] ;
			$version = $results["version"] ;
			$output .= "<" . "results id=\"$id\" " . 
								"name=\"$name\" " . 
								"total_number_of_results=\"$total_number_of_results\" " . 
								"start=\"$start\" " . 
								"number_of_results_returned=\"$number_of_results_returned\" " . 
								"error_message=\"$error_message\" " . 
								"version=\"$version\">\n" ;
			foreach ($results["names"] as $name_record) {
				$output .= "<result>\n" ;
				$properties = array("id","name","rank","name_status","name_html","genus","species","infraspecies_marker","infraspecies",
				  "author","additional_data","distribution","language","country","url","source_database","source_database_url", 
				  "record_scrutiny_date", "online_resource") ;
				$output .= writeXMLPropertyValues($name_record,$properties,false) ;
				
				if (array_key_exists("references", $name_record)) {
					$output .= "<references>\n" ;
					$properties = array("author","year","title","source") ;
					foreach ($name_record["references"] as $this_item) {
						$output .= "<refererence>\n" ;
						$output .= writeXMLPropertyValues($this_item,$properties,false) ;
						$output .= "</refererence>\n" ;
					}
					$output .= "</references>\n" ;
				}
				if (array_key_exists("accepted_name", $name_record)) {
					$output .= "<accepted_name>\n" ;
					$properties = array("id","name","rank","name_status","name_html","genus","species","infraspecies_marker", 
					  "infraspecies",  "author","additional_data","distribution","url","source_database","source_database_url",
					  "record_scrutiny_date","online_resource") ;
					$output .= writeXMLPropertyValues($name_record["accepted_name"],$properties,false) ;
					if (array_key_exists("references", $name_record["accepted_name"])) {
						$output .= "<references>\n" ;
						$properties = array("author","year","title","source") ;
						foreach($name_record["accepted_name"]["references"] as $this_item) {
							$output .= "<reference>\n" ;
							$output .= writeXMLPropertyValues($this_item,$properties,false) ;
							$output .= "</reference>\n" ;
						}
						$output .= "</references>\n" ;
					}
					$name_record = $name_record["accepted_name"] ;
					$is_accepted_name = false ;
				} else {
					$is_accepted_name = true ;
				}
				
				if (array_key_exists("classification", $name_record)) {
					$output .= "<classification>\n" ;
					$properties = array("id","name","rank","name_html","url") ;
					foreach ($name_record["classification"] as $this_item) {
						$output .= "<taxon>\n" ;
						$output .= writeXMLPropertyValues($this_item,$properties,false) ;
						$output .= "</taxon>\n" ;
						
					}
					$output .= "</classification>\n" ;
				}
				if (array_key_exists("child_taxa", $name_record)) {
					$output .= "<child_taxa>\n" ;
					$properties = array("id","name","rank","name_html","genus","species","infraspecies_marker","infraspecies","url") ;
					foreach ($name_record["child_taxa"] as $this_item) {
						$output .= "<taxon>\n" ;
						$output .= writeXMLPropertyValues($this_item,$properties,false) ;
						$output .= "</taxon>\n" ;
					}
					$output .= "</child_taxa>\n" ;
				}
				if (array_key_exists("synonyms", $name_record)) {
					$output .= "<synonyms>\n" ;
					foreach ($name_record["synonyms"] as $this_synonym) {
						$output .= "<synonym>\n" ;
						$properties = array("id","name","rank","name_status","name_html","genus","species","infraspecies_marker", 
						  "infraspecies","author","additional_data","url","source_database","source_database_url", 
						  "record_scrutiny_date","online_resource") ;
						$output .= writeXMLPropertyValues($this_synonym,$properties,false) ;
						if (array_key_exists("references", $this_synonym)) {
							$output .= "<references>\n" ;
							foreach($this_synonym["references"] as $this_reference) {
								$output .= "<reference>\n" ;
								$properties = array("author","year","title","source") ;
								$output .= writeXMLPropertyValues($this_reference,$properties,false) ;
								$output .= "</reference>\n" ;
							}
							$output .= "</references>\n" ;
						}
						$output .= "</synonym>\n" ;
					}
					$output .= "</synonyms>\n" ;
				}
				if (array_key_exists("common_names", $name_record)) {
					$output .= "<common_names>\n" ;
					foreach ($name_record["common_names"] as $common_name) {
						$output .= "<common_name>\n" ;
						$properties = array("name","year","language","country") ;
						$output .= writeXMLPropertyValues($common_name,$properties,true) ;
						if (array_key_exists("references", $common_name)) {
							$output .= "<references>\n" ;
							foreach($common_name["references"] as $this_reference) {
								$output .= "<reference>\n" ;
								$properties = array("author","year","title","source") ;
								$output .= writeXMLPropertyValues($this_reference,$properties,false) ;
								$output .= "</reference>\n" ;
							}
							$output .= "</references>\n" ;
						}
						$output .= "</common_name>\n" ;
					}
					$output .= "</common_names>\n" ;
				}
				if ($is_accepted_name === false) {
					$output .= "</accepted_name>\n" ;
				}
				$output .= "</result>\n" ;
			}
			$output .= "</results>" ;
		}
		return $output ;
	}
	
	function writeXMLPropertyValues($this_item,$properties,$is_common_name) {
		$output = "" ;
		foreach ($properties as $property) {
			if (array_key_exists($property, $this_item)) {
				$value = $this_item[$property]  ;
				
				if ($is_common_name === false) {
					$value = str_replace("&amp;","&",$value) ;
					$value = str_replace("&","&amp;",$value) ;
				}
				
				$output .= "<" . strtolower($property) . ">$value</" . strtolower($property) . ">\n" ;
			}
		}
		return $output ;
	}
?>