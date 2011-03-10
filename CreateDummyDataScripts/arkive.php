<?php
	// Stopped at Tragulus javanicus


    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    for ($i = 0; $i < ob_get_level(); $i++) {
        ob_end_flush();
    }
    ob_implicit_flush(1);
    set_time_limit(0);

	$connection = mysql_connect('localhost', 'root', 'root');
	$db = mysql_select_db('base_scheme_2011_19');
	
	//mysql_query('TRUNCATE TABLE `_image_resource`');

	$base_url = 'http://www.arkive.org/api/ED41047V5D/portlet/latin/';
	$added = 0;
	
    //$query = 'SELECT `taxon_id`, `genus_name`, `species_name`, `infraspecies_name` FROM `_species_details`';
    $query = 'SELECT `taxon_id`, `genus_name`, `species_name`, `infraspecies_name` FROM `_species_details` WHERE `taxon_id` > 6903812';
    $result = mysql_query($query) or die(mysql_error());
	$total = mysql_num_rows($result) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {

		$species = trim($row[1].' '.$row[2].' '.$row[3]);
		$url = $base_url . urlencode($species) . '/1?media=images';
		
		// Skip taxa with odd names containing species chars, as this messes up the webservice
		if (!taxonNameOK($species)) {
			continue;
		}

		$data = json_decode(file_get_contents($url));

		echo '<i>'.$species.'</i>';

		if ($data->error == '') {
			$image_link = $data->results[0];
			$href = getAttribute('href', $image_link);
			$src = getAttribute('img src', $image_link);
			$size = getimagesize($src);
			$width = $size[0];
			$height = $size[1];
			$caption = getAttribute('alt', $image_link);
			
			$insert = 'INSERT INTO `_import_image_resource` (`taxon_id`, `src`, `href`, `width`, `height`, `source`, `caption`) VALUES ("' .
			$row[0] . '", "' .
			mysql_real_escape_string($src) . '", "' .
			mysql_real_escape_string($href) . '", ' .
			$width . ', ' .
			$height . ', "ARKive", "' .
			mysql_real_escape_string($caption) . '")';
			
			//echo $insert;
			mysql_query($insert) or die(mysql_error());
		
			$added++;
			echo ' -- <span style="color: red; font-weight: bold;">picture added</span>';
		}
		echo '<br>';
	}
	
	echo "<br>Ready! Added $added pictures to a total of $total (infra)species.";
	
	function getAttribute($attrib, $tag){
		//get attribute from html tag
		$re = '/'.$attrib.'=["\']?([^"\']*)["\']/is';
		preg_match($re, $tag, $match);
		if ($match) {
			return urldecode($match[1]);
		} else {
			return false;
		}
	}
	
	function taxonNameOK($name) {
		$chars_to_skip = array('-', '+', '/', '"');
		foreach ($chars_to_skip as $char) {
			if (strstr($name ,$char) !== false) {
				return false;
			}
		}
		return true;
	}
?>