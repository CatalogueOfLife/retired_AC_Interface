<?php
	session_start();
?>
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

	function compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,
	  $this_author,$this_kingdom) {
		if ($this_kingdom == "Viruses" || $this_kingdom == "Subviral agents") {
			$scientific_name = $this_species ;
			if ($this_infraspecies != "") {
				if ($this_infraspecies_marker != "") {
					$scientific_name .= " $this_infraspecies_marker" ;
				}
				$scientific_name .= " $this_infraspecies"  ;
			}
		} else {
			$scientific_name = "<i>$this_genus $this_species</i>" ;
			if ($this_infraspecies != "") {
				if ($this_infraspecies_marker != "") {
					$scientific_name .= " $this_infraspecies_marker" ;
				}
				$scientific_name .= " <i>$this_infraspecies</i>"  ;
			}
			if ($this_author != "") {
				$scientific_name .= " $this_author" ;
			}
		}
		return $scientific_name ;
	}
	
	$record_id = 0 ;
	if (isset($_REQUEST["record_id"])) {
		$record_id = ($_REQUEST["record_id"])-0 ;
	}
	
		
	if (isset($_REQUEST["search_type"])) {
		$search_type = trim(stripslashes(urldecode($_REQUEST["search_type"]))) ;
	} else if (isset($_SESSION["ac_search_type"])) {
		$search_type = trim(stripslashes(urldecode($_SESSION["ac_search_type"]))) ;
	} else {
		$search_type = "" ;
	}
	$new_search_url = (($search_type == "") ? "search.php" : urldecode($search_type) . ".php") ;

	// connect to database
	
	include "includes/db_connect.php" ;
	
	// check if it is a synonym
	
	$selected_synonym_header = "" ;
	$query = "SELECT `scientific_names`.`genus` ,
					 `scientific_names`.`species` ,
					 `scientific_names`.`infraspecies_marker` ,
					 `scientific_names`.`infraspecies` ,
					 `scientific_names`.`author` ,
					 `scientific_names`.`name_code` ,
					 `scientific_names`.`accepted_name_code` ,
					 `sp2000_statuses`.`sp2000_status`,
					 `families`.`kingdom`
			  FROM `scientific_names` , `sp2000_statuses`  ,`families`
			  WHERE `scientific_names`.`record_id` = '$record_id'
			    AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`
			    AND `scientific_names`.`family_id` = `families`.`record_id` " ;
				
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$this_genus               = $row[0] ;
	$this_species             = $row[1] ;
	$this_infraspecies_marker = $row[2] ;
	$this_infraspecies        = $row[3] ;
	$this_author			 = $row[4] ;
	$name_code  		      = $row[5] ;
	$accepted_name_code       = $row[6] ;
	$status				 = $row[7] ;
	$this_kingdom			 = $row[8] ;
	
	$is_infraspecies = ($this_infraspecies != "") ;
	
	$page_title = "" ;
	if (strpos(strtolower($status),"accepted name") === FALSE) {
		$is_synonym = "yes" ;
		$selected_synonym_name = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,
		$this_infraspecies,$this_author,$this_kingdom) ;
		$selected_synonym_header = "You selected $selected_synonym_name" .
			((substr($selected_synonym_name,-1) == ".") ? " " : ". ") .
			"This is a" . ((strpos("aehiou",substr($status,0,1)) === false) ? " " : "n ") .
			" $status for:" ;
		$page_title .= strip_tags($selected_synonym_name) .
		  " - a" . ((strpos("aehiou",substr($status,0,1)) === false) ? " " : "n ") . " $status for " ;
		$query = "SELECT `record_id`
				  FROM `scientific_names`
				  WHERE `name_code` = '$accepted_name_code'
				    AND `name_code` LIKE BINARY '$accepted_name_code'" ;
		$result = mysql_query($query) or die("Error: MySQL query failed");
		$row = mysql_fetch_row($result);
		mysql_free_result($result) ;
		$record_id = ($row[0])-0 ;
	} else {
		$is_synonym = "no" ;
	}
	
	// collect fields from database
	
	if ($record_id == 0) {
		die ("<p>Error: invalid or missing record ID<p>") ;
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
					 `scientific_names`.`scrutiny_date` ,
					 `scientific_names`.`family_id`
			  FROM `scientific_names` , `sp2000_statuses`
			  WHERE `scientific_names`.`record_id` = '$record_id'
				AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`" ;
				
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$this_genus               = $row[0] ;
	$this_species             = $row[1] ;
	$this_infraspecies_marker = $row[2] ;
	$this_infraspecies        = $row[3] ;
	$accepted_name_code  = $row[4] ;
	$status              = $row[5] ;
	$author              = $row[6] ;
	$remarks             = $row[7] ;
	$db_id               = $row[8] ;
	$specialist_id       = $row[9] ;
	$web_site     	     = $row[10] ;
	$name_code           = $row[11] ;
	$last_modified		 = $row[12] ;
	$family_id		     = $row[13] ;
	
	if ($this_genus == "") {
		$this_genus = "not assigned" ;
	}
	if ($accepted_name_code == "") {
		$accepted_name_code = $name_code ;
	}
	if ($remarks == "") {
		$remarks = "&#150" ;
	}
	
	if ($name_code == $accepted_name_code) {
		$accepted_genus = $this_genus ;
		$accepted_species = $this_species ;
		$accepted_infraspecies_marker = $this_infraspecies_marker ;
		$accepted_infraspecies = $this_infraspecies ;
	} else {
		$query = "SELECT `genus` ,
						 `species` ,
						 `infraspecies_marker`,
						 `infraspecies`
				  FROM `scientific_names`
				  WHERE `name_code` = '" . addslashes($accepted_name_code) . "'
				    AND `name_code` LIKE BINARY '" . addslashes($accepted_name_code) . "'" ;
		$result = mysql_query($query) or die("Error: MySQL query failed");
		$row = mysql_fetch_row($result);
		$accepted_genus = $row[0] ;
		$accepted_species = $row[1] ;
		$accepted_infraspecies_marker = $row[2] ;
		$accepted_infraspecies = $row[3] ;
	}
	
	// get taxonomic classification
	
	$classification = "" ;
	$this_kingdom = "" ;
	
	$parent_id = "" ;
	$query = "SELECT `record_id` FROM `taxa` WHERE `name_code` = '$name_code' AND `name_code` LIKE BINARY '$name_code'" ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
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
			$result = mysql_query($query) or die("Error: MySQL query failed");
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
			$classification .= "<table cellspacing=\"0\" cellpadding=\"0\">";
			$firstcol="";
			$secondcol="";
			for ($i = 0; $i < count($parents); $i++) {
				$parent_id = $parents[$i] ;
				$query = "SELECT `taxon`,`name_with_italics`,`LSID` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
				$result = mysql_query($query) or die("Error: MySQL query failed");
				$number_of_rows = mysql_num_rows($result);
				if ($number_of_rows > 0) {
					$row = mysql_fetch_row($result);
					$parent_rank = $row[0] ;
					$parent_name = $row[1] ;
					$lsid = $row[2];
					if ($parent_rank == "Kingdom") {
						$this_kingdom = $parent_name  ;
					}
					if ($parent_name != "Not assigned") {
						$parent_name = "<a href=\"browse_taxa.php?selected_taxon=$parent_id\">$parent_name</a>" ;
						if ($parent_rank != "Kingdom") {
							$parent_name = "$parent_rank $parent_name" ;
						}
$requesturi = $_SERVER['PHP_SELF'];
if($_SERVER['QUERY_STRING']>' ')
{
	$requesturi .="?".$_SERVER['QUERY_STRING'];
}
				$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$requesturi."&showlsid=$lsid";
				if(isset($_REQUEST["showlsid"]))
				{
					$param_pos = strpos($requesturi,"&showlsid=");
					$already_there = substr($requesturi,0,$param_pos);
					$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$already_there."&showlsid=$lsid";
					if($lsid==$_REQUEST["showlsid"])
					{
						$firstcol.="<tr><td><p class='fieldvalue'>$parent_name</p></td>";
						$firstcol.="<td><p class='fieldvalue'><small><small>$lsid</small></small></p>";
					}
					else
					{
						$firstcol.="<tr><td><p class='fieldvalue'>$parent_name</p></td>";
						if($lsid!="")
						$firstcol.="<td><p class='fieldvalue'><a href=\"$show_lsid_url\" title=\"Display LSID\"><small>LSID</small></a></p></td>";
						else
						$firstcol.="<td></td>";
					}
				}
				else
				{
						$firstcol.="<tr><td><p class='fieldvalue'>$parent_name</p></td>";
						if($lsid!="")
						$firstcol.="<td><p class='fieldvalue'><a href=\"$show_lsid_url\" title=\"Display LSID\"><small>LSID</small></a></p></td>";
						else
						$firstcol.="<td></td>";
				}
					}
				}
				mysql_free_result($result) ;
			}
		$classification .= "$firstcol";
		$classification .= "</table>";
		}
	}
		
	// compile scientific name
	
	$scientific_name = compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$author,$this_kingdom) ;
	$page_title .= strip_tags($scientific_name)  ;
	
	// collect synonyms
	
	$number_of_synonyms = 0 ;
	$query = "SELECT DISTINCT `scientific_names`.`record_id` ,
							  `scientific_names`.`genus` ,
							  `scientific_names`.`species` ,
							  `scientific_names`.`infraspecies_marker` ,
							  `scientific_names`.`infraspecies` ,
							  `scientific_names`.`author` ,
							  `sp2000_statuses`.`sp2000_status`
			 FROM  `scientific_names` , `sp2000_statuses`
			 WHERE `scientific_names`.`accepted_name_code` = '$name_code'
			   AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`
			   AND LOCATE('accepted name', `sp2000_statuses`.`sp2000_status`) = 0
			 ORDER BY `scientific_names`.`genus`, `scientific_names`.`species`, `scientific_names`.`infraspecies`, `scientific_names`.`author`" ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$number_of_synonyms = mysql_num_rows($result);
	
	$synonyms = array() ;
	$synonyms_id = array() ;
	$synonyms_genus = array() ;
	$synonyms_species = array() ;
	$synonyms_infraspecies_marker = array() ;
	$synonyms_infraspecies = array() ;
	$synonyms_author = array() ;
	$synonyms_status = array() ;
	for ($i = 1; $i <= $number_of_synonyms; $i++) {
		$row = mysql_fetch_row($result) ;
		$synonym_id = $row[0];
		$synonym_genus = $row[1];
		$synonym_species = $row[2];
		$synonym_infraspecies_marker = $row[3];
		$synonym_infraspecies = $row[4];
		$synonym_author = $row[5];
		$synonym_status = $row[6];
		
		$synonyms[$i] = compileScientificName($synonym_genus,$synonym_species,$synonym_infraspecies_marker,$synonym_infraspecies,$synonym_author,$this_kingdom) ;
		$synonyms_id[$i] = $synonym_id ;
		$synonyms_genus[$i] = $synonym_genus ;
		$synonyms_species[$i] = $synonym_species ;
		$synonyms_infraspecies_marker[$i] = $synonym_infraspecies_marker ;
		$synonyms_infraspecies[$i] = $synonym_infraspecies ;
		$synonyms_author[$i] = $synonym_author ;
		$synonyms_status[$i] = $synonym_status ;
		
		$query2 = "SELECT `name_code`
				   FROM `scientific_names` , `sp2000_statuses`
				   WHERE `scientific_names`.`genus` = '" . addslashes($synonym_genus) . "'
				     AND `scientific_names`.`species` = '" . addslashes($synonym_species) . "' " ;
		if ($synonym_infraspecies_marker == "") {
			$query2 .= " AND (`scientific_names`.`infraspecies_marker` = '' OR `scientific_names`.`infraspecies_marker` IS NULL)" ;
		} else {
			$query2 .= " AND `scientific_names`.`infraspecies_marker`  = '" . addslashes($synonym_infraspecies_marker) . "'" ;
		}
		if ($synonym_infraspecies == "") {
			$query2 .= " AND (`scientific_names`.`infraspecies` = '' OR `scientific_names`.`infraspecies` IS NULL)" ;
		} else {
			$query2 .= " AND `scientific_names`.`infraspecies`  = '" . addslashes($synonym_infraspecies) . "'" ;
		}
		if ($author == "") {
			$query2 .= " AND (`scientific_names`.`author` = '' OR `scientific_names`.`author` IS NULL)" ;
		} else {
			$query2 .= " AND `scientific_names`.`author`  = '" . addslashes($synonym_author) . "'" ;
		}
		$query2 .= " AND `scientific_names`.`author` = '" . addslashes($synonym_author) . "'
					 AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`
					 AND `sp2000_statuses`.`sp2000_status` = '" . addslashes($synonym_status) . "' " ;
		$result2 = mysql_query($query2) or die("Error: MySQL query failed");
		$number_of_name_codes = mysql_num_rows($result2);
		$synonyms_references[$i] = 0 ;
		for ($j = 0 ; $j < $number_of_name_codes ; $j++) {
			$row2 = mysql_fetch_row($result2) ;
			$synonym_name_code = $row2[0] ;
			$query3 = "SELECT DISTINCT `scientific_name_references` . `reference_id`
					  FROM `scientific_name_references`
					  WHERE `scientific_name_references`.`name_code` = '" . addslashes($synonym_name_code) . "'
					     AND `scientific_name_references`.`name_code` LIKE BINARY '" . addslashes($synonym_name_code) . "'
						AND (`scientific_name_references`.`reference_type` = 'NomRef'
						  OR `scientific_name_references`.`reference_type` = 'TaxAccRef'
						  OR `scientific_name_references`.`reference_type` = ''
						  OR `scientific_name_references`.`reference_type` IS NULL)" ;
			$result3 = mysql_query($query3) or die("Error: MySQL query failed");
			$synonyms_references[$i] += mysql_num_rows($result3);
		}
					 
	}
	mysql_free_result($result) ;
	
	
	// collect infraspecies for a species
	
	$infraspecies_for_species = "" ;
	if ($this_infraspecies == "") {
		$query = "SELECT `scientific_names`.`record_id` ,
						 `scientific_names`.`infraspecies_marker` ,
						 `scientific_names`.`infraspecies` ,
						 `scientific_names`.`author`,
						 `scientific_names`.`name_code`,
						 `scientific_names`.`accepted_name_code`
				  FROM `scientific_names` , `sp2000_statuses`
				  WHERE `scientific_names`.`genus` = '" . addslashes(strip_tags($this_genus)) . "'
					AND `scientific_names`.`species` = '" . addslashes(strip_tags($this_species)) . "'
					AND `scientific_names`.`infraspecies` != '' AND `scientific_names`.`infraspecies` IS NOT NULL
					AND `scientific_names`.`sp2000_status_id` =  `sp2000_statuses`.`record_id`
					AND LOCATE('accepted name', `sp2000_statuses`.`sp2000_status`) > 0" ;
		$result = mysql_query($query) or die("Error: MySQL query failed");
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
				$infraspecies_for_species .= "<p class=\"fieldvalue\"><a href=\"show_species_details.php?record_id=$this_id\">" ;
				$infraspecies_for_species .= compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_author,$this_kingdom) ;
				$infraspecies_for_species .= "</a></p>\n" ;
			}
		}
	}
	
	$query = "SELECT `database_name` ,
					 `database_full_name` ,
					 `version`
			  FROM   `databases`
			  WHERE  `record_id` = '$db_id' " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$db_name = $row[0] ;
	$db_fullname = $row[1] ;
	$db_version = $row[2] ;
	
	$query = "SELECT `specialist_name`
			  FROM `specialists` WHERE `record_id` = '$specialist_id'" ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$specialist_name = $row[0] ;
	
	$query = "SELECT `distribution`
			  FROM `distribution`
			  WHERE `name_code` = '$name_code'
			    AND  `name_code` LIKE BINARY '$name_code' " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$this_distribution = "" ;
	while ($row = mysql_fetch_row($result)) {
		$this_distribution .= (($this_distribution == "") ? "" : "; ") . trim($row[0]) ;
	}
	mysql_free_result($result) ;
	if ($this_distribution == "") {
		$this_distribution = "&#150" ;
	}
	
	$query = "SELECT DISTINCT (`common_name`)
			  FROM `common_names`
			  WHERE `name_code` = '$name_code'
			    AND `name_code` LIKE BINARY '$name_code' " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$number_of_common_names = mysql_num_rows($result);
	for ($i = 1; $i <= $number_of_common_names; $i++) {
		$row = mysql_fetch_row($result);
		$this_common_names_names[$i] = $row[0] ;
		$this_common_names_languages[$i] = "" ;
	}
	mysql_free_result($result) ;
	
	if ($number_of_common_names > 0)  {
		for ($i = 1; $i <= $number_of_common_names; $i++) {
			$this_common_name = addslashes ($this_common_names_names[$i]) ;
			$query = "SELECT DISTINCT `language`
					  FROM `common_names`
					  WHERE `language` != '' AND `language` IS NOT NULL
					    AND `common_name` = '$this_common_name'
					  ORDER by `language`" ;
			$result = mysql_query($query) or die("Error: MySQL query failed");
			$number_of_languages = mysql_num_rows($result);
			if ($number_of_languages  > 0) {
				$this_common_names_languages[$i] .= " " ;
				for ($j = 0; $j < $number_of_languages; $j++) {
					$row = mysql_fetch_row($result);
					$this_language = $row[0] ;
					$this_common_names_languages[$i] .= (($j > 0) ? ", " : "") . $this_language ;
				}
			}
			
		}
		mysql_free_result($result) ;
	}
	
	$query = "SELECT DISTINCT `reference_id`
			  FROM `scientific_name_references`
			  WHERE `name_code` = '$name_code'
			   AND `name_code` LIKE BINARY '$name_code'
			    AND (`reference_type` = 'NomRef' OR `reference_type` = 'TaxAccRef' OR `reference_type` = '' OR `reference_type` IS NULL) " ;
	
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$number_of_author_references = mysql_num_rows($result);
	
	mysql_free_result($result) ;
	mysql_close($link) ;
	
	$image_path = "images/db_logos/" . str_replace(" ","_",$db_name). ".jpg" ;
	if (file_exists($image_path)) {
		$image_path = "<p style='margin-bottom:14px'><a href=\"show_database_details.php?database_name=" . urlencode($db_name) ."\">" .
			"<img src=\"$image_path\" border=0 title='Source database for this record: $db_name'></a></p>" ;
	} else {
		$image_path = "" ;
	}
	
?>

<title>Catalogue of Life : 2009 Annual Checklist : <?php echo $page_title ; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="en-GB" />
<meta name="keywords" content="<?php echo strip_tags($scientific_name) ?> biodiversity species 2000 itis taxonomy taxa" />
<meta name="description" content="The Species 2000/ITIS Catalogue of Life : 2006 Annual Checklist
  is a comprehensive index of all known plants, animals, fungi and micro-organisms.
  It can be used to search multiple databases simultaneously for the scientific name of an
  organism." />
<meta name="language" content="en-GB" />
<meta name="robots" content="all" />
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "includes/config.php" ;
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
	if ($is_infraspecies === TRUE) {
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
		<p class="fieldvalue"><?php echo "$scientific_name ($status)" ?>
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
		$label_stripped = str_replace ("\"","",$label) ;
		$label_stripped = str_replace ("\'","",$label_stripped) ;
		$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' title='$label_stripped'>" ;
		echo "<a href=\"show_reference_details.php?" .
		  "record_id=$record_id\" " .
		  "onmouseover=\"return showStatus('$label')\" " .
		  "onmouseout=\"return showStatus('')\">$book_icon</a>" ;

	}
?>
                                          </td>
  </tr>
</table>
   							
									
                                  </td>
                                </tr>
                              <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px">
 <?php
	if ($status == "accepted name" || $status == "provisionally accepted name") {
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
	if ($status == "accepted name" || $status == "provisionally accepted name") {
		if ($number_of_synonyms == 0) {
			echo "<p class=\"fieldvalue\">&#150;</p>\n" ;
		} else {
			for ($i = 1; $i <= $number_of_synonyms; $i++) {
				$synonym = $synonyms[$i] ;
				$synonym_id = $synonyms_id[$i] ;
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
					$label_stripped = str_replace ("\"","",$label) ;
					$label_stripped = str_replace ("\'","",$label_stripped) ;
					
					$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' title='$label_stripped'>" ;

					echo "<a href=\"show_reference_details.php?record_id=$synonym_id\" " .
					  "onmouseover=\"return showStatus('$label')\" " .
					  "onmouseout=\"return showStatus('')\">$book_icon</a>" ;
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
			$this_common_name = $this_common_names_names[$i] ;
			$language = $this_common_names_languages[$i] ;
			echo "<p class='fieldvalue'>" ;
			echo "<a href='show_common_name_details.php?name=" . urlencode($this_common_name) . "'>" ;
			echo $this_common_name . "</a>" . $language . "</p>" ;
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
                                      <?php echo $this_distribution ?>
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
                                    <p class="fieldvalue"><?php echo "<a href=\"show_database_details.php?database_name=" . urlencode($db_name) ."\">$db_fullname</a>, $db_version" ; ?></p>
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
	$web_site = trim($web_site) ;
	if ( substr($web_site,0,1) == "#" ) {
		$web_site = substr($web_site,1,strlen($web_site)-1) ;
	}
	if ( (substr($web_site,0,7) == "http://" || substr($web_site,0,8) == "https://") && strlen($web_site) >= 8 ) {
		$first_slash = strpos($web_site,"/",8) ;
		if ($first_slash != FALSE ) {
			echo "<a href=\"$web_site\" TARGET=\"_blank\">" . substr($web_site,0,$first_slash) . "/...</a>" ;
		} else {
			echo "<a href=\"$web_site\" TARGET=\"_blank\">$web_site</a>" ;
		}
	} elseif ($web_site == "") {
		echo "&#150" ;
	} else {
		echo $web_site ;
	}
?>
                                    </p>
				   <br>
                                  </td>
                                </tr>
                              </table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                          <tr>
                            <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                          </tr>
                        </table>
			<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#EDEBEB">
                          <tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>">
                                  <td valign=top width="150px">
                                    <p class="fieldheader">CoL taxon LSID:</p>
                                  </td>
                                  <td valign=top>
                                    <p class="fieldvalue">
 <?php
					#LSID Changes
					include "includes/db_connect.php" ;
					$lsid_query = "SELECT `lsid`, `record_id`
							  FROM `taxa`
							  WHERE `name_code` = '$name_code'";

					$lsid_result = mysql_query($lsid_query) or die("Error: MySQL query failed");
					$number_of_lsid = mysql_num_rows($lsid_result);
					if ($number_of_lsid  > 0) {
					$row = mysql_fetch_row($lsid_result);
					if($row[0]!="")
					{
						echo "<small><small>$row[0]</small></small>\n";
					}
					}
?>
                                    </p>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <p align=center>
                        <br />
<script language="JavaScript" type="text/javascript">
	showBackLink() ;
</script>
<?php
	echo "<a href=\"$new_search_url\">New search</a>" ;
?>
                        </p>
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
<form name="show_reference_details" method="get" action="show_reference_details.php">
  <input type="hidden" name="name">
  <input type="hidden" name="genus">
  <input type="hidden" name="species">
  <input type="hidden" name="infraspecies_marker">
  <input type="hidden" name="infraspecies">
  <input type="hidden" name="author">
  <input type="hidden" name="status">
</form>
</div>
<?php
	if ($is_synonym == "no") {
		echo "<div id=\"links for spidering\" style=\"height:1px; overflow:hidden; visibility: hidden;\">\n";
		for ($i = 1; $i <= $number_of_synonyms; $i++) {
			$synonym = strip_tags($synonyms[$i]) ;
			$synonym_id = $synonyms_id[$i] ;
			echo "<a title=\"$synonym\" href=\"show_species_details.php?record_id=$synonym_id\">" .
			  "$synonym</a>\n";
		}
		echo "</div>\n";
	}
?>
<?php include_once 'includes/gax.php'; ?>
</body>
</html>
