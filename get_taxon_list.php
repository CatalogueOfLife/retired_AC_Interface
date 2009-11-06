
<?php
	$selected_letter = "" ;
	if (isset($_REQUEST["selected_letter"])) {
		$selected_letter = $_REQUEST["selected_letter"] ;
	}
	
	if (isset($show_taxon)) {
		if ($show_taxon != "") {
			$results = "" ;
			if ($show_taxon == "kingdom") {
				$query = "SELECT DISTINCT `kingdom` FROM `families` WHERE `kingdom` != '' AND `kingdom` != 'Not assigned' AND `is_accepted_name` = 1" ;
				$query .= " ORDER BY `kingdom`" ;
			} elseif ($show_taxon == "phylum") {
				$query = "SELECT DISTINCT `phylum` FROM `families` WHERE `phylum` != '' AND `phylum` != 'Not assigned' AND `is_accepted_name` = 1" ;
				if ($search_kingdom != "") {
					$query .= " AND `kingdom` = '$search_kingdom'" ;
				}
				$query .= " ORDER BY `phylum`" ;
			} elseif ($show_taxon == "class") {
				$query = "SELECT DISTINCT `class` FROM `families` WHERE `class` != '' AND `class` != 'Not assigned' AND `is_accepted_name` = 1" ;
				if ($search_kingdom != "") {
					$query .= " AND `kingdom` = '$search_kingdom'" ;
				}
				if ($search_phylum != "") {
					$query .= " AND `phylum` = '$search_phylum'" ;
				}
				$query .= " ORDER BY `class`" ;
			} elseif ($show_taxon == "order") {
				$query = "SELECT DISTINCT `order` FROM `families` WHERE `order` != '' AND `order` != 'Not assigned' AND `is_accepted_name` = 1" ;
				if ($search_kingdom != "") {
					$query .= " AND `kingdom` = '$search_kingdom'" ;
				}
				if ($search_phylum != "") {
					$query .= " AND `phylum` = '$search_phylum'" ;
				}
				if ($search_class != "") {
					$query .= " AND `class` = '$search_class'" ;
				}
				$query .= " ORDER BY `order`" ;
			} elseif ($show_taxon == "family") {
				$query = "SELECT DISTINCT `family` FROM `families` WHERE `family` != '' AND `family` != 'Not assigned' AND `is_accepted_name` = 1" ;
				if ($search_kingdom != "") {
					$query .= " AND `kingdom` = '$search_kingdom'" ;
				}
				if ($search_phylum != "") {
					$query .= " AND `phylum` = '$search_phylum'" ;
				}
				if ($search_class != "") {
					$query .= " AND `class` = '$search_class'" ;
				}
				if ($search_order != "") {
					$query .= " AND `order` = '$search_order'" ;
				}
				if ($search_order != "") {
					$query .= " AND `order` = '$search_order'" ;
				}
				$query .= " ORDER BY `family`" ;
			} elseif ($show_taxon == "genus") {
				if ($search_kingdom . $search_phylum . $search_class . $search_order . $search_family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`genus` 
							  FROM `scientific_names`,`families`
							  WHERE `scientific_names`.`genus` != '' 
							    AND `scientific_names`.`genus` != 'Not assigned'
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_page == "browse_by_classification.php") {
						$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
					}
					if ($search_kingdom != "") {
						$query .= " AND `families`.`kingdom` = '$search_kingdom'" ;
					}
					if ($search_phylum != "") {
						$query .= " AND `families`.`phylum` = '$search_phylum'" ;
					}
					if ($search_class != "") {
						$query .= " AND `families`.`class` = '$search_class'" ;
					}
					if ($search_order != "") {
						$query .= " AND `families`.`order` = '$search_order'" ;
					}
					if ($search_family != "") {
						$query .= " AND `families`.`family` = '$search_family'" ;
					}
					$query .= " ORDER BY `scientific_names`.`genus`" ;
				} else {
					/*
					$query = "SELECT DISTINCT `genus` 
							  FROM `scientific_names` 
							  WHERE `genus` != '' 
							    AND `genus` != 'Not assigned' " ;
					if ($search_page == "browse_by_classification.php") {
						$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
					}
					$query .= " ORDER BY `genus` " ;
					*/
					$query = "SELECT `name` 
							  FROM `hard_coded_taxon_lists` 
							  WHERE `rank` = 'genus' " ;
					if ($search_page == "browse_by_classification.php") {
						$query .= " AND `accepted_names_only` = 1 " ;
					} else {
						$query .= " AND `accepted_names_only` = 0 " ;
					}
					
					if ($selected_letter == "") {
						$selected_letter = "A" ;
						if ($search_genus != "") {
							$first_char = strtoupper(substr($search_genus,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
				}
			} elseif ($show_taxon == "species") {
				if ($search_kingdom . $search_phylum . $search_class . $search_order . $search_family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`species` 
							  FROM `scientific_names`,`families` 
							  WHERE `scientific_names`.`species` != '' 
							    AND `scientific_names`.`species` != 'Not assigned'
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_page == "browse_by_classification.php") {
						$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
					}
					if ($search_kingdom != "") {
						$query .= " AND `families`.`kingdom` = '$search_kingdom'" ;
					}
					if ($search_phylum != "") {
						$query .= " AND `families`.`phylum` = '$search_phylum'" ;
					}
					if ($search_class != "") {
						$query .= " AND `families`.`class` = '$search_class'" ;
					}
					if ($search_order != "") {
						$query .= " AND `families`.`order` = '$search_order'" ;
					}
					if ($search_family != "") {
						$query .= " AND `families`.`family` = '$search_family'" ;
					}
					if ($search_genus != "") {
						$query .= " AND `scientific_names`.`genus` = '$search_genus'" ;
					}
					$query .= " ORDER BY `scientific_names`.`species`" ;
				} else {
					if ($search_genus == "") {
						$query = "SELECT `name` 
								  FROM `hard_coded_taxon_lists` 
								  WHERE `rank` = 'species' " ;
						if ($search_page == "browse_by_classification.php") {
							$query .= " AND `accepted_names_only` = 1 " ;
						} else {
							$query .= " AND `accepted_names_only` = 0 " ;
						}
					} else {
						$query = "SELECT DISTINCT `species` 
								  FROM `scientific_names`
								  WHERE `species` != '' 
									AND `species` != 'Not assigned' " ;
						if ($search_page == "browse_by_classification.php") {
							$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
						}
						if ($search_genus != "") {
							$query .= " AND `genus` = '$search_genus'" ;
						}
						$query .= " ORDER BY `species`" ;
					}
					
					if ($selected_letter == "" && $search_genus == "") {
						$selected_letter = "A" ;
						if ($search_species != "") {
							$first_char = strtoupper(substr($search_species,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
				}
			} elseif ($show_taxon == "infraspecies") {
				if ($search_kingdom . $search_phylum . $search_class . $search_order . $search_family != "") {
					$query = "SELECT DISTINCT `scientific_names`.`infraspecies` 
							  FROM `scientific_names`,`families` 
							  WHERE `scientific_names`.`infraspecies` != '' 
								AND `scientific_names`.`family_id` = `families`.`record_id` " ;
					if ($search_page == "browse_by_classification.php") {
						$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
					}
					if ($search_kingdom != "") {
						$query .= " AND `families`.`kingdom` = '$search_kingdom'" ;
					}
					if ($search_phylum != "") {
						$query .= " AND `families`.`phylum` = '$search_phylum'" ;
					}
					if ($search_class != "") {
						$query .= " AND `families`.`class` = '$search_class'" ;
					}
					if ($search_order != "") {
						$query .= " AND `families`.`order` = '$search_order'" ;
					}
					if ($search_family != "") {
						$query .= " AND `families`.`family` = '$search_family'" ;
					}
					if ($search_genus != "") {
						$query .= " AND `scientific_names`.`genus` = '$search_genus'" ;
					}
					if ($search_species != "") {
						$query .= " AND `scientific_names`.`species` = '$search_species'" ;
					}
					$query .= " ORDER BY `scientific_names`.`infraspecies`" ;
				} else {
					if ($search_genus . $search_species == "") {
						$query = "SELECT `name` 
								  FROM `hard_coded_taxon_lists` 
								  WHERE `rank` = 'infraspecies' " ;
						if ($search_page == "browse_by_classification.php") {
							$query .= " AND `accepted_names_only` = 1 " ;
						} else {
							$query .= " AND `accepted_names_only` = 0 " ;
						}
					} else {
						$query = "SELECT DISTINCT `infraspecies` 
								  FROM `scientific_names` 
								  WHERE `infraspecies` != '' " ;
						if ($search_page == "browse_by_classification.php") {
							$query .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
						}
						if ($search_genus != "") {
							$query .= " AND `genus` = '$search_genus'" ;
						}
						if ($search_species != "") {
							$query .= " AND `species` = '$search_species'" ;
						}
						$query .= " ORDER BY `infraspecies`" ;
					}
					if ($selected_letter == "" && $search_genus . $search_species == "") {
						$selected_letter = "A" ;
						if ($search_infraspecies != "") {
							$first_char = strtoupper(substr($search_infraspecies,0,1)) ;
							if (strpos("#ABCDEFGHIJKLMNOPQRTSUVWXYZ",$first_char) > 0) {
								$selected_letter = $first_char ;
							} else {
								$selected_letter = "#" ;
							}
						}
					}
				}
			}
			
			include "connect_to_database.php" ;
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
			$number_of_names = mysql_num_rows($result) ;
			$full_list = "" ;
			$list_of_names = array() ;
			for ($i = 1 ; $i <= $number_of_names ; $i++) {
				$row = mysql_fetch_row($result) ;
				$found_name = $row[0] ;
				$full_list .= "/" . rawurlencode ($found_name) ;
				$list_of_names[$i] = $found_name ;
			}
			mysql_free_result($result) ;
			mysql_close($link) ;
		}
	}
?>

