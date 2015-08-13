<?php 

//always flush
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    for ($i = 0; $i < ob_get_level(); $i++) {
        ob_end_flush();
    }
    ob_implicit_flush(1);
    set_time_limit(0);

$connect = mysql_connect('localhost', 'root', 'root', true);
if(!$connect) {
    die('mysql connect has failed you...');
}
mysql_query("SET NAMES 'UTF8'");

$db_selected = mysql_select_db('base_scheme_2011_19', $connect);
if(!$db_selected) {
	die('mysql select db has failed you...');
}

$result = mysql_query('SELECT * FROM __import_species_estimate') or die(mysql_error());
while ($row = mysql_fetch_array($result)) {
    $update = 'UPDATE _taxon_tree SET 
               total_species_estimation = "'.mysql_real_escape_string($row[4]).'", 
               estimate_source = "'.mysql_real_escape_string(trim($row[3])).'" 
               WHERE name = "'.mysql_real_escape_string($row[1]).'" AND 
               rank = "'.mysql_real_escape_string($row[0]).'"';
    mysql_query($update) or die(mysql_error());
    echo "$update<br>";
}


echo 'done!'
?>