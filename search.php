<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Search</title>
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
	$search_simple = "" ;
	if (isset($_REQUEST["search_simple"])) {
		$search_simple = urldecode($_REQUEST["search_simple"]) ;
	}
	$search_mode = "" ;
	if (isset($_REQUEST["search_mode"])) {
		$search_mode = urldecode($_REQUEST["search_mode"]) ;
	}
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onLoad="JavaScript: moveMenu(); document.search_form.search_simple.select();" onScroll="moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top> 
<?php
	include "menu.php" ;
?>
      </td>
      
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> </td>
	  
    <td valign=top> 
      <form name="search_form" method="post" action="search_results.php" onSubmit="JavaScript:getSearchMode(); showWaitScreen('Search in progress. ');">
        <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366">
          <tr> 
            <td> 
              <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
                <tr> 
                  <td> 
                    <table width="100%" border="0" cellspacing="0" cellpadding="10">
                      <tr> 
                        <td>
                          <p class="formheader" align="center">Search the Annual 
                            Checklist</p>
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
                              <td valign="top"> 
                                <p class="formfieldheader">Search for:</p>
                              </td>
                              <td valign=top> 
                                <input type="text" name="search_simple" size="40" value="<?php echo stripslashes(htmlentities($search_simple)) ?>">
 
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <td width="24px"> 
                                     </td>
                                  <tr> 
                                    <td> <img src="images/blank.gif" border="0" height="5" width="1"></td>
                                  </tr>
                                  <tr> 
                                    <td width="24px"> 
                                        <input type="checkbox" name="find_whole_words" <?php if ($search_mode == "whole words") { echo " checked" ; } ?>>
									  <input type="hidden" name="search_mode" value="">
                                    </td>
                                    <td valign=top> 
                                      <p class="formfieldheader">Match whole words 
                                        only</p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                            <tr height="42px"> 
                              <td valign=bottom>&nbsp;</td>
                              <td valign=bottom> 
                                <div align="right"> 
                                  <table border="0" cellspacing="0" cellpadding="0">
                                    <tr> 
                                      <td> 
                                        <input type="submit" value="Search >>" class="formbutton">
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
		<input type="hidden" name="search_page" value="search.php">
</form>
    </td>
  </tr>
</table>
</div>
</body>
</html>
