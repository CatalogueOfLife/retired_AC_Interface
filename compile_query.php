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
		$search_string_lc = urldecode(strtolower($search_string)) ;
		if ($match_whole_words == "on") {
			if (strpos($search_string_lc,"%") === FALSE) {
				$where = " `simple_search`.`words` = '$search_string_lc' " ;
			} else {
				$where = " `simple_search`.`words` LIKE '$search_string_lc' " ;
			}
			$where .= " AND `simple_search`.`taxa_id` = `taxa`.`record_id` " ;
		} else {
			$where = "`taxa`.`name` LIKE '%$search_string_lc%' " ;
		}
		$where .= " AND `taxa`.`is_species_or_nonsynonymic_higher_taxon` = 1 " ;
		$where .= " AND (`taxa`.`database_id` = 0 OR `taxa`.`database_id` = `databases`.`record_id`) " ;
		$where .= " AND (`taxa`.`sp2000_status_id` = 0 OR `taxa`.`sp2000_status_id` = `sp2000_statuses`.`record_id`) " ;
		
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
		if ($match_whole_words == "on") {
			$from .= ",`simple_search`" ;
			$select = "DISTINCT " . $select ;
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
			$word_delimiter_chars = "[ \.\"\'\(\),;:-]" ;
			$where2 = "(`common_names`.`common_name` LIKE '%$search_string_lc%' AND 
					(`common_names`.`common_name` LIKE '$search_string_lc' 
					OR `common_names`.`common_name` REGEXP '^" . $search_string_lc . "$word_delimiter_chars+.*$' = 1 
					OR `common_names`.`common_name` REGEXP '^.*$word_delimiter_chars+" . $search_string_lc . "$' = 1 
					OR `common_names`.`common_name` REGEXP '^.*$word_delimiter_chars+" . $search_string_lc . "$word_delimiter_chars+.*$' = 1)) " ;
		} else {
			$where2 = "`common_names`.`common_name` LIKE '%$search_string_lc%'  " ;
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

	} else if ($common_name != "") {
		
		if ($target == "search results") {
			$select = "DISTINCT `common_names`.`common_name`   ,
					   `scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker` ,
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author` , 
					   `scientific_names`.`name_code`,
				       `databases`.`database_name` " ;
			$from = "`scientific_names` , `common_names`, `databases`  " ;
		} else if ($target == "export file") {
			$select = "DISTINCT  `common_names`.`common_name` ,
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
		$common_name_lc = addslashes(strtolower($common_name)) ;
		
		if ($match_whole_words == "on") {
			$word_delimiter_chars = "[ \.\"\'\(\),;:-]" ;
			$where = "(`common_names`.`common_name` LIKE '%$common_name_lc%' AND 
					(`common_names`.`common_name` LIKE '$common_name_lc' 
					OR `common_names`.`common_name` REGEXP '^" . $common_name_lc . "$word_delimiter_chars+.*$' = 1 
					OR `common_names`.`common_name` REGEXP '^.*$word_delimiter_chars+" . $common_name_lc . "$' = 1 
					OR `common_names`.`common_name` REGEXP '^.*$word_delimiter_chars+" . $common_name_lc . "$word_delimiter_chars+.*$' = 1)) " ;
		} else {
			$where = "`common_names`.`common_name` LIKE '%$common_name_lc%'  " ;
		}

		$where .= " AND `scientific_names`.`name_code` = `common_names`.`name_code`
				  AND `scientific_names`.`name_code` LIKE BINARY `common_names`.`name_code`
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
			$word_delimiter_chars = "[ \.\"\'\(\),;:-]" ;
			$where = "(`distribution`.`distribution` LIKE '%$area_lc%' AND 
					(`distribution`.`distribution` LIKE '$area_lc'
					OR `distribution`.`distribution` REGEXP '^" . $area_lc . "$word_delimiter_chars+.*$' = 1 
					OR `distribution`.`distribution` REGEXP '^.*$word_delimiter_chars+" . $area_lc . "$' = 1 
					OR `distribution`.`distribution` REGEXP '^.*$word_delimiter_chars+" . $area_lc . "$word_delimiter_chars+.*$' = 1)) " ;						
		} else {
			$where = "`distribution`.`distribution` LIKE '%$area_lc%' " ;
		}
		$where .= "		AND `scientific_names`.`name_code` = `distribution`.`name_code`
						AND `scientific_names`.`name_code` LIKE BINARY `distribution`.`name_code` 
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
	} else if ($kingdom . $phylum . $tax_class . $order . $superfamily . $family . 
	  $genus . $species . $infraspecies == "") {
		$select = "" ;
		$number_of_records_found = 0 ;
	} else if ($kingdom . $phylum . $tax_class . $order . $superfamily. $family  == "") {
		
		$these_ranks = array("genus","species","infraspecies") ;
		$where = "" ;
		foreach ($these_ranks as $this_rank) {
			if ($$this_rank == "") {
				continue ;
			}
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`" . $this_rank . "` " ;
			if ($match_whole_words == "on") {
				if (strpos($$this_rank,"%") === false) {
					$where .= "= '" . addslashes($$this_rank) . "'" ;
				} else {
					$where .= "LIKE '" . addslashes($$this_rank) . "'" ;
				}
			} else {
				$where .= "LIKE '%" . addslashes($$this_rank) . "%'" ;
			}
		}
		if ($search_type == "browse_by_classification") {
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`is_accepted_name` = 1 " ;
		}
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
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id` " ;
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
			$where .= (($where == "") ? "" : " AND ") . " `sp2000_statuses`.`record_id` = `scientific_names`.`sp2000_status_id` 
				  AND `scientific_names`.`database_id` = `databases`.`record_id`
				  AND `scientific_names`.`family_id` = `families`.`record_id`" ;
			$from = "`scientific_names` , `sp2000_statuses` , `databases` , `families` " ;
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
	} else {
		$these_ranks = array("family" => "families", 
						 "superfamily" => "families", 
						 "order" => "families", 
						 "tax_class" => "families", 
						 "phylum" => "families", 
						 "kingdom" => "families",
						 "genus" => "scientific_names", 
						 "species" => "scientific_names", 
						 "infraspecies" => "scientific_names") ;
		$where = "" ;
		foreach ($these_ranks as $this_rank => $this_table) {
			if ($$this_rank == "") {
				continue ;
			}
			$where .= (($where == "") ? "" : " AND ") . "`$this_table`.`" . (($this_rank == "tax_class") ? "class" : $this_rank) . "` " ;
			if ($match_whole_words == "on") {
				if (strpos($$this_rank,"%") === false) {
					$where .= "= '" . addslashes($$this_rank) . "'" ;
				} else {
					$where .= "LIKE '" . addslashes($$this_rank) . "'" ;
				}
			} else {
				$where .= "LIKE '%" . addslashes($$this_rank) . "%'" ;
			}
		}
		if ($search_type == "browse_by_classification") {
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`is_accepted_name` = 1 " ;
		}
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
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`  
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
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
			$where .= (($where == "") ? "" : " AND ") . "`scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
						AND `scientific_names`.`database_id` = `databases`.`record_id` 
						AND `scientific_names`.`family_id` = `families`.`record_id` " ;
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
			$number_of_records_found = getHardCodedSpeciesTotal("Animalia") ;
		} else if ($kingdom == "Plantae" && $phylum . $tax_class . 
		  $order . $superfamily . $family . $genus . 
		  $species .  $infraspecies == "") {	
			$number_of_records_found = getHardCodedSpeciesTotal("Plantae") ;
		} else if ($kingdom == "Animalia" && $phylum == "Arthropoda" && 
		  $tax_class . $order . $superfamily . $family . 
		  $genus . $species .  $infraspecies == "") {	
			$number_of_records_found = getHardCodedSpeciesTotal("Arthropoda") ;
		} else if ($kingdom == "Animalia" && $phylum == "Chordata" && 
		  $tax_class . $order . $superfamily . $family . $genus . 
		  $species .  $infraspecies == "") {	
			$number_of_records_found = getHardCodedSpeciesTotal("Chordata") ;
		}
		
	}
?>