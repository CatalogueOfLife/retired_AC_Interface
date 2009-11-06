<?php
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2006 Annual Checklist : Search by distribution</title>
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
	$variables_to_get = Array("area","match_whole_words") ;
	foreach($variables_to_get as $variable){
		if (isset($_REQUEST[$variable])) {
			$$variable = $_REQUEST[$variable] ;
		} else if ($_SESSION["ac_$variable"]) {
			$$variable = $_SESSION["ac_$variable"] ;
		} else {
			$$variable = "" ;
		}
		$$variable = trim(stripslashes(urldecode($$variable))) ;
	}
	if ($match_whole_words != "on" && $match_whole_words  != "off") {
		$match_whole_words = "on" ;
	}
?>
</head>
<body onload="JavaScript: moveMenu(); document.search_form.area.select();" onscroll="moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top> 
<?php
	require_once "menu.php" ;
?>
      </td>
      
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> </td>
	  
    <td valign=top>
<form name="search_form" method="get" action="search_results.php" onsubmit="JavaScript:showWaitScreen('Search in progress. ');">
	<input type="hidden" name="search_type" value="search_by_distribution">
<table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366">
          <tr> 
            <td> 
              <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
                <tr> 
                  <td> 
                    <table width="100%" border="0" cellspacing="0" cellpadding="10">
                      <tr> 
                        <td>
                          <p class="formheader" align="center">Search by distribution</p>
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
                                <input type="text" name="area" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($area))) ?>">
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
                                      <p class="formfieldheader">Match whole words 
                                        only</p>
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
</form>
</td>
  </tr>
</table>
</div>
</body>
</html>
