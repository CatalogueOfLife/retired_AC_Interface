<?php
	include "connect_to_database.php" ;
	
	function getHardCodedSpeciesTotal($taxon) {
		$species_total = 0 ;
		$query = "SELECT `species_count` FROM `hard_coded_species_totals` WHERE `taxon` = '$taxon'" ;
		$result = mysql_query($query) or die("Error: MySQL query failed");
		$number_of_rows = mysql_num_rows($result);
		if ($number_of_rows > 0) {
			$row = mysql_fetch_row($result);
			$species_total = $row[0] ;
		}
		mysql_free_result($result) ;
		return $species_total ;
	}
	
	if ($search_string != "") {
		$ranks = "Common name,Kingdom,Phylum,Class,Order,Superfamily,Family,Genus,Species,Infraspecies" ;
		$search_string_lc = strtolower($search_string) ;
		$use_search_string_table = ($online_or_offline_version == "online" && $match_whole_words == "on") ;
		if ($use_search_string_table) {
			if (strpos($search_string_lc,"%") === FALSE) {
				$name_search_query = " `simple_search`.`words` = '$search_string_lc' " ;
			} else {
				$name_search_query = " `simple_search`.`words` LIKE '$search_string_lc' " ;
			}
		} else if ($match_whole_words == "on") {
			if (strpos($search_string_lc,"%") === FALSE) {
				$name_search_query = "( `taxa`.`name` = '$search_string_lc' 
					   OR `taxa`.`name` LIKE '% $search_string_lc' 
					   OR `taxa`.`name` LIKE '$search_string_lc %' 
					   OR `taxa`.`name` LIKE '% $search_string_lc %' )" ;
			} else {
				$name_search_query = "( `taxa`.`name` LIKE '$search_string_lc' 
					   OR `taxa`.`name` LIKE '% $search_string_lc' 
					   OR `taxa`.`name` LIKE '$search_string_lc %' 
					   OR `taxa`.`name` LIKE '% $search_string_lc %' )" ;
			}
		} else {
			$name_search_query = "`taxa`.`name` LIKE '%$search_string_lc%' " ;
		}
		
		$select = "`taxa`.`record_id` , 
				   `taxa`.`name_with_italics`, 
				   `taxa`.`taxon` , 
				   `taxa`.`name_code` ,
				   `taxa`.`name` as name_sort_key , " ;
		$select .= "CASE `taxa`.`sp2000_status_id` WHEN '0' THEN 'zzzzzz' ELSE `sp2000_statuses`.`sp2000_status` END AS status_sort_key, " ;
		if ($sort_by_column == 2) {
			$select .= "FIND_IN_SET(`taxa`.`taxon`, '$ranks') AS rank_sort_key, " ;
		} else {
			$select .= "'' AS rank_sort_key, " ;
		}
		if ($target == "search results") {
			$select .= " CASE `taxa`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_name` END AS database_sort_key," ;
		} else if ($target == "export file") {
			$select .= " CASE `taxa`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_full_name` END AS database_sort_key," ;
		}
		$select .= " `taxa`.`is_accepted_name` " ;
		$from = "`taxa` ,`sp2000_statuses`,`databases` " ;
		$where = $name_search_query  ;
		$where .= " AND `taxa`.`simple_search` = 1 " ;
		$where .= " AND (`taxa`.`database_id` = 0 OR `taxa`.`database_id` = `databases`.`record_id`) " ;
		$where .= " AND (`taxa`.`sp2000_status_id` = 0 OR `taxa`.`sp2000_status_id` = `sp2000_statuses`.`record_id`) " ;
		if ($use_search_string_table) {
			$from .= ",`simple_search`" ;
			$select = "DISTINCT " . $select ;
			$where .= " AND `simple_search`.`taxa_id` = `taxa`.`record_id` " ;
		}
		
		$select2 = "'' ,
					`common_names`.`common_name` ,
					CASE `common_names`.`is_infraspecies` WHEN '0' THEN 'Species' ELSE 'Infraspecies' END ,
				   `common_names`.`name_code` ,
					lower(`common_names`.`common_name`) AS name_sort_key , " ;
		$select2 .= "'common name' status_sort_key , " ;
		if ($sort_by_column == 2) {
			$select2 .= "CASE `common_names`.`is_infraspecies` WHEN '0' THEN FIND_IN_SET('Species', '$ranks') ELSE FIND_IN_SET('Infraspecies', '$ranks') END AS rank_sort_key, " ;
		} else {
			$select2 .= "'' AS rank_sort_key, "  ;
		}
		if ($target == "search results") {
			$select2 .= " CASE `common_names`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_name` END AS database_sort_key, " ;
		} else if ($target == "export file") {
			$select2 .= " CASE `common_names`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_full_name` END AS database_sort_key, " ;
		}
		$select2 .= " '1' " ;
		$from2 = "`common_names`,`databases` " ;
		if ($match_whole_words == "on") {
			if (strpos($search_string_lc,"%") === FALSE) {
				$where2 = "(`common_names`.`common_name` = '$search_string_lc' 
						OR `common_names`.`common_name` LIKE '% $search_string_lc %' 
						OR `common_names`.`common_name` LIKE '% $search_string_lc' 
						OR `common_names`.`common_name` LIKE '$search_string_lc %' ) " ;
			} else {
				$where2 = "(`common_names`.`common_name` LIKE '$search_string_lc' 
						OR `common_names`.`common_name` LIKE '% $search_string_lc %' 
						OR `common_names`.`common_name` LIKE '% $search_string_lc' 
						OR `common_names`.`common_name` LIKE '$search_string_lc %' ) " ;
			}
		} else {
			$where2 = "`common_names`.`common_name` LIKE '%$search_string_lc%' " ;
		}
		$where2 .= " AND `common_names`.`database_id` = `databases`.`record_id` " ;
		if ($sort_by_column == 1) {
			$order_by = "name_sort_key, status_sort_key" ;
		} else if ($sort_by_column == 2) {
			$order_by = "rank_sort_key ASC, name_sort_key" ;
		} else if ($sort_by_column == 3) {
			$order_by = "status_sort_key, name_sort_key" ;
		} else {
			$order_by = "database_sort_key, name_sort_key" ;
		}
		$query_number_of_hits = "" ;

	} else if ($common_name != "") {
		
		if ($target == "search results") {
			$select = "DISTINCT ( `common_names`.`common_name`  ) ,
					   `scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker` ,
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author` , 
					   `scientific_names`.`name_code`,
				       `databases`.`database_name` " ;
			$from = "`scientific_names` , `common_names`, `databases`  " ;
		} else if ($target == "export file") {
			$select = "DISTINCT ( `common_names`.`common_name`  ) ,
					   `common_names`.`language` , 
					   `common_names`.`country` , 
					   `scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker`,
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author` , 
				   	   `families`.`kingdom` , 
				   	   `families`.`class` , 
				   	   `families`.`order` , 
				   	   `families`.`superfamily`,
				   	   `families`.`family`,
				       `databases`.`database_full_name` as source_database" ;
			$from = "`scientific_names` , `common_names`, `families`, `databases` " ;
		}
		$common_name_lc = strtolower($common_name) ;
		if ($match_whole_words == "on") {
			$where = "(`common_names`.`common_name` LIKE '$common_name_lc' 
					OR `common_names`.`common_name` LIKE '% $common_name_lc %' 
					OR `common_names`.`common_name` LIKE '% $common_name_lc' 
					OR `common_names`.`common_name` LIKE '$common_name_lc %' ) " ;
		} else {
			$where = "`common_names`.`common_name` LIKE '%$common_name_lc%'  " ;
		}
		$where .= " AND `scientific_names`.`name_code` = `common_names`.`name_code`
					AND `scientific_names`.`is_accepted_name` = 1
					AND `scientific_names`.`database_id` = `databases`.`record_id` " ;
		if ($target == "export file") {
			$where .= " AND `scientific_names`.`family_id` = `families`.`record_id` " ;
		}
		if ($sort_by_column == 1) {
			$order_by = "`common_names`.`common_name`, 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author` " ;
		} else if ($sort_by_column == 2) {
			$order_by = "`scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` ,
						 `scientific_names`.`author` ,
						 `common_names`.`common_name` " ;
		} else {
			$order_by = "`databases`. `database_name` ,
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` ,
						 `scientific_names`.`author`  " ;
		}
		$query_number_of_hits = "SELECT COUNT($select) 
								FROM $from
								WHERE $where" ;
	} else if ($area != "") {
		if ($target == "search results") {
			$select = "`scientific_names`.`record_id` , 
					   `scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker` , 
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author` ,
					   `distribution`.`distribution`, 
					   `families`.`kingdom` ,
				       `databases`.`database_name`" ;
		} else if ($target == "export file") {
			$select = "`scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker` , 
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author`, 
					   `families`.`kingdom` , 
				   	   `families`.`class` , 
				   	   `families`.`order` , 
				   	   `families`.`superfamily` ,
				   	   `families`.`family` ,
					   `distribution`.`distribution`,
				       `databases`.`database_full_name` as source_database " ;
		}
		$from = "`scientific_names`, `distribution`, `families` , `databases` " ;
		$area_lc = strtolower($area) ;
		if ($match_whole_words == "on") {
			$where = "(`distribution`.`distribution` LIKE '$area_lc' 
						OR `distribution`.`distribution` LIKE '$area_lc %' 
						OR `distribution`.`distribution` LIKE '% $area_lc %' 
						OR `distribution`.`distribution` LIKE '% $area_lc' 
						OR `distribution`.`distribution` LIKE '% $area_lc,%'
						OR `distribution`.`distribution` LIKE '% $area_lc.%' 
						OR `distribution`.`distribution` LIKE '% $area_lc;%' 
						OR `distribution`.`distribution` LIKE '% $area_lc:%' 
						OR `distribution`.`distribution` LIKE '% $area_lc/%' ) " ;
			
		} else {
			$where = "`distribution`.`distribution` LIKE '%$area_lc%' " ;
		}
		$where .= "		AND `scientific_names`.`name_code` = `distribution`.`name_code`
						AND `scientific_names`.`family_id` = `families`.`record_id` 
						AND `scientific_names`.`database_id` = `databases`.`record_id` " ;
		
		if ($sort_by_column == 1) {
			$order_by = "`distribution`.`distribution` ,
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies`, 
						 `scientific_names`.`author` " ;
		} else if ($sort_by_column == 2) {
			$order_by = "`scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author` " ;
		} else {
			$order_by = "`databases`.`database_name` , 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author` " ;
		}
		
		$query_number_of_hits = "SELECT COUNT(*) 
								FROM $from
								WHERE $where" ;
	} else if ($kingdom . $phylum . $tax_class . $order . $superfamily . $family . 
	  $genus . $species . $infraspecies == "") {
		$select = "" ;
		$number_of_records_found = 0 ;
	} else if ($kingdom . $phylum . $tax_class . $order . $superfamily. $family  == "") {
		if ($target == "search results") {
			$select = "`scientific_names`.`record_id` , 
				       `scientific_names`.`genus` , 
				       `scientific_names`.`species` , 
				       `scientific_names`.`infraspecies_marker` , 
				       `scientific_names`.`infraspecies` , 
				       `scientific_names`.`author` ,
				       `scientific_names`.`name_code` ,
				       `scientific_names`.`accepted_name_code` ,
				       `sp2000_statuses`.`sp2000_status` ,
				       `databases`.`database_name`, 
				       `scientific_names`.`family_id` " ;
			$where = "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id` " ;
			if ($search_type == "browse_by_classification") {
				$where .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
			$from = "`scientific_names`, `sp2000_statuses`, `databases` " ;
		} else if ($target == "export file") {
			$select = "`scientific_names`.`genus` , 
				       `scientific_names`.`species` , 
				       `scientific_names`.`infraspecies_marker` , 
				       `scientific_names`.`infraspecies` , 
				       `scientific_names`.`author` ,
				       `scientific_names`.`name_code` ,
				       `scientific_names`.`accepted_name_code` ,
				       `sp2000_statuses`.`sp2000_status` ,
				       `databases`.`database_name`, 
					   `families`.`kingdom` , 
				   	   `families`.`class` , 
				   	   `families`.`order` , 
				   	   `families`.`superfamily` , 
				   	   `families`.`family` " ;
			$where = "`sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id`
				  AND `scientific_names`.`family_id` = `families`.`record_id`" ;
			if ($search_type == "browse_by_classification") {
				$where .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
			$from = "`scientific_names` , `sp2000_statuses` , `databases` , `families` " ;
		}
		
		if ($genus != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`genus` LIKE '$genus'" ;
			} else {
				$where .= " AND `scientific_names`.`genus` LIKE '%$genus%'" ;
			}
		}
		if ($species != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`species` LIKE '$species'" ;
			} else {
				$where .= " AND `scientific_names`.`species` LIKE '%$species%'" ;
			}
		}
		if ($infraspecies != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '$infraspecies'" ;
			} else {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '%$infraspecies%'" ;
			}
		}
		if ($sort_by_column == 1) {
			$order_by = "`scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author` " ;
		} else if ($sort_by_column == 2) {
			$order_by = "`sp2000_statuses`.`sp2000_status` , 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author`" ;
		} else {
			$order_by = "`databases`.`database_name` , 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author`" ;
		}
		
		$query_number_of_hits = "SELECT COUNT(*) 
								FROM $from 
								WHERE $where" ;
	} else {
		if ($target == "search results") {
			$select = "`scientific_names`.`record_id` , 
				       `scientific_names`.`genus` , 
				       `scientific_names`.`species` , 
				       `scientific_names`.`infraspecies_marker` , 
				       `scientific_names`.`infraspecies` , 
				       `scientific_names`.`author` ,
				       `scientific_names`.`name_code` ,
				       `scientific_names`.`accepted_name_code` ,
				       `sp2000_statuses`.`sp2000_status` ,
				       `databases`.`database_name`, 
				       `scientific_names`.`family_id` " ;
			$from = "`scientific_names`, `families` , `sp2000_statuses`, `databases` " ; 
			$where = "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`  
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if ($search_type == "browse_by_classification") {
				$where .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
		} else if ($target == "export file") {
			$select = "`scientific_names`.`genus` , 
				       `scientific_names`.`species` , 
				       `scientific_names`.`infraspecies_marker` , 
				       `scientific_names`.`infraspecies` , 
				       `scientific_names`.`author` ,
				       `scientific_names`.`name_code` ,
				       `scientific_names`.`accepted_name_code` ,
				       `sp2000_statuses`.`sp2000_status` ,
				       `databases`.`database_name`, 
					   `families`.`kingdom` , 
				   	   `families`.`class` , 
				   	   `families`.`order` , 
				   	   `families`.`superfamily` , 
				   	   `families`.`family` " ;
			$from = "`scientific_names`, `families` , `sp2000_statuses` , `databases` " ; 
			$where = "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if ($search_type == "browse_by_classification") {
				$where .= " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
		}
		if ($kingdom != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`kingdom` LIKE '$kingdom'" ;
			} else {
				$where .= " AND `families`.`kingdom` LIKE '%$kingdom%'" ;
			}
		}
		if ($phylum != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`phylum` LIKE '$phylum'" ;
			} else {
				$where .= " AND `families`.`phylum` LIKE '%$phylum%'" ;
			}
		}
		if ($tax_class != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`class` LIKE '$tax_class'" ;
			} else {
				$where .= " AND `families`.`class` LIKE '%$tax_class%'" ;
			}
		}
		if ($order != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`order` LIKE '$order'" ;
			} else {
				$where .= " AND `families`.`order` LIKE '%$order%'" ;
			}
		}
		if ($superfamily != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`superfamily` LIKE '$superfamily'" ;
			} else {
				$where .= " AND `families`.`superfamily` LIKE '%$superfamily%'" ;
			}
		}
		if ($family != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `families`.`family` LIKE '$family'" ;
			} else {
				$where .= " AND `families`.`family` LIKE '%$family%'" ;
			}
		}
		if ($genus != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`genus` LIKE '$genus'" ;
			} else {
				$where .= " AND `scientific_names`.`genus` LIKE '%$genus%'" ;
			}
		}
		if ($species != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`species` LIKE '$species'" ;
			} else {
				$where .= " AND `scientific_names`.`species` LIKE '%$species%'" ;
			}
		}
		if ($infraspecies != "") {
			if ($match_whole_words == "on") {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '$infraspecies'" ;
			} else {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '%$infraspecies%'" ;
			}
		}
		if ($sort_by_column == 1) {
			$order_by = "`scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author` " ;
		} else if ($sort_by_column == 2) {
			$order_by = "`sp2000_statuses`.`sp2000_status` , 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author`" ;
		} else {
			$order_by = "`databases`.`database_name` , 
						 `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies` , 
						 `scientific_names`.`author`" ;
		}
		if ($kingdom == "Animalia" && $phylum . $tax_class . $order . 
		  $superfamily . $family . $genus . $species .
		  $infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Animalia") ;
		} else if ($kingdom == "Plantae" && $phylum . $tax_class . 
		  $order . $superfamily . $family . $genus . 
		  $species .  $infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Plantae") ;
		} else if ($kingdom == "Animalia" && $phylum == "Arthropoda" && 
		  $tax_class . $order . $superfamily . $family . 
		  $genus . $species .  $infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Arthropoda") ;
		} else if ($kingdom == "Animalia" && $phylum == "Chordata" && 
		  $tax_class . $order . $superfamily . $family . $genus . 
		  $species .  $infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Chordata") ;
		} else {
			$query_number_of_hits = "SELECT COUNT(* ) 
								FROM $from 
								WHERE $where" ;
		}
	}
?>