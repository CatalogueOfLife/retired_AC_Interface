<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Source databases</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" onLoad="moveMenu();" onScroll=" moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 17px; margin-right:17px">
<form name="show_database" method="post" action="show_database_details.php">
  <input type="hidden" name="database_name">
</form>
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
	<table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100%">
        <tr> 
          <td> 
            <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
              <tr> 
                <td> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="10">
                    <tr> 
                      <td> 
                        <p class="formheader" align="center">Source databases</p>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                            <tr> 
                              <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                            </tr>
                          </table>
                        </td>
                    </tr>
                  </table>
                  <table border="0" cellspacing="10" cellpadding="0" >
                    <tr> 
                      <td> 
                          <p style="margin-bottom:20px">Catalogue of Life: 2005 
                            Annual Checklist is compiled from the following databases:</p>
<?php
	$sort_by_column = 1 ;
	if (isset($_REQUEST["sort_by_column"])) {
		$sort_by_column = $_REQUEST["sort_by_column"] ;
	}
	$query = "SELECT  `record_id` , 
					  `database_name` , 
					  `database_full_name` , 
					  `taxa` ,
					  (`accepted_species_names` + `accepted_infraspecies_names`) as accepted_names
			  FROM     `databases` " ;
	if ($sort_by_column == 1) {
		$query .= " ORDER BY lower(`databases`.`database_full_name`)" ;
	} else if ($sort_by_column == 2) {
		$query .= " ORDER BY lower(`taxa`)" ;
	} else if ($sort_by_column == 3) {
		$query .= " ORDER BY accepted_names DESC " ;
	}
			  
	include "connect_to_database.php" ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	$number_of_records = mysql_num_rows($result);
	if ($number_of_records == 0) {
		echo "<p>Error: no databases found</p>" ;
	} else {
		$column_1 = styleColumnHeader("Source database",1,$sort_by_column) ;
		$column_2 = styleColumnHeader("Taxonomic coverage",2,$sort_by_column) ;
		$column_3 = styleColumnHeader("Accepted scientific names",3,$sort_by_column) ;
		echo "<table border=0 cellspacing=0 cellpadding=1 width='100%' bgcolor='#EDEBEB'>\n<tr>\n<td>\n" ;
		echo "<table border=0 cellspacing=0 cellpadding=0 width='100%' bgcolor='#FAFCFE'>\n<tr>\n<td>\n" ;
		echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n" ;
		echo "<tr bgcolor='#FAFCFE'>\n" ;
		echo "<td valign=middle align=left><span class='tableheader'>$column_1</span></td>" ;
		echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
		echo "<td valign=middle></td>" ;
		echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
		echo "<td valign=middle><span class='tableheader'>$column_2</span></td>" ;
		echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
		echo "<td valign=middle align=right width=130><span class='tableheader'>$column_3</span></td>" ;
		echo "<td><img src='images/blank.gif' width=25 height=1 border=0></td>" ;
		echo "<td><img src='images/blank.gif' width=80 height=1 border=0></td>" ;
		echo "</tr>\n" ;
	
		$row_color = "#EAF2F7" ;
		for ($i = 1; $i <= $number_of_records; $i++) {
			$row = mysql_fetch_row($result);
			$db_id = $row[0] ;
			$db_shortname = $row[1] ;
			$db_fullname = $row[2] ;
			$taxa = $row[3] ;
			$accepted_names = $row[4] ;
			
			if ($db_shortname == $db_fullname) {
				$db_name = $db_fullname ;
			} else {
				$db_name = "$db_fullname [$db_shortname]" ;
			
			}
			
			$image_path = "images/db_logos/" . str_replace(" ","_",$db_shortname). ".gif" ;
			if (file_exists($image_path)) {
				$image_path = "<a href='JavaScript:showDatabaseDetails(\"$db_shortname\")'><img src=\"$image_path\" border=0 title='Click here for more information about $db_name'></a>" ;
			} else {
				$image_path = "" ;
			}
			
			echo "<tr bgcolor='$row_color' id='$db_name'>\n" ;
			echo "<td colspan=9><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
			echo "</tr>\n" ;
			echo "<tr bgcolor='$row_color'>\n" ;
			echo "<td valign=top><p>" . 
				"<span class='fieldheader'><a href='JavaScript:showDatabaseDetails(\"$db_shortname\")'>$db_name</a></span>" ;
			echo "</p></td>\n" ;
			echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
			echo "<td valign=top><p><span class='fieldheader'>$image_path</span>" ;
			echo "</p></td>\n" ;
			echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
			echo "<td valign=top><p>$taxa</p></td>" ;
			echo "<td><img src='images/blank.gif' width=15 height=1 border=0></td>" ;
			echo "<td valign=top align=right><p>$accepted_names</p></td>" ;
			echo "<td><img src='images/blank.gif' width=25 height=1 border=0></td>" ;
			echo "<td valign=top align=right><p class='showdetails'><a href='JavaScript:showDatabaseDetails(\"$db_shortname\")'>Show details</a></p></td>\n" ;
			echo "</tr>\n" ;
			echo "<tr bgcolor='$row_color'>\n" ;
			echo "<td colspan=9><img src='images/blank.gif' width=1 height=2 border=0></td>" ;
			echo "</tr>\n" ;
	
			if ($row_color == "#EAF2F7") {
				$row_color = "#FAFCFE" ;
			} else {
				$row_color = "#EAF2F7" ;
			}
		}
		echo "</table>" ;
		echo "</tr></table>" ;
		echo "</tr></table>" ;
	}
	mysql_free_result($result) ;
	mysql_close($link) ;
	
	function styleColumnHeader ($column_name, $current_column, $selected_column) {
		if ($current_column == $selected_column) {
			return "<u>$column_name</u>" ;
		} else {
			return "<a href='JavaScript:sortByColumn($current_column)'>$column_name</a>" ;
		}
	}
?>

                        <p><img src="images/blank.gif" width="505px" height="1" border="0"></p>
                        </td>
                    </tr>
                  </table>
                  
                  <table width="100%" border="0" cellspacing="0" cellpadding="10">
                    <tr> 
                      <td> 
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                          <tr> 
                            <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                          </tr>
                        </table>
                        <?php
	include "info_arrow_buttons.php" ;
?>
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
<form name="sort_by_column" method="post" action="info_source_dbs.php">
  <input type="hidden" name="sort_by_column">
</form>
</div>
</body>
</html>
