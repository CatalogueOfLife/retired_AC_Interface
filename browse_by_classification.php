<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Browse by classification</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script language="JavaScript" type="text/javascript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->
</script>
<?php
	$show_taxon = "" ;
	$select_taxon = "" ;
	$search_kingdom = "" ;
	$search_phylum = "" ;
	$search_class = "" ;
	$search_order = "" ;
	$search_family = "" ;
	$search_genus = "" ;
	$search_species = "" ;
	$search_infraspecies = "" ;
	$search_mode = "whole words" ;
	$search_page = "browse_by_classification.php" ;
	
	if (isset($_REQUEST["show_taxon"])) {
		$show_taxon = $_REQUEST["show_taxon"] ;
	}
	if (isset($_REQUEST["select_taxon"])) {
		$select_taxon = $_REQUEST["select_taxon"] ;
	}
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
		$search_species = urldecode($_REQUEST["search_species"]) ;
	}
	if (isset($_REQUEST["search_infraspecies"])) {
		$search_infraspecies = urldecode($_REQUEST["search_infraspecies"]) ;
	}
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = urldecode($_REQUEST["search_mode"]) ;
	}
	
	if ($select_taxon == "") {
		if ($show_taxon != "") {
			$select_taxon = $show_taxon ;
		} else {
			$select_taxon = "kingdom" ;
		}
	}
	echo "<script language='JavaScript' type='text/javascript'>\n" ;
	echo "function setFocus() {\n" ;
	echo "document.search_form.search_" . $select_taxon . ".focus() ;\n" ;
	if ($show_taxon != "") {
		echo "var theValue = document.search_form.search_" . $select_taxon . ".value ;\n" ;
		echo "if (theValue != '') {\n" ;
		echo "var theLastChar = theValue.charCodeAt((theValue.length)-1) ;\n" ;
		echo "autoComplete('$select_taxon',theLastChar) ;\n" ;
		echo "}\n" ;
	}
	echo "}\n" ;
	echo "</script>\n" ;
?>

</head>
<body onLoad="JavaScript: moveMenu(); setFocus(); preloadImages();" onScroll="moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top> 
<?php
	include "menu.php" ;
?>
<form name="page_status">
	<input type='hidden' name='status' value=''>
</form>
<script language="JavaScript" type="text/javascript">
	/*
	var show_taxon = "<?php echo $show_taxon ; ?>" ;
	if (show_taxon == "genus" || show_taxon == "species" || show_taxon == "infraspecies"){
		document.page_status.status.value = "" ;
		showWaitScreen('');
	}
	*/
</script>
      </td>
      
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> </td>
	  
    <td valign=top>
<form name="search_form" method="post" action="search_results.php" onSubmit="JavaScript: getSearchMode(); document.page_status.status.value = ''; showWaitScreen('Search in progress. ');">
<table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366">
          <tr> 
            <td> 
              <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
                <tr> 
                  <td> 
                    <table width="100%" border="0" cellspacing="0" cellpadding="10">
                      <tr> 
                        <td>
                          <p class="formheader" align="center">Browse by classification</p>
                          <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                            <tr> 
                              <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
<?php
	include "get_taxon_list.php" ;
?>
                    <table border="0" cellspacing="5" cellpadding="0">
                      <tr> 
                        <td> 
                    <table border="0" cellspacing="0" cellpadding="4">
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Top level group</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_kingdom" size="40" value="<?php echo stripslashes(htmlentities($search_kingdom)) ?>"
							  onKeyUp="autoComplete('kingdom',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "kingdom" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Phylum</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_phylum" size="40" value="<?php echo stripslashes(htmlentities($search_phylum)) ?>" 
							  onKeyUp="autoComplete('phylum',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "phylum" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Class</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_class" size="40" value="<?php echo stripslashes(htmlentities($search_class)) ?>" 
							  onKeyUp="autoComplete('class',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "class" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Order</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_order" size="40" value="<?php echo stripslashes(htmlentities($search_order)) ?>" 
							  onKeyUp="autoComplete('order',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "order" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Family</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_family" size="40" value="<?php echo stripslashes(htmlentities($search_family)) ?>" 
							  onKeyUp="autoComplete('family',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "family" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Genus</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_genus" size="40" value="<?php echo stripslashes(htmlentities($search_genus)) ?>" 
							  onKeyUp="autoComplete('genus',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "genus" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                        </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Species</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_species" size="40" value="<?php echo stripslashes(htmlentities($search_species)) ?>" 
							  onKeyUp="autoComplete('species',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "species" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                         </td>
                      </tr>
                      <tr> 
                        <td valign=top> 
                                <p class="formfieldheader">Infraspecies</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="search_infraspecies" size="40" value="<?php echo stripslashes(htmlentities($search_infraspecies)) ?>" 
							  onKeyUp="autoComplete('infraspecies',event.keyCode,event.ctrlKey,event.altKey)">
<?php
	$this_taxon = "infraspecies" ;
	if ($show_taxon == $this_taxon) {
		include "show_taxon_list.php" ;
	}
?>
                       </td>
                        <td valign=top> 
<?php
	include "show_arrow_button.php" ;
?>
                       </td>
                      </tr>
					</table>
                                  <table border="0" cellspacing="0" cellpadding="0">
                                    <td width="24px"> </td>
                                    <tr> 
                                      <td> <img src="images/blank.gif" border="0" height="5" width="1"></td>
                                    </tr>
                                    <tr> 
                                      <td width="24px"> 
                                        <input type="checkbox" name="find_whole_words" <?php if ($search_mode == "whole words") { echo " checked" ; } ?>>
                                        <input type="hidden" name="search_mode" value="">
                                      </td>
                                      <td valign=top> 
                                        <p class="formfieldheader">Match whole 
                                          words only</p>
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                      </tr>
                      <tr height="42px"> 
                        <td valign=top>&nbsp;</td>
                        <td valign=bottom> 
                          <div align="right"> 
						<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
     <input type="reset" name="Reset" value="Clear form" class="formbutton" onClick="JavaScript:document.reset_form.submit()">
    </td>
    <td valign=top width=5px><img src="images/blank.gif" width="1" height="12" border="0"></td>
    <td>
         <input type="submit" name="Submit" value="Search >>" class="formbutton">
    </td>
  </tr>
</table>
</div>
                        </td>
                      </tr>
                    </table>
                  </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
        </table>
          <input type="hidden" name="search_page" value="browse_by_classification.php">
</form></td>
  </tr>
</table>
<form name="list_of_names" method="post" action="">
<?php
	if (!isset($full_list)) {
		$full_list = '' ;
	}
	echo "<input type='hidden' name='taxon' value='$show_taxon'>" ;
	echo "<input type='hidden' name='names' value='$full_list'>" ;
?>
</form>
<form name="show_names" method="post" action="browse_by_classification.php">
	<input type="hidden" name="show_taxon" value="">
	<input type="hidden" name="select_taxon" value="">
	<input type="hidden" name="search_kingdom" value="">
	<input type="hidden" name="search_phylum" value="">
	<input type="hidden" name="search_class" value="">
	<input type="hidden" name="search_order" value="">
	<input type="hidden" name="search_family" value="">
	<input type="hidden" name="search_genus" value="">
	<input type="hidden" name="search_species" value="">
	<input type="hidden" name="search_infraspecies" value="">
	<input type="hidden" name="selected_letter" value="">
</form>
<form name="reset_form" method="post" action="browse_by_classification.php">
	<input type="hidden" name="search_kingdom" value="">
	<input type="hidden" name="search_phylum" value="">
	<input type="hidden" name="search_class" value="">
	<input type="hidden" name="search_order" value="">
	<input type="hidden" name="search_family" value="">
	<input type="hidden" name="search_genus" value="">
	<input type="hidden" name="search_species" value="">
	<input type="hidden" name="search_infraspecies" value="">
</form>
</div>
</body>
</html>
