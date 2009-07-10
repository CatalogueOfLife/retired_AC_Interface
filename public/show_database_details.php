<?php
	session_start(); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2009 Annual Checklist : Database details</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
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
	
	function addCommas($value) {
		$length = strlen($value) ;
		$counter = 0 ;
		$new_value = "" ;
		for ($i = 0; $i < $length; $i++) {
			$counter ++ ;
			if ($counter == 4) {
				$counter = 1 ;
				$new_value = "," .$new_value  ;
			}
			$new_value = substr($value,$length-1-$i,1) . $new_value ;
		}
		return $new_value ;
	}
	
	// get ID of record to display
	if (isset($_REQUEST["database_name"])) {
		$database_name = trim(stripslashes(urldecode($_REQUEST["database_name"]))) ;
	} else {
		$database_name = "" ;
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
	include "connect_to_database.php" ;
	
	$query = "SELECT `record_id` , 
					 `database_full_name` , 
					 `database_name` , 
					 `contact_person` , 
					 `taxonomic_coverage` , 
					 `abstract` , 
					 `version` ,  
					 DATE_FORMAT(  `release_date`  , '%M %D, %Y' ) ,
					 `accepted_species_names` ,
					 `accepted_infraspecies_names` ,
					 `species_synonyms` ,
					 `infraspecies_synonyms` ,
					 `common_names` ,
					 `total_names` ,
					 `web_site`, 
					 `authors_editors`,
					 `organization`
			  FROM `databases` 
			  WHERE `database_name` = '$database_name' " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result);
	mysql_free_result($result) ;
	$db_id = $row[0] ;
	$db_fullname = $row[1] ;
	$db_name = $row[2] ;
	$contact = $row[3] ;
	$taxa = $row[4] ;
	$description = $row[5] ;
	$version = $row[6] ;
	$release_date = $row[7] ;
	$species_count = addCommas($row[8]) ;
	$infraspecies_count = addCommas($row[9]) ;
	$species_synonyms_count = addCommas($row[10]) ;
	$infraspecies_synonyms_count = addCommas($row[11]) ;
	$this_common_names_count = addCommas($row[12]) ;
	$names_count = addCommas($row[13]) ;
	$web_site = $row[14] ;
	$authors_editors = $row[15] ;
	$organization = $row[16] ;
	
	if ($taxa == "") {
		$taxa = "&#150" ;
	}
	if ($description == "") {
		$description = "&#150" ;
	}
	if ($version == "") {
		$version = "&#150" ;
	}
	if ($release_date == "") {
		$release_date = "&#150" ;
	}
	if ($authors_editors == "") {
		$authors_editors = "&#150" ;
	}
	if ($organization == "") {
		$organization = "&#150" ;
	}
	
	$web_site = trim ($web_site) ;
	if ( substr($web_site,0,1) == "#" ) {
		$web_site = substr($web_site,1,strlen($web_site)-1) ;
	}
	if (strpos ($web_site,"#") !== FALSE) {
		$web_site = substr($web_site,0,strpos ($web_site,"#")) ;
	}
	if ( (substr($web_site,0,7) == "http://" || substr($web_site,0,8) == "https://") 
		&& strlen($web_site) >= 8 ) {
		$web_site = "<a href='$web_site' TARGET='_blank'>$web_site</a>" ;
	} elseif ($web_site == "") {
		$web_site = "&#150" ;
	}
	
	$image_path = "images/db_logos/" . str_replace(" ","_",$db_name). ".jpg" ;
	
	if (file_exists($image_path)) {
		$image_path = "<p style=\"margin-bottom:14px\"><img src=\"$image_path\" border=0></p>" ;
	} else {
		$image_path = "" ;
	}
?>
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
                        <p class="formheader" align="center">Database details</p>
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
                        <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#EDEBEB">
                          <tr> 
                            <td> 
                              <table border="0" cellspacing="0" cellpadding="3" width="100%">
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Full name:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $db_fullname ; ?>
                                    </p>
                                  </td>
                                </tr>
							    <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Short name:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $db_name ; ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Version:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $version ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Release date:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $release_date ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Authors/editors:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $authors_editors ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Taxonomic coverage:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $taxa ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Number of species names:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo "$species_count accepted names; $species_synonyms_count synonyms"; ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Number of infraspecies 
                                      names:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo "$infraspecies_count accepted names; $infraspecies_synonyms_count synonyms"; ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Number of common names:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $this_common_names_count ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Total number of names:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $names_count ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                      <p class="fieldheader">Abstract:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $description ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Organization:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $organization ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Web site:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $web_site ?>
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
</div>
</body>
</html>