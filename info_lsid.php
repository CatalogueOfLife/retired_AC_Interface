<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2008 Annual Checklist : CoL Life Science Identifiers (LSIDs)</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
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
                          <p class="formheader" align="center">CoL Life Science Identifiers (LSIDs)</p>
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
                          <p>
                            This year we have introduced a unique LSID (Life Science Identifier, lsid.sourceforge.net) for every recognised taxon
                            in the Annual Checklist, as recommended by TDWG. LSIDs appear on Species Details pages and in the tree. Software or human users
                            can “resolve” the LSID to obtain information expressed as TCS metadata in RDF format, using an LSID Resolution Service. See:
                            <a href="http://www.catalogueoflife.org/lsid/"  target="_blank">http://www.catalogueoflife.org/lsid/</a>.
                            We thank TDWG and CBOL for funding, and Richard White, Ewen Orme & Andrew Jones (Cardiff University) for the
                            implementation.
                          </p>
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
