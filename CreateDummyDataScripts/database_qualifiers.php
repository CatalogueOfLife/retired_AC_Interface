<?php
	// Error at Inovirus enterobacteria phage x-2
	
	// Stopped at Gemylus wainiloka


    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    for ($i = 0; $i < ob_get_level(); $i++) {
        ob_end_flush();
    }
    ob_implicit_flush(1);
    set_time_limit(0);

	$connection = mysql_connect('localhost', 'root', 'root');
	$db = mysql_select_db('base_scheme_2011_19');
	
	mysql_query('TRUNCATE TABLE `_import_source_database_qualifiers`');
	
	
	$coverage = array('Worldwide', 'Regional (Netherlands)', 'Regional (Africa)', 'Regional');
	
	$query = 'SELECT `short_name` FROM `_source_database_details`';
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result)) {
		$insert = 'INSERT INTO `_import_source_database_qualifiers` 
		(`source_database_name`, `coverage`, `completeness`, `confidence`) 
		VALUES ("'.
		$row[0].'", "'.
		array_rand(array_flip($coverage)).'", '.
		((rand(50, 100)/100) * 100).', '.
		(round((rand(20, 100)/100) * 5)).')';
		mysql_query($insert) or die(mysql_error());
	}
?>