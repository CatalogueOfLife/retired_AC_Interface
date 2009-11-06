<?php
	session_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2008 Annual Checklist : Search</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script language="JavaScript" type="text/javascript">
	var preloadImage = new Image() ;
	preloadImage.src = "images/wait.gif" ;
</script>
<?php
	$variables_to_get = Array("search_string","match_whole_words") ;
	foreach($variables_to_get as $variable){
		$session_variable = "ac_$variable" ;
		if (isset($_REQUEST[$variable])) {
			$$variable = $_REQUEST[$variable] ;
		} else if (isset($_SESSION[$session_variable])) {
			$$variable = $_SESSION[$session_variable] ;
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
<body bgcolor="#FFFFFF" text="#000000" onload="JavaScript: moveMenu(); document.search_form.search_string.select();" onscroll="moveMenu();">
<?php
	require_once "ac_config.php" ;
	if ($online_or_offline_version == "offline") {
		include "cd_rom_version_icon.php" ;
	}
?>
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left:15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign=top> 
<?php
	require_once "menu.php" ;
?>
      </td>
      
    <td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> </td>
	  
    <td valign=top> 
        <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366">
          <tr> 
            <td> 
              <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE">
                <tr> 
                  <td> 
                    <table width="100%" border="0" cellspacing="0" cellpadding="10">
                      <tr> 
                        <td>
                          <p class="formheader" align="center">Search the <span style="color:#B41A1A">Annual Checklist</span> - fixed edition each year</p>
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
							  
	      <form name="search_form" method="get" action="search_results.php" onsubmit="JavaScript:showWaitScreen('Search in progress. ');">
                               <input type="text" name="search_string" size="40" value="<?php echo str_replace("%","*",stripslashes(htmlentities($search_string))) ?>">
 
                                <table border="0" cellspacing="0" cellpadding="0">
                                    <td width="24px"> 
                                     </td>
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
								
</form>
								
                              </td>
                            </tr>
                            <tr height="42px"> 
                              <td valign=bottom>&nbsp;</td>
                              <td valign=bottom> 
                                <div align="right"> 
                                  <table border="0" cellspacing="0" cellpadding="0">
                                    <tr> 
                                      <td> 
                                        <input type="button" value="Search >>" class="formbutton" name="Button" onclick="JavaScript:document.search_form.submit();">
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
    </td>
  </tr>
</table>
</div>
</body>
</html>
