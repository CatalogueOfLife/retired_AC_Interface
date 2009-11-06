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
	
	if (isset($_REQUEST["name"])) {
		$reference_type = "common name" ;
	} else {
		$reference_type = "scientific name" ;
	}
	
	$variables_to_get = Array(
	  "record_id", 
	  "name", 
	  "name_code", 
	  "language", 
	  "country" ) ;
	foreach($variables_to_get as $variable){
		if (isset($_REQUEST[$variable])) {
			$$variable = $_REQUEST[$variable] ;
		} else {
			$$variable = "" ;
		}
		$$variable = trim(stripslashes(urldecode($$variable))) ;
	}
	
	$record_id = ($record_id)-0 ;
	
	if ($reference_type == "scientific name" && $record_id == 0) {
		die ("<p>Error: missing or invalid record id</p>") ;
	} else if ($reference_type == "common name" && $name_code == "") {
		die ("<p>Error: missing or invalid name code</p>") ;
	}
	
	if (isset($_REQUEST["search_type"])) {
		$search_type = trim(stripslashes(urldecode($_REQUEST["search_type"]))) ;
	} else if (isset($_SESSION["ac_search_type"])) {
		$search_type = trim(stripslashes(urldecode($_SESSION["ac_search_type"]))) ;
	} else {
		$search_type = "" ;
	}
	$new_search_url = (($search_type == "") ? "search.php" : urldecode($search_type) . ".php") ;

	include "connect_to_database.php" ;
	
	if ($reference_type == "common name") {
	
		// get reference(s) for common name
	
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
	} else {
	
		// get scientific name
		
		$query = "SELECT `scientific_names`.`genus` , 
						 `scientific_names`.`species` , 
						 `scientific_names`.`infraspecies_marker` , 
						 `scientific_names`.`infraspecies` ,
						 `scientific_names`.`author` , 
						 `scientific_names`.`accepted_name_code` , 
						 `sp2000_statuses`.`sp2000_status` , 
						 `families`.`kingdom`
				  FROM `scientific_names` , `sp2000_statuses`, `families` 
				  WHERE `scientific_names`.`record_id` = '$record_id' 
					AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id`
					AND `scientific_names`.`family_id` = `families`.`record_id` " ;
		$result = mysql_query($query) or die("Error: MySQL query failed");
		if ( mysql_num_rows($result) == 0) {
			die ("<p>Error: missing or invalid record ID</p>") ;
		}
		$row = mysql_fetch_row($result);
		$this_genus               = $row[0] ;
		$this_species             = $row[1] ;
		$this_infraspecies_marker = $row[2] ;
		$this_infraspecies        = $row[3] ;
		$this_author 			  = $row[4] ;
		$accepted_name_code		  = $row[5] ;
		$status					  = $row[6] ;
		$this_kingdom			  = $row[7] ;
		$name = compileScientificName($this_genus,$this_species,$this_infraspecies_marker, 
		  $this_infraspecies,$this_author,$this_kingdom) ;
	
		// get reference(s) for scientific name
		
		$query = "SELECT DISTINCT `references`.`author` , 
					 		  `references`.`year` , 
					 		  `references`.`title` , 
					 		  `references`.`source`  
			  FROM `references` , `scientific_name_references` , `scientific_names`
			  WHERE `scientific_name_references`.`reference_id` = `references`.`record_id` 
				AND (`scientific_name_references`.`reference_type` = 'NomRef' 
				  OR `scientific_name_references`.`reference_type` = 'TaxAccRef') 
				AND `scientific_name_references`.`name_code` = `scientific_names`.`name_code`
				AND `scientific_name_references`.`name_code` LIKE BINARY `scientific_names`.`name_code`
				AND `scientific_names`.`record_id` = '$record_id'  
			  ORDER BY  `references`.`author`, 
			  			`references`.`year`, 
						`references`.`title`, 
						`references`.`source`" ;
	}
	
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$number_of_records = mysql_num_rows($result);
	$authors = Array() ;
	$years = Array() ;
	$titles = Array() ;
	$sources = Array() ;
	
	for ($i = 0; $i < $number_of_records; $i++) {
		$row = mysql_fetch_row($result);
		$author = $row[0] ;
		if ($author == "") {
			$author = "&#150;" ;
		}
		$year = $row[1] ;
		if ($year == "") {
			$year = "&#150;" ;
		}
		$title = $row[2] ;
		if ($title == "") {
			$title = "&#150;" ;
		}
		$source = $row[3] ;
		if ($source == "") {
			$source = "&#150;" ;
		}
		
		$authors[$i] = $author ;
		$years[$i] = $year ;
		$titles[$i] = $title ;
		$sources[$i] = $source ;
	}
	mysql_close($link) ;
	
	$output = "" ;
	if ($number_of_records > 0) {
		for ($i = 0 ; $i < $number_of_records; $i++) {
			$output .= "\n<table width='100%' cellpadding=1 cellspacing=0 border=0 bgcolor='#EDEBEB'>\n" ;
			$output .= "<tr>\n" ;
			$output .= "<td>\n" ;
			$output .= "\n<table width='100%' cellpadding=3 cellspacing=0 border=0>\n" ;
			
			$table_row_color = getTableRowColor($table_row_color) ;
			$output .= "<tr bgcolor='$table_row_color'>\n" ;
			$output .= "<td valign=top width='80px'>\n" ;
			$output .= "<p class='fieldheader'>Author</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "<td valign=top>\n" ;
			$output .= "<p class='fieldvalue'>" . $authors[$i] . "</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "</tr>\n" ;
			
			$table_row_color = getTableRowColor($table_row_color) ;
			$output .= "<tr bgcolor='$table_row_color'>\n" ;
			$output .= "<td valign=top width='80px'>\n" ;
			$output .= "<p class='fieldheader'>Year</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "<td valign=top>\n" ;
			$output .= "<p class='fieldvalue'>" . $years[$i] . "</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "</tr>\n" ;
			
			$table_row_color = getTableRowColor($table_row_color) ;
			$output .= "<tr bgcolor='$table_row_color'>\n" ;
			$output .= "<td valign=top width='80px'>\n" ;
			$output .= "<p class='fieldheader'>Title</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "<td valign=top>\n" ;
			$output .= "<p class='fieldvalue'>" . $titles[$i] . "</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "</tr>\n" ;
			
			$table_row_color = getTableRowColor($table_row_color) ;
			$output .= "<tr bgcolor='$table_row_color'>\n" ;
			$output .= "<td valign=top width='80px'>\n" ;
			$output .= "<p class='fieldheader'>Source</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "<td valign=top>\n" ;
			$output .= "<p class='fieldvalue'>" . $sources[$i] . "</p>\n" ; 
			$output .= "</td>\n" ;
			$output .= "</tr>\n" ;
			
			$output .= "</table>\n" ;
			$output .= "</td>\n" ;
			$output .= "</tr>\n" ;
			$output .= "</table>\n" ;
			if ($i < $number_of_records) {
				$output .= "<br>" ;
			}
		}
	}
	
	$header = "Literature reference" ;
	if ($number_of_records != 1) {
		$header .= "s" ;
	}
	
	$book_icon = "<img src='images/book.gif' border='0' width='15' height='14' hspace='2' title='Click here to show literature reference(s)'>" ;
?>

<title>Catalogue of Life : 2007 Annual Checklist : <?php
	echo strip_tags($header) ;
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
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
	echo $header ;
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
	
					  
<?php
	echo "<p style='margin-bottom:12px'>$number_of_records " . strtolower($header) . " found" .
	  (($reference_type == "scientific name") ? " for $name" : "") . ":</p>" ;
	echo $output ;
?>
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
				</tr>
			</table>
					</td>
				</tr>
			</table>
</div>
</body>
</html>
