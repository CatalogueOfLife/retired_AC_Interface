<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Export search results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<?php
	$search_kingdom = "" ;
	$search_phylum = "" ;
	$search_class = "" ;
	$search_order = "" ;
	$search_family = "" ;
	$search_genus = "" ;
	$search_species = "" ;
	$search_infraspecies = "" ;
	$search_common_name = "" ;
	$search_distribution = "" ;
	$search_simple = "" ;
	$search_mode = "" ;
	$search_page = "" ;
	$sort_by_column = "" ;
	if (isset($_REQUEST["search_kingdom"])) {
		$search_kingdom = urldecode($_REQUEST["search_kingdom"]) ;
	}
	if (isset($_REQUEST["search_phylum"])) {
		$search_phylum = urldecode($_REQUEST["search_phylum"]) ;
	}
	if (isset($_REQUEST["search_class"])) {
		$search_class = urldecode($_REQUEST["search_class"]) ;
	}
	if (isset($_REQUEST["search_order"])) {
		$search_order = urldecode($_REQUEST["search_order"]) ;
	}
	if (isset($_REQUEST["search_family"])) {
		$search_family = urldecode($_REQUEST["search_family"]) ;
	}
	if (isset($_REQUEST["search_genus"])) {
		$search_genus = urldecode($_REQUEST["search_genus"]) ;
	}
	if (isset($_REQUEST["search_species"])) {
		$search_species = $_REQUEST["search_species"] ;
	}
	if (isset($_REQUEST["search_infraspecies"])) {
		$search_infraspecies = urldecode($_REQUEST["search_infraspecies"]) ;
	}
	if (isset($_REQUEST["search_common_name"])) {
		$search_common_name = urldecode($_REQUEST["search_common_name"]) ;
	}
	if (isset($_REQUEST["search_distribution"])) {
		$search_distribution = urldecode($_REQUEST["search_distribution"]) ;
	}
	if (isset($_REQUEST["search_simple"])) {
		$search_simple = urldecode($_REQUEST["search_simple"]) ;
	}
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = urldecode($_REQUEST["search_mode"]) ;
	}
	if (isset($_REQUEST["search_page"])) {
		$search_page = urldecode($_REQUEST["search_page"]) ;
	}
	if (isset($_REQUEST["sort_by_column"])) {
		$sort_by_column = urldecode($_REQUEST["sort_by_column"]) ;
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
	<table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100%">
        <tr> 
          <td> 
            <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
              <tr> 
                <td> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="10">
                    <tr> 
                      <td> 
                        <p class="formheader" align="center">Export search results</p>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                            <tr> 
                              <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                            </tr>
                          </table>
                        </td>
                    </tr>
                  </table>
                  <table border="0" cellspacing="10" cellpadding="0">
                    <tr> 
                      <td> 
                        <p>Click the button below to export your search results 
                          to a spreadsheet file. Most browsers will offer to either 
                          open this file in Microsoft Excel, or save the file 
                          to your hard disk.</p>
                        <p style="margin-bottom:20px">The exported data is tab-delimited 
                          (columns are separated by tabs, rows are separated by 
                          returns) so the file can be opened in most spreadsheet, 
                          database, word processing and text editing programs.</p>
                        <table border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td>
                              <table border="0" cellspacing="0" cellpadding="1" bgcolor=black>
                                <tr>
                                  <td>
								  <table border="0" cellspacing="0" cellpadding="10" bgcolor=white>
  <tr>
    <td>
	<p><span class="formheader">Copyright notice</span></p>
                                          <p>All exported species data is &copy; 
                                            2005 Species 2000 on behalf of the 
                                            Catalogue of Life partners. Use of 
                                            the species names for publications 
                                            and databases is encouraged, with 
                                            appropriate acknowledgement of 1) 
                                            the Species 2000 &amp; ITIS Catalogue 
                                            of Life, 2005 Annual Checklist, (2) 
                                            the member database(s) concerned, 
                                            and (3) the experts responsible for 
                                            individual records. Sale or multiple 
                                            reproduction of this compilation or 
                                            any of the species datasets contained 
                                            within requires written permission 
                                            from Species 2000 and/or ITIS.</p>
                        
	</td>
  </tr>
</table>
								  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <br>
                        <form name="export_results" method="post" action="export_search_results2.php">
                          <input type="hidden" name="search_kingdom" value="<?php echo urlencode($search_kingdom) ?>">
                            <input type="hidden" name="search_phylum" value="<?php echo urlencode($search_phylum) ?>">
                            <input type="hidden" name="search_class" value="<?php echo urlencode($search_class) ?>">
                            <input type="hidden" name="search_order" value="<?php echo urlencode($search_order) ?>">
                            <input type="hidden" name="search_family" value="<?php echo urlencode($search_family) ?>">
                            <input type="hidden" name="search_genus" value="<?php echo urlencode($search_genus) ?>">
                            <input type="hidden" name="search_species" value="<?php echo urlencode($search_species) ?>">
                            <input type="hidden" name="search_infraspecies" value="<?php echo urlencode($search_infraspecies) ?>">
                            <input type="hidden" name="search_common_name" value="<?php echo urlencode($search_common_name) ?>">
                            <input type="hidden" name="search_distribution" value="<?php echo urlencode($search_distribution) ?>">
                            <input type="hidden" name="search_simple" value="<?php echo urlencode($search_simple) ?>">
                            <input type="hidden" name="search_mode" value="<?php echo $search_mode ?>">
	                        <input type="hidden" name="search_page" value="<?php echo urlencode($search_page) ?>">
						    <input type="hidden" name="sort_by_column" value="<?php echo $sort_by_column ?>">
                            
                          <input type="submit" name="Submit" value="Export to file" class="formbutton">
                        </form>
                        <p style="margin-top:36px ; margin-bottom:12px"><a href="JavaScript:history.back();"><span style="font-size:9">&lt;&lt;</span> 
                          Back to search results</a></p>
    </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
      </table>
<p><img src="images/blank.gif" width="530px" height="1" border="0"></p>
      </td>
  </tr>
</table>
</div>
</body>
</html>
