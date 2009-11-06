<?php
	function getHardCodedSpeciesTotal($taxon) {
		$species_total = 0 ;
		include "connect_to_database.php" ;
		$query = "SELECT `species_count` FROM `hard_coded_species_totals` WHERE `taxon` = '$taxon'" ;
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		$number_of_rows = mysql_num_rows($result);
		if ($number_of_rows > 0) {
			$row = mysql_fetch_row($result);
			$species_total = $row[0] ;
		}
		mysql_free_result($result) ;
		return $species_total ;
	}
	
	if ($search_simple != "") {
		$ranks = "Common name,Kingdom,Phylum,Class,Order,Family,Genus,Species,Infraspecies" ;
		$search_simple_lc = strtolower($search_simple) ;
		if ($search_mode == "whole words") {
			if (strpos($search_simple_lc,"%") === FALSE) {
				$name_search_query = "( `taxa`.`name` = '$search_simple_lc' 
					   OR `taxa`.`name` LIKE '% $search_simple_lc' 
					   OR `taxa`.`name` LIKE '$search_simple_lc %' 
					   OR `taxa`.`name` LIKE '% $search_simple_lc %' )" ;
			} else {
				$name_search_query = "( `taxa`.`name` LIKE '$search_simple_lc' 
					   OR `taxa`.`name` LIKE '% $search_simple_lc' 
					   OR `taxa`.`name` LIKE '$search_simple_lc %' 
					   OR `taxa`.`name` LIKE '% $search_simple_lc %' )" ;
			}
		} else {
			$name_search_query = "`taxa`.`name` LIKE '%$search_simple_lc%' " ;
		}
		
		$select = "`taxa`.`record_id` , 
				   `taxa`.`name_with_italics`, 
				   `taxa`.`taxon` , 
				   `taxa`.`name_code` ,
				   `taxa`.`name` as name_sort_key , " ;
		$select .= "CASE `taxa`.`sp2000_status_id` WHEN '0' THEN 'zzzzzz' ELSE `sp2000_statuses`.`sp2000_status` END as status_sort_key, " ;
		if ($sort_by_column == 2) {
			$select .= "FIND_IN_SET(`taxa`.`taxon`, '$ranks') as rank_sort_key, " ;
		} else {
			$select .= "'' as rank_sort_key, " ;
		}
		if ($target == "search results") {
			$select .= " CASE `taxa`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_name` END database_sort_key," ;
		} else if ($target == "export file") {
			$select .= " CASE `taxa`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_full_name` END database_sort_key," ;
		}
		$select .= " `taxa`.`is_accepted_name` " ;
		$from = "`taxa` ,`sp2000_statuses`,`databases` " ;
		$where = $name_search_query  ;
		$where .= " AND ((`taxa`.`sp2000_status_id` = 0 AND `taxa`.`is_accepted_name` = 1) 
				       OR `taxa`.`sp2000_status_id` = `sp2000_statuses`.`record_id`) " ;
		$where .= " AND (`taxa`.`database_id` = 0 OR `taxa`.`database_id` = `databases`.`record_id`) " ;
		$select2 = "'' ,
					`common_names`.`common_name` ,
					CASE `common_names`.`is_infraspecies` WHEN '0' THEN 'Species' ELSE 'Infraspecies' END ,
				   `common_names`.`name_code` ,
					lower(`common_names`.`common_name`) as name_sort_key , " ;
		$select2 .= "'common name' status_sort_key , " ;
		if ($sort_by_column == 2) {
			$select2 .= "CASE `common_names`.`is_infraspecies` WHEN '0' THEN FIND_IN_SET('Species', '$ranks') ELSE FIND_IN_SET('Infraspecies', '$ranks') END rank_sort_key, " ;
		} else {
			$select2 .= "'' as rank_sort_key, "  ;
		}
		
		if ($target == "search results") {
			$select2 .= " CASE `common_names`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_name` END database_sort_key, " ;
		} else if ($target == "export file") {
			$select2 .= " CASE `common_names`.`database_id` WHEN 0 THEN '' ELSE `databases`.`database_full_name` END database_sort_key, " ;
		}
		$select2 .= " '1' " ;
		$from2 = "`common_names`,`databases` " ;
		if ($search_mode == "whole words") {
			if (strpos($search_simple_lc,"%") === FALSE) {
				$where2 = "(`common_names`.`common_name` = '$search_simple_lc' 
						OR `common_names`.`common_name` LIKE '% $search_simple_lc %' 
						OR `common_names`.`common_name` LIKE '% $search_simple_lc' 
						OR `common_names`.`common_name` LIKE '$search_simple_lc %' ) " ;
			} else {
				$where2 = "(`common_names`.`common_name` LIKE '$search_simple_lc' 
						OR `common_names`.`common_name` LIKE '% $search_simple_lc %' 
						OR `common_names`.`common_name` LIKE '% $search_simple_lc' 
						OR `common_names`.`common_name` LIKE '$search_simple_lc %' ) " ;
			}
		} else {
			$where2 = "`common_names`.`common_name` LIKE '%$search_simple_lc%' " ;
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

	} else if ($search_common_name != "") {
		
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
				   	   `families`.`family`,
				       `databases`.`database_full_name` as source_database" ;
			$from = "`scientific_names` , `common_names`, `families`, `databases` " ;
		}
		$search_common_name_lc = strtolower($search_common_name) ;
		if ($search_mode == "whole words") {
			$where = "(`common_names`.`common_name` LIKE '$search_common_name_lc' 
					OR `common_names`.`common_name` LIKE '% $search_common_name_lc %' 
					OR `common_names`.`common_name` LIKE '% $search_common_name_lc' 
					OR `common_names`.`common_name` LIKE '$search_common_name_lc %' ) " ;
		} else {
			$where = "`common_names`.`common_name` LIKE '%$search_common_name_lc%'  " ;
		}
		$where .= " AND `scientific_names`.`name_code` = `common_names`.`name_code`
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
	} else if ($search_distribution != "") {
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
				   	   `families`.`family` ,
					   `distribution`.`distribution`,
				       `databases`.`database_full_name` as source_database " ;
		}
		$from = "`scientific_names`, `distribution`, `families` , `databases` " ;
		$search_distribution_lc = strtolower($search_distribution) ;
		if ($search_mode == "whole words") {
			$where = "(`distribution`.`distribution` LIKE '$search_distribution_lc' 
						OR `distribution`.`distribution` LIKE '$search_distribution_lc %' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc %' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc,%'
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc.%' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc;%' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc:%' 
						OR `distribution`.`distribution` LIKE '% $search_distribution_lc/%' ) " ;
			
		} else {
			$where = "`distribution`.`distribution` LIKE '%$search_distribution_lc%' " ;
		}
		$where .= "		AND `scientific_names`.`name_code` = `distribution`.`name_code`
						AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` 
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
	} else if ($search_kingdom . $search_phylum . $search_class . $search_order . $search_family . 
	  $search_genus . $search_species . $search_infraspecies == "") {
		$select = "" ;
		$number_of_records_found = 0 ;
	} else if ($search_kingdom . $search_phylum . $search_class . $search_order . $search_family  == "") {
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
			$where = "`sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id` " ;
			if ($search_page == "browse_by_classification.php") {
				$where .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
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
				   	   `families`.`family` " ;
			$where = "`sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id`
				  AND `scientific_names`.`family_id` = `families`.`record_id`" ;
			if ($search_page == "browse_by_classification.php") {
				$where .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
			}
			$from = "`scientific_names` , `sp2000_statuses` , `databases` , `families` " ;
		}
		
		if ($search_genus != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`genus` LIKE '$search_genus'" ;
			} else {
				$where .= " AND `scientific_names`.`genus` LIKE '%$search_genus%'" ;
			}
		}
		if ($search_species != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`species` LIKE '$search_species'" ;
			} else {
				$where .= " AND `scientific_names`.`species` LIKE '%$search_species%'" ;
			}
		}
		if ($search_infraspecies != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '$search_infraspecies'" ;
			} else {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '%$search_infraspecies%'" ;
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
			$where = "`sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if ($search_page == "browse_by_classification.php") {
				$where .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
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
				   	   `families`.`family` " ;
			$from = "`scientific_names`, `families` , `sp2000_statuses` , `databases` " ; 
			$where = "`sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if ($search_page == "browse_by_classification.php") {
				$where .= " AND `scientific_names`.`name_code` = `scientific_names`.`accepted_name_code` " ;
			}
		}
		if ($search_kingdom != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `families`.`kingdom` LIKE '$search_kingdom'" ;
			} else {
				$where .= " AND `families`.`kingdom` LIKE '%$search_kingdom%'" ;
			}
		}
		if ($search_phylum != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `families`.`phylum` LIKE '$search_phylum'" ;
			} else {
				$where .= " AND `families`.`phylum` LIKE '%$search_phylum%'" ;
			}
		}
		if ($search_class != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `families`.`class` LIKE '$search_class'" ;
			} else {
				$where .= " AND `families`.`class` LIKE '%$search_class%'" ;
			}
		}
		if ($search_order != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `families`.`order` LIKE '$search_order'" ;
			} else {
				$where .= " AND `families`.`order` LIKE '%$search_order%'" ;
			}
		}
		if ($search_family != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `families`.`family` LIKE '$search_family'" ;
			} else {
				$where .= " AND `families`.`family` LIKE '%$search_family%'" ;
			}
		}
		if ($search_genus != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`genus` LIKE '$search_genus'" ;
			} else {
				$where .= " AND `scientific_names`.`genus` LIKE '%$search_genus%'" ;
			}
		}
		if ($search_species != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`species` LIKE '$search_species'" ;
			} else {
				$where .= " AND `scientific_names`.`species` LIKE '%$search_species%'" ;
			}
		}
		if ($search_infraspecies != "") {
			if ($search_mode == "whole words") {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '$search_infraspecies'" ;
			} else {
				$where .= " AND `scientific_names`.`infraspecies` LIKE '%$search_infraspecies%'" ;
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
		if ($search_kingdom == "Animalia" && $search_phylum . $search_class . $search_order . 
		  $search_family . $search_genus . $search_species . $search_infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Animalia") ;
		} else if ($search_kingdom == "Plantae" &&  $search_phylum . $search_class . 
		  $search_order . $search_family . $search_genus . $search_species .  $search_infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Plantae") ;
		} else if ($search_kingdom == "Animalia" && $search_phylum == "Arthropoda" && $search_class . 
		  $search_order . $search_family . $search_genus . $search_species .  $search_infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Arthropoda") ;
		} else if ($search_kingdom == "Animalia" && $search_phylum == "Chordata" && $search_class . 
		  $search_order . $search_family . $search_genus . $search_species .  $search_infraspecies == "") {
			$query_number_of_hits = "" ;	
			$number_of_records_found = getHardCodedSpeciesTotal("Chordata") ;
		} else {
			$query_number_of_hits = "SELECT COUNT(* ) 
								FROM $from 
								WHERE $where" ;
		}
	}
?>