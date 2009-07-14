<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2008 Annual Checklist : Export search results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<?php
	$kingdom = "" ;
	$phylum = "" ;
	$tax_class = "" ;
	$order = "" ;
	$superfamily = "" ;
	$family = "" ;
	$genus = "" ;
	$species = "" ;
	$infraspecies = "" ;
	$common_name = "" ;
	$area = "" ;
	$search_string = "" ;
	$match_whole_words = "off" ;
	$search_type = "" ;
	$sort_by_column = "" ;
	if (isset($_REQUEST["kingdom"])) {
		$kingdom = urldecode($_REQUEST["kingdom"]) ;
	}
	if (isset($_REQUEST["phylum"])) {
		$phylum = urldecode($_REQUEST["phylum"]) ;
	}
	if (isset($_REQUEST["tax_class"])) {
		$tax_class = urldecode($_REQUEST["tax_class"]) ;
	}
	if (isset($_REQUEST["order"])) {
		$order = urldecode($_REQUEST["order"]) ;
	}
	if (isset($_REQUEST["superfamily"])) {
		$superfamily = urldecode($_REQUEST["superfamily"]) ;
	}
	if (isset($_REQUEST["family"])) {
		$family = urldecode($_REQUEST["family"]) ;
	}
	if (isset($_REQUEST["genus"])) {
		$genus = urldecode($_REQUEST["genus"]) ;
	}
	if (isset($_REQUEST["species"])) {
		$species = $_REQUEST["species"] ;
	}
	if (isset($_REQUEST["infraspecies"])) {
		$infraspecies = urldecode($_REQUEST["infraspecies"]) ;
	}
	if (isset($_REQUEST["common_name"])) {
		$common_name = urldecode($_REQUEST["common_name"]) ;
	}
	if (isset($_REQUEST["area"])) {
		$area = urldecode($_REQUEST["area"]) ;
	}
	if (isset($_REQUEST["search_string"])) {
		$search_string = urldecode($_REQUEST["search_string"]) ;
	}
	if (isset($_REQUEST["match_whole_words"])) {
		$match_whole_words = urldecode($_REQUEST["match_whole_words"]) ;
	}
	if (isset($_REQUEST["sort_by_column"])) {
		$sort_by_column = urldecode($_REQUEST["sort_by_column"]) ;
	}
	if (isset($_REQUEST["search_type"])) {
		$search_type = $_REQUEST["search_type"] ;
	}
?>
</head>

<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "includes/config.php" ;
	if ($online_or_offline_version == "offline") {
		include "cd_rom_version_icon.php" ;
	}
?>
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> 
</div><div style="margin-left: 15px; margin-right:15px;"> <table border="0" cellspacing="0" cellpadding="0"> 
<tr> <td valign=top> <?php
	require_once "menu.php" ;
?> </td><td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> 
</td><td valign=top> <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100%"> 
<tr> <td> <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE"> 
<tr> <td> <table width="100%" border="0" cellspacing="0" cellpadding="10"> 
<tr> <td> <p class="formheader" align="center">Export search results</p><table width="100%" border="0" cellspacing="0" cellpadding="0" height="0"> 
<tr> <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td></tr> 
</table></td></tr> </table><table border="0" cellspacing="10" cellpadding="0"> 
<tr> <td> <p>Click the button below to export your search results to a spreadsheet 
file. Most browsers will offer to either open this file in Microsoft Excel, 
or save the file to your hard disk.</p><p style="margin-bottom:20px">The 
exported data is tab-delimited (columns are separated by tabs, rows are 
separated by returns) so the file can be opened in most spreadsheet, database, 
word processing and text editing programs.</p><table border="0" cellspacing="0" cellpadding="0"> 
<tr> <td> <table border="0" cellspacing="0" cellpadding="1" bgcolor=black> 
<tr> <td> <table border="0" cellspacing="0" cellpadding="10" bgcolor=white> 
<tr> <td> <p><span class="formheader">Copyright notice</span></p>
<p>All 
exported species data is &copy; 2009 Species 2000 on behalf of the Catalogue 
of Life partners. Use of the species names for publications and databases 
is encouraged, with appropriate acknowledgement of 1) the Species 2000 &amp; 
ITIS Catalogue of Life, 2007 Annual Checklist, (2) the member database(s) 
concerned, and (3) the experts responsible for individual records. Sale 
or multiple reproduction of this compilation or any of the species datasets 
contained within requires written permission from Species 2000 and/or ITIS.</p></td></tr> 
</table></td></tr> </table></td></tr> </table><br> <form name="export_results" method="get" action="export_search_results2.php"> 
<input type="hidden" name="kingdom" value="<?php echo urlencode($kingdom) ?>"> 
<input type="hidden" name="phylum" value="<?php echo urlencode($phylum) ?>"> 
<input type="hidden" name="tax_class" value="<?php echo urlencode($tax_class) ?>"> 
<input type="hidden" name="order" value="<?php echo urlencode($order) ?>"> 
<input type="hidden" name="superfamily" value="<?php echo urlencode($superfamily) ?>"> 
<input type="hidden" name="family" value="<?php echo urlencode($family) ?>"> 
<input type="hidden" name="genus" value="<?php echo urlencode($genus) ?>"> 
<input type="hidden" name="species" value="<?php echo urlencode($species) ?>"> 
<input type="hidden" name="infraspecies" value="<?php echo urlencode($infraspecies) ?>"> 
<input type="hidden" name="common_name" value="<?php echo urlencode($common_name) ?>"> 
<input type="hidden" name="area" value="<?php echo urlencode($area) ?>"> 
<input type="hidden" name="search_string" value="<?php echo urlencode($search_string) ?>"> 
<input type="hidden" name="match_whole_words" value="<?php echo $match_whole_words ?>"> 
<input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>"> 
<input type="hidden" name="search_type" value="<?php echo urlencode($search_type) ?>"> 
<input type="submit" name="Submit" value="Export to file" class="formbutton"> 
</form><p style="margin-top:36px ; margin-bottom:12px"><a href="JavaScript:history.back();"><span style="font-size:9">&lt;&lt;</span> 
Back to search results</a></p></td></tr> </table></td></tr> </table></td></table><p><img src="images/blank.gif" width="530px" height="1" border="0"></p></td></tr> 
</table></div>
</body>
</html>
