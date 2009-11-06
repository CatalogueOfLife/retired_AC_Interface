<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Database details</title>
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
	
	// get ID of record to display
	if (isset($_REQUEST["database_name"])) {
		$database_name = $_REQUEST["database_name"] ;
	} else {
		$database_name = "" ;
	}
	
	// connect to database
	include "connect_to_database.php" ;
	
	$query = "SELECT `record_id` , 
					 `database_full_name` , 
					 `database_name` , 
					 `contact_person` , 
					 `taxa` , 
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
					 `custodian`
			  FROM `databases` 
			  WHERE `database_name` = '$database_name' " ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
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
	$species_count = $row[8] ;
	$infraspecies_count = $row[9] ;
	$species_synonyms_count = $row[10] ;
	$infraspecies_synonyms_count = $row[11] ;
	$common_names_count = $row[12] ;
	$names_count = $row[13] ;
	$web_site = $row[14] ;
	$custodian = $row[15] ;
	
	if ($taxa == "") {
		$taxa = "&#105" ;
	}
	if ($description == "") {
		$description = "&#105" ;
	}
	if ($version == "") {
		$version = "&#105" ;
	}
	if ($release_date == "") {
		$release_date = "&#105" ;
	}
	if ($custodian == "") {
		$custodian = "&#105" ;
	}
	
	$web_site = trim ($web_site) ;
	if ( substr($web_site,0,1) == "#" ) {
		$web_site = substr($web_site,1,strlen($web_site)-1) ;
	}
	if ( substr($web_site,-1) == "#" ) {
		$web_site = substr($web_site,0,strlen($web_site)-1) ;
	}
	if ( (substr($web_site,0,7) == "http://" || substr($web_site,0,8) == "https://") 
		&& strlen($web_site) >= 8 ) {
		$web_site = "<a href='$web_site' TARGET='_blank'>$web_site</a>" ;
	} elseif ($web_site == "") {
		$web_site = "&#150" ;
	}
	
	$image_path = "images/db_logos/" . str_replace(" ","_",$db_name). ".jpg" ;
	
	if (file_exists($image_path)) {
		$image_path = "<p style=\"margin-bottom:12px\"><img src=\"$image_path\" border=0></p>" ;
	} else {
		$image_path = "" ;
	}
?>
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
                                      <?php echo "$species_count accepted names, $species_synonyms_count synonyms"; ?>
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
                                      <?php echo "$infraspecies_count accepted names, $infraspecies_synonyms_count synonyms"; ?>
                                    </p>
                                  </td>
                                </tr>
                                <tr bgcolor='<?php $table_row_color=getTableRowColor($table_row_color) ; ?>'> 
                                  <td valign=top width="220px"> 
                                    <p class="fieldheader">Number of common names:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $common_names_count ?>
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
                                    <p class="fieldheader">Custodian:</p>
                                  </td>
                                  <td valign=top> 
                                    <p class="fieldvalue"> 
                                      <?php echo $custodian ?>
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
                        <br>
						<p align=center>
<?php
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referring_page = $_SERVER['HTTP_REFERER'] ;
	} else {
		$referring_page = "" ;
	}
	if (strpos($referring_page,"info_source_dbs.php") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to list of databases</a>" ;
	} else if (strpos($referring_page,"show_species_details.php") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to species details</a>" ;
	} else if (strpos($referring_page,"show_common_name_details.php") > 0) {
		echo "<a href='JavaScript:history.back()'>Back to common name details</a>" ;
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
      </table>
    </td>
  </tr>
</table>
</div>
</body>
</html>