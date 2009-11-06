
<html>
<head>
</head>
<body>
<?php
	function compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies, 
	  $this_kingdom,$this_author) {
		if ($this_kingdom == "Viruses" || $this_kingdom = "Subviral agents") {
			$scientific_name = $this_species ;
		} else {
			$scientific_name = "$this_genus $this_species" ;
			if ($this_infraspecies != "") {
				if ($this_infraspecies_marker != "") {
					$scientific_name .= " $this_infraspecies_marker" ;
				}
				$scientific_name .= " $this_infraspecies"  ;
			}
			if ($this_author != "") {
				$scientific_name .= " $this_author"  ;
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
	$result = mysql_query($query) or die("Error: MySQL query failed");
	
	$count = mysql_num_rows($result);
	for ($i = 0; $i < $count; $i++){
		$row = mysql_fetch_row($result) ;
		$this_genus = $row[0] ;
		$this_species = $row[1] ;
		$this_infraspecies_marker = $row[2] ;
		$this_infraspecies = $row[3] ;
		$this_author = $row[4] ;
		$this_kingdom = $row[5] ;
		echo "<p>" . compileScientificName($this_genus,$this_species,$this_infraspecies_marker,$this_infraspecies,$this_kingdom,$this_author) . "</p>\n" ;
	}
	mysql_free_result($result) ;
	mysql_close($link) ;
?> 
</body>
</html>
