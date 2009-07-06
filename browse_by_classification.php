<?php
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2009 Annual Checklist : Browse by classification</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script language="JavaScript" type="text/javascript" src="scripts_autocomplete.js">
</script>
<?php
	$variables_to_get = Array("search_type","show_taxon","select_taxon","kingdom","phylum","tax_class",
	  "order","superfamily","family", "genus","species","infraspecies","match_whole_words") ;
	foreach($variables_to_get as $variable){
		if (isset($_REQUEST[$variable])) {
			$$variable = $_REQUEST[$variable] ;
		} else if (isset($_SESSION["ac_$variable"])) {
			$$variable = $_SESSION["ac_$variable"] ;
		} else {
			$$variable = "" ;
		}
		$$variable = trim(stripslashes(urldecode($$variable))) ;
	}
	if ($search_type != "browse_by_classification") {
		$kingdom = "" ;
		$phylum = "" ;
		$tax_class = "" ;
		$order = "" ;
		$superfamily = "" ;
		$family = "" ;
		$genus = "" ;
		$species = "" ;
		$infraspecies = "" ;
	}
	if ($match_whole_words != "on" && $match_whole_words  != "off") {
		$match_whole_words = "on" ;
	}
	if ($select_taxon == "") {
		if ($show_taxon != "") {
			$select_taxon = $show_taxon ;
		} else {
			$select_taxon = "kingdom" ;
		}
	}
	
	include "get_taxon_list.php" ;
?>
<script language="JavaScript" type="text/javascript">
	function setFocus() { 
<?php
	echo "document.search_form." . (($select_taxon == "class") ? "tax_class" : $select_taxon) . ".focus() ;\n" ;
	if ($show_taxon != "") {
		echo "ShowLayer('nameslayercontents','visible');\n" ;
		echo "selectRow('$select_taxon')\n" ;
	}
?>
	}
</script>
</head>
<body onload="JavaScript: moveMenu(); setFocus(); preloadImages();" onscroll="moveMenu();">
<?php
	require_once "ac_config.php" ;
	if ($online_or_offline_version == "offline") {
		include "cd_rom_version_icon.php" ;
	}
?>
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top> 
<?php
	require_once "menu.php" ;
?>
<form name="list_of_names" action="">
<?php
	if (!isset($full_list)) {
		$full_list = "" ;
	}
	echo "<input type='hidden' name='names' value='$full_list'>" ;
?>
</form>
      </td>
      
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> </td>
	  
    <td valign=top>
<form name="search_form" method="get" action="search_results.php" onsubmit="JavaScript: showWaitScreen('Search in progress. ');">
	<input type="hidden" name="search_type" value="browse_by_classification">
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
                             <input type="text" name="kingdom" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($kingdom))) ?>"
							  onKeyUp="autoComplete('kingdom',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="phylum" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($phylum))) ?>" 
							  onKeyUp="autoComplete('phylum',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="tax_class" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($tax_class))) ?>" 
							  onKeyUp="autoComplete('tax_class',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="order" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($order))) ?>" 
							  onKeyUp="autoComplete('order',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                                <p class="formfieldheader">Superfamily</p>
                        </td>
                        <td valign=top> 
					<table border="0" cellspacing="0" cellpadding="1">
                      <tr> 
                        <td valign=top> 
                             <input type="text" name="superfamily" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($superfamily))) ?>" 
							  onKeyUp="autoComplete('superfamily',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
<?php
	$this_taxon = "superfamily" ;
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
                             <input type="text" name="family" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($family))) ?>" 
							  onKeyUp="autoComplete('family',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="genus" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($genus))) ?>" 
							  onKeyUp="autoComplete('genus',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="species" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($species))) ?>" 
							  onKeyUp="autoComplete('species',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                             <input type="text" name="infraspecies" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($infraspecies))) ?>" 
							  onKeyUp="autoComplete('infraspecies',this.value,event.keyCode,event.ctrlKey,event.altKey)" onfocus="setSelectedTaxon(this.name);">
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
                                        <input type="checkbox" name="match_whole_words" <?php if ($match_whole_words == "on") { echo " checked" ; } ?>>
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
     <input type="reset" name="Reset" value="Clear form" class="formbutton" onClick="JavaScript:document.location.href='browse_by_classification.php?kingdom=&amp;phylum=&amp;class=&amp;order=&amp;superfamily=&amp;family=&amp;genus=&amp;species=&amp;infraspecies'">
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
</form></td>
  </tr>
</table>
</div>
<div style="visibility:hidden">
<form name="show_names" method="get" action="browse_by_classification.php">
	<input type="hidden" name="search_type" value="browse_by_classification">
	<input type="hidden" name="show_taxon" value="">
	<input type="hidden" name="select_taxon" value="">
	<input type="hidden" name="kingdom" value="">
	<input type="hidden" name="phylum" value="">
	<input type="hidden" name="tax_class" value="">
	<input type="hidden" name="order" value="">
	<input type="hidden" name="superfamily" value="">
	<input type="hidden" name="family" value="">
	<input type="hidden" name="genus" value="">
	<input type="hidden" name="species" value="">
	<input type="hidden" name="infraspecies" value="">
	<input type="hidden" name="selected_letter" value="">
</form>
<form name="auto_complete">
	<input type="hidden" name="selected_taxon" value="">
	<input type="hidden" name="old_value" value="">
	<input type="hidden" name="query" value="">
	<input type="hidden" name="taxon_list_shown" value="<?php echo $show_taxon ?>">
	<input type="hidden" name="taxon_list_number_of_names_shown" value="<?php echo $show_taxon ?>">
</form>
<form name="taxon_list">
	<input type="hidden" name="taxon_shown" value="<?php echo $show_taxon ?>">
	<input type="hidden" name="number_of_names_shown" value="<?php echo $number_of_names ?>">
</form>
<div id="showresult"></div>
</div>
</body>
</html>
