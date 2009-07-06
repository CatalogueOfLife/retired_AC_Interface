
<?php
	flush();

	$selected_letter = "" ;
	if (isset($_REQUEST["selected_letter"])) {
		$selected_letter = $_REQUEST["selected_letter"] ;
	}
	
	function addToQuery($rank,$name) {
		if ($name == "") {
			return "" ;
		} else if (strpos($name, "*") !== FALSE) {
			return " AND `families`.`$rank` LIKE '" . str_replace(array("*","?"),"%",$name) . "' " ;
		} else {
			return " AND `families`.`$rank` = '$name' " ;
		}
	}
	
	$number_of_names = 0 ;
	$list_of_names = array() ;
	$full_list = "" ;
	
	if (isset($show_taxon)) {
		if ($show_taxon != "") {
			
			include "connect_to_database.php" ;
			
			$results = "" ;
			if ($show_taxon == "kingdom") {
				$query = "SELECT DISTINCT `kingdom` FROM `families` WHERE `kingdom` != '' AND `kingdom` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= " ORDER BY `kingdom`" ;
			} elseif ($show_taxon == "phylum") {
				$query = "SELECT DISTINCT `phylum` FROM `families` WHERE `phylum` != '' AND `phylum` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= addToQuery("kingdom",$kingdom) ;
				$query .= " ORDER BY `phylum`" ;
			} elseif ($show_taxon == "class") {
				$query = "SELECT DISTINCT `class` FROM `families` WHERE `class` != '' AND `class` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= addToQuery("kingdom",$kingdom) ;
				$query .= addToQuery("phylum",$phylum) ;
				$query .= " ORDER BY `class`" ;
			} elseif ($show_taxon == "order") {
				$query = "SELECT DISTINCT `order` FROM `families` WHERE `order` != '' AND `order` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= addToQuery("kingdom",$kingdom) ;
				$query .= addToQuery("phylum",$phylum) ;
				$query .= addToQuery("class",$tax_class) ;
				$query .= " ORDER BY `order`" ;
			} elseif ($show_taxon == "superfamily") {
				$query = "SELECT DISTINCT `superfamily` FROM `families` WHERE `superfamily` != '' AND `superfamily` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= addToQuery("kingdom",$kingdom) ;
				$query .= addToQuery("phylum",$phylum) ;
				$query .= addToQuery("class",$tax_class) ;
				$query .= addToQuery("order",$order) ;
				$query .= " ORDER BY `superfamily`" ;
			} elseif ($show_taxon == "family") {
				$query = "SELECT DISTINCT `family` FROM `families` WHERE `family` != '' AND `family` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= addToQuery("kingdom",$kingdom) ;
				$query .= addToQuery("phylum",$phylum) ;
				$query .= addToQuery("class",$tax_class) ;
				$query .= addToQuery("order",$order) ;
				$query .= addToQuery("superfamily",$superfamily) ;
				$query .= " ORDER BY `family`" ;
			} elseif ($show_taxon == "genus") {
				if ($kingdom . $phylum . $tax_class . $order . $superfamily . $family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`genus` 
							  FROM `scientific_names`,`families`
							  WHERE `scientific_names`.`genus` != '' 
							    AND `scientific_names`.`genus` != 'Not assigned'
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_type == "browse_by_classification") {
						$query .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
					}
					$query .= addToQuery("family",$family) ;
					$query .= addToQuery("superfamily",$superfamily) ;
					$query .= addToQuery("order",$order) ;
					$query .= addToQuery("class",$tax_class) ;
					$query .= addToQuery("phylum",$phylum) ;
					$query .= addToQuery("kingdom",$kingdom) ;
					$query .= " ORDER BY `scientific_names`.`genus`" ;
				} else {
				
					if ($selected_letter == "") {
						$selected_letter = "A" ;
						if ($genus != "") {
							$first_char = strtoupper(substr($genus,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
					
					$query = "SELECT `name` 
							  FROM `hard_coded_taxon_lists` 
							  WHERE `rank` = 'genus' " ;
					if ($search_type == "browse_by_classification") {
						$query .= " AND `accepted_names_only` = 1 " ;
					} else {
						$query .= " AND `accepted_names_only` = 0 " ;
					}
					if ($selected_letter != "" && $selected_letter != "#") {
						$query .= " AND `name` LIKE '{$selected_letter}%' " ;
					}
				}
			} elseif ($show_taxon == "species") {
				if ($kingdom . $phylum . $tax_class . $order . $family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`species` 
							  FROM `scientific_names`,`families` 
							  WHERE `scientific_names`.`species` != '' 
							    AND `scientific_names`.`species` != 'Not assigned'
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_type == "browse_by_classification") {
						$query .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
					}
					$query .= addToQuery("family",$family) ;
					$query .= addToQuery("superfamily",$superfamily) ;
					$query .= addToQuery("order",$order) ;
					$query .= addToQuery("class",$tax_class) ;
					$query .= addToQuery("phylum",$phylum) ;
					$query .= addToQuery("kingdom",$kingdom) ;
					if ($genus != "") {
						$query .= " AND `scientific_names`.`genus` LIKE '$genus'" ;
					}
					$query .= " ORDER BY `scientific_names`.`species`" ;
				} else {
					
					if ($selected_letter == "" && $genus == "") {
						$selected_letter = "A" ;
						if ($species != "") {
							$first_char = strtoupper(substr($species,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
					
					if ($genus == "") {
						$query = "SELECT `name` 
								  FROM `hard_coded_taxon_lists` 
								  WHERE `rank` = 'species' " ;
						if ($search_type == "browse_by_classification") {
							$query .= " AND `accepted_names_only` = 1 " ;
						} else {
							$query .= " AND `accepted_names_only` = 0 " ;
						}
						if ($selected_letter != "" && $selected_letter != "#") {
							$query .= " AND `name` LIKE '{$selected_letter}%' " ;
						}
					} else {
						$query = "SELECT DISTINCT `species` 
								  FROM `scientific_names`
								  WHERE `species` != '' 
									AND `species` != 'Not assigned' " ;
						if ($search_type == "browse_by_classification") {
							$query .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
						}
						if ($genus != "") {
							$query .= " AND `genus` LIKE '$genus'" ;
						}
						if ($selected_letter != "" && $selected_letter != "#") {
							$query .= " AND `species` LIKE '{$selected_letter}%' " ;
						}
						$query .= " ORDER BY `species`" ;
					}
				}
			} elseif ($show_taxon == "infraspecies") {
				if ($kingdom . $phylum . $tax_class . $order . $family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`infraspecies` 
							  FROM `scientific_names`,`families` 
							  WHERE `scientific_names`.`infraspecies` != '' AND `scientific_names`.`infraspecies` IS NOT NULL 
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_type == "browse_by_classification") {
						$query .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
					}
					$query .= addToQuery("family",$family) ;
					$query .= addToQuery("superfamily",$superfamily) ;
					$query .= addToQuery("order",$order) ;
					$query .= addToQuery("class",$tax_class) ;
					$query .= addToQuery("phylum",$phylum) ;
					$query .= addToQuery("kingdom",$kingdom) ;
					if ($genus != "") {
						$query .= " AND `scientific_names`.`genus` LIKE '$genus'" ;
					}
					if ($species != "") {
						$query .= " AND `scientific_names`.`species` LIKE '$species'" ;
					}
					$query .= " ORDER BY `scientific_names`.`infraspecies`" ;
				} else {
				
					if ($selected_letter == "" && $genus . $species == "") {
						$selected_letter = "A" ;
						if ($infraspecies != "") {
							$first_char = strtoupper(substr($infraspecies,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
					
					if ($genus . $species == "") {
						$query = "SELECT `name` 
								  FROM `hard_coded_taxon_lists` 
								  WHERE `rank` = 'infraspecies' " ;
						if ($search_type == "browse_by_classification") {
							$query .= " AND `accepted_names_only` = 1 " ;
						} else {
							$query .= " AND `accepted_names_only` = 0 " ;
						}
						if ($selected_letter != "" && $selected_letter != "#") {
							$query .= " AND `name` LIKE '{$selected_letter}%' " ;
						}
					} else {
						$query = "SELECT DISTINCT `infraspecies` 
								  FROM `scientific_names` 
								  WHERE `infraspecies` != '' AND `infraspecies` IS NOT NULL " ;
						if ($search_type == "browse_by_classification") {
							$query .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
						}
						if ($genus != "") {
							$query .= " AND `genus` LIKE '$genus'" ;
						}
						if ($species != "") {
							$query .= " AND `species` LIKE '$species'" ;
						}
						if ($selected_letter != "" && $selected_letter != "#") {
							$query .= " AND `infraspecies` LIKE '{$selected_letter}%' " ;
						}
						$query .= " ORDER BY `infraspecies`" ;
					}
				}
			}
			$result = mysql_query($query) or die("Error: MySQL query failed");
			$number_of_names = mysql_num_rows($result) ;
			for ($i = 0 ; $i < $number_of_names ; $i++) {
				$row = mysql_fetch_row($result) ;
				$found_name = $row[0] ;
				array_push($list_of_names, addslashes($found_name)) ;
				$full_list .= $i . "/" . rawurlencode ($found_name) . "/" ;
			}
			mysql_free_result($result) ;
			mysql_close($link) ;
		}
	}
?>

