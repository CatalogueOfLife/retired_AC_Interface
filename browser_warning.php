<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2009 Annual Checklist : Browser warning</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000">
<?php
	require_once "includes/config.php";
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
                        <p class="formheader" align="center">Browser not supported</p><table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
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
                        <p><b><strong>Warning:</strong></b> The Catalogue of Life 
                          site may not function properly in the browser you are 
                          using
<script language="JavaScript" type="text/javascript">
	var type = getBrowserType() ;
	var platform = getPlatform() ;
	if (type == "SA") {
		document.write(" (Safari)") ;
	} else if (type == "OP") {
		document.write(" (Opera)") ;
	} else if (type == "IC") {
		document.write(" (iCab)") ;
	} else if (type == "IE" && platform == "macintosh") {
		document.write(" (Internet Explorer for Macintosh)") ;
	}
</script>.</p>
                        <p>You can continue to use this browser but we strongly 
                          recommend that you view this site in one of the following 
                          browsers:</p>
                          <ul>
                            <li><a href="http://www.microsoft.com/ie/" target="_blank">Internet 
                              Explorer</a> for Windows (version 5.0 or later)</li>
                            <li><a href="http://www.apple.com/safari/" target="_blank">Safari</a> 
                              for Macintosh</li>
                            <li><a href="http://www.netscape.com/" target="_blank">Netscape 
                              Navigator</a> (version 7.0 or later)</li>
                            <li>Any <a href="http://www.mozilla.org/" target="_blank">Mozilla</a> 
                              browser (version 1.0 or later)</li>
                          </ul>
						
                        <p><img src="images/blank.gif" width="505px" height="1" border="0"></p>
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
