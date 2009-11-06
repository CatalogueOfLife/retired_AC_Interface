
<html>
<head>
</head>
<body>
<?php
	function compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$kingdom,$author) {
		if ($kingdom == "Viruses") {
			$scientific_name = "<i>" . $species . "</i>" ;
		} else {
			$scientific_name = "<i>$genus $species</i>" ;
			if ($infraspecies != "") {
				if ($infraspecies_marker != "") {
					$scientific_name .= " $infraspecies_marker" ;
				}
				$scientific_name .= " <i>$infraspecies</i>"  ;
			}
			if ($author != "") {
				$scientific_name .= " $author"  ;
			}
		}
		return $scientific_name ;
	}

	include "connect_to_database.php" ;
	$query = "SELECT  `scientific_names`.`genus` , 
					   `scientific_names`.`species` , 
					   `scientific_names`.`infraspecies_marker` ,
					   `scientific_names`.`infraspecies` , 
					   `scientific_names`.`author` , 
					   `families`.`kingdom`
			  FROM `scientific_names`,`families` 
			  WHERE `scientific_names`.`family_id` = `families`.`record_id`
			    AND `scientific_names`.`genus` LIKE 'A%'
			ORDER BY `scientific_names`.`genus`, `scientific_names`.`species`,`scientific_names`.`infraspecies`
			LIMIT 0,1000" ;
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	
	$count = mysql_num_rows($result);
	for ($i = 0; $i < $count; $i++){
		$row = mysql_fetch_row($result) ;
		$genus = $row[0] ;
		$species = $row[1] ;
		$infraspecies_marker = $row[2] ;
		$infraspecies = $row[3] ;
		$author = $row[4] ;
		$kingdom = $row[5] ;
		echo "<p>" . compileScientificName($genus,$species,$infraspecies_marker,$infraspecies,$kingdom,$author) . "</p>\n" ;
	}
	mysql_free_result($result) ;
	mysql_close($link) ;
?>
</body>
</html>
