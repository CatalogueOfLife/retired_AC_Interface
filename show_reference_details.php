<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
	$reference_id = "" ;
	$name = "" ;
	$genus = "" ;
	$species = "" ;
	$infraspecies_marker = "" ;
	$infraspecies = "" ;
	$author = "" ;
	$status = "" ;
	$output = "" ;
	
	if (isset($_REQUEST["reference_id"])) {
		$reference_id = $_REQUEST["reference_id"] ;
	}
	if (isset($_REQUEST["name"])) {
		$name = urldecode( $_REQUEST["name"] ) ;
	}
	if (isset($_REQUEST["genus"])) {
		$genus = urldecode( $_REQUEST["genus"] ) ;
	}
	if (isset($_REQUEST["species"])) {
		$species = urldecode( $_REQUEST["species"] ) ;
	}
	if (isset($_REQUEST["infraspecies_marker"])) {
		$infraspecies_marker = urldecode( $_REQUEST["infraspecies_marker"] ) ;
	}
	if (isset($_REQUEST["infraspecies"])) {
		$infraspecies = urldecode( $_REQUEST["infraspecies"] ) ;
	}
	if (isset($_REQUEST["author"])) {
		$author = urldecode( $_REQUEST["author"] ) ;
	}
	if (isset($_REQUEST["status"])) {
		$status = urldecode( $_REQUEST["status"] ) ;
	}
	
	$table_row_color = "" ;
	function getTableRowColor($old_color) {
		if ( $old_color != "#FAFCFE" ) {
			$new_color = "#FAFCFE" ;
		} else {
			$new_color = "#EAF2F7" ;
		}
		return $new_color ;
	}
	
	include "connect_to_database.php" ;
	
	if ($reference_id != "") {
	
		// get reference(s) for common name
	
		$query = "SELECT DISTINCT `references`.`author` , 
					 		  `references`.`year` , 
					 		  `references`.`title` , 
					 		  `references`.`source`  
			  FROM `references` 
			  WHERE `references`.`record_id` = '$reference_id'
			  ORDER BY  `references`.`author`, 
			  			`references`.`year`, 
						`references`.`title`, 
						`references`.`source`" ;
	
	} else if ($genus != "") {
	
		// get reference(s) for scientific name
	
		$query = "SELECT DISTINCT `references`.`author` , 
					 		  `references`.`year` , 
					 		  `references`.`title` , 
					 		  `references`.`source`  
			  FROM `references` , `scientific_name_references`, `scientific_names`, `sp2000_statuses`
			  WHERE `scientific_name_references`.`reference_id` = `references`.`record_id` 
				AND `scientific_names`.`sp2000_status_id` = `sp2000_statuses`.`record_id` 
			    AND `scientific_names`.`name_code` = `scientific_name_references`.`name_code`
				AND `scientific_names`.`genus` = '" . addslashes($genus) . "' 
				AND `scientific_names`.`species` = '" . addslashes($species) . "' 
				AND `scientific_names`.`infraspecies_marker` = '" . addslashes($infraspecies_marker) . "' 
				AND `scientific_names`.`infraspecies` = '" . addslashes($infraspecies) . "' 
				AND `scientific_names`.`author` = '" . addslashes($author) . "' 
				AND `sp2000_statuses`.`sp2000_status` = '" . addslashes($status) . "' 
				AND (`scientific_name_references`.`reference_type` = 'AuthorRef' 
				  OR `scientific_name_references`.`reference_type` = 'StatusRef') 
			  ORDER BY  `references`.`author`, 
			  			`references`.`year`, 
						`references`.`title`, 
						`references`.`source`" ;
						
	} else {
		$query = "" ;
	}
	
	$number_of_records = 0 ;
	if ($query != "") {
	
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
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
	}
	
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

<title>Catalogue of Life : 2005 Annual Checklist : <?php
	echo strip_tags($header) ;
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="moveMenu();" onScroll="moveMenu();">
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
	echo "<p style='margin-bottom:12px'>$number_of_records " . strtolower($header) . " found for $name:</p>" ;
	echo $output ;
?>
						
                        <br>
						<p align=center>
<?php
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referring_page = $_SERVER['HTTP_REFERER'] ;
	} else {
		$referring_page = "" ;
	}
	if (strpos($referring_page,"show_species_details.php") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to species details</a>" ;
	} else {
		echo "<a href='JavaScript:history.back()'>Back to last page</a>" ;
	}
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
