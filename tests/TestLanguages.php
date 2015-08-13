<?php
/**
 * Script used to test language files for missing strings and non-translated strings
 *
 * @author Wouter Addink
 */
echo '<pre>';
$y = parse_ini_file('application/data/languages/lang.en.ini');
$z = parse_ini_file('application/data/languages/lang.vi.ini');
$yy = array_keys($y);
$zz = array_keys($z);
//check if strings are missing, compared with english template
//print_r(array_diff($yy,$zz));
//print_r(array_diff($zz,$yy));

//check if the file can be parsed and shown in array
//print_r($z);

//check for values that are still english
print_r(array_intersect($y,$z));
echo '</pre>';