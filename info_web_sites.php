<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2009 Annual Checklist : Web sites</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "includes/config.php" ;
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
                          <p class="formheader" align="center">Web sites</p>
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
                          <p><b>Online version of the Annual Checklist:</b></p>
                          <p><a href="http://www.catalogueoflife.org/annual-checklist/2009/" target="_blank">www.catalogueoflife.org/annual-checklist/2009/</a>
                          <p><strong> Web service </strong>
                          <p><strong> </strong>Species 2000 provides web services to facilitate automated access to the Annual Checklist by computer programs. Please visit <a href="http://www.sp2000.org/webservices">www.sp2000.org/webservices</a> for more information. </p>
                          <p>
                            <!--                            <span class="fieldheader">Mirror sites:</span></p>
                          <p>Asia: <a href="http://www.sp2000.nies.go.jp/annualchecklist.html" target="_blank">http://www.sp2000.nies.go.jp/annualchecklist.html</a><br>
                            North America: <a href="http://www.usa.sp2000.org/AnnualChecklist.html" target="_blank">http://www.usa.sp2000.org/AnnualChecklist.html</a></p>-->
                            
                        </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="0" cellpadding="10">
                    <tr> 
                      <td> 
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
                          <tr> 
                            <td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td>
                          </tr>
                        </table>
                        <?php
	include "info_arrow_buttons.php" ;
?>
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
