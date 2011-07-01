<?php 

//always flush
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    for ($i = 0; $i < ob_get_level(); $i++) {
        ob_end_flush();
    }
    ob_implicit_flush(1);
    set_time_limit(0);

function estimateThis($interger) {
	if($interger == 0) {
		return 0;
	} elseif ($interger == 1) {
		return 1;
	}
	//get a result that is between 75% and 300% but never is always less then 10 million.
	$percentage = rand(75,300);
	$estimation = ceil(($interger / 100) * $percentage);
	return $estimation;
}

$connect = mysql_connect('localhost', 'root', 'root', true);
if(!$connect) {
    die('mysql connect has failed you...');
}
mysql_query("SET NAMES 'UTF8'");

$db_selected = mysql_select_db('bs_v19', $connect);
if(!$db_selected) {
	die('mysql select db has failed you...');
}

$sqlCount = 'SELECT count(*) AS total FROM `_taxon_tree`'; 
echo 'Select total number of records.';
$result = mysql_query($sqlCount,$connect);
$row = mysql_fetch_array($result,MYSQL_ASSOC);
$totalRecords = $row['total'];
echo ' ' . $totalRecords . ' records found.<br />';
$sql = 'SELECT * FROM `_taxon_tree`';
echo 'Select all species from the tree.<br />';

$counter = 0;
$i = 0;
$j = 0;
$taxa = array();
$result = mysql_query($sql,$connect);
echo 'Update species count in _taxon_tree and insert GSD_id per branch in _taxon_tree.<br />';
while($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
	if(strstr($row['name'],' ')) {
		$sqlGetGSD = 'SELECT `source_database_id` FROM `_search_all` WHERE `name` = "' . $row['name'] . '" AND `source_database_id` != 0 GROUP BY `source_database_id` ;';
		$resultGetGSD = mysql_query($sqlGetGSD,$connect);
	} else {
		$sqlGetGSD = 'SELECT `source_database_id` FROM `_search_scientific` WHERE `' . $row['rank'] .'` = "' . $row['name'] . '" AND `source_database_id` != 0 GROUP BY `source_database_id` ;';
		$resultGetGSD = mysql_query($sqlGetGSD,$connect);
	}
	if($row['rank'] == 'species') {	
		$sqlCountSpecies = 'SELECT 1 AS counter FROM `_taxon_tree` WHERE `taxon_id` = "' . $row['taxon_id'] . '";';
		$resultCountSpecies = mysql_query($sqlCountSpecies,$connect);
	} else {
		$sqlCountSpecies = 'SELECT COUNT(*) AS counter FROM `_search_scientific` WHERE `' . $row['rank'] .'` = "' . $row['name'] . '" AND `species` != "" AND `infraspecies` = "" AND status NOT IN ;';
		$resultCountSpecies = mysql_query($sqlCountSpecies,$connect);
	}
	if($resultCountSpecies !== false) {
		while($rowCountSpecies = mysql_fetch_array($resultCountSpecies,MYSQL_ASSOC)) {
			$sqlUpdateCountSpecies = 'UPDATE _taxon_tree SET `total_species_estimation` = "' . estimateThis($rowCountSpecies['counter']) . '", `total_species` = "' . $rowCountSpecies['counter'] . '" WHERE `taxon_id` = "' . $row['taxon_id'] . '";';
			//mysql_query($sqlUpdateCountSpecies,$connect);
		}
	}
	$sqlInsertGSDperTaxonTreeId = 'INSERT INTO `_source_database_to_taxon_tree_branch` (`source_database_id`, `taxon_tree_id`) VALUES';
	if($resultGetGSD !== false) {
		$firstValues = true;
		while($rowGetGSD = mysql_fetch_array($resultGetGSD,MYSQL_ASSOC)) {
			if($firstValues == true) {
				$firstValues = false;
			} else {
				$sqlInsertGSDperTaxonTreeId .= ',';
			}
			$sqlInsertGSDperTaxonTreeId .= '("'.$rowGetGSD['source_database_id'].'","'.$row['taxon_id'].'")';
		}
		mysql_query($sqlInsertGSDperTaxonTreeId,$connect);
	}
	$i++;
	$counter++;
	if($i >= 250) {
		$j++;
		$i = 0;
		echo '.';flush();
		if($j >= 175) {
			$j = 0;
			echo ($counter) . ' of ' . $totalRecords . '<br />';
		}
	}
}
echo 'done!'
?>