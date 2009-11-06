<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2006 Annual Checklist : About the Catalogue of Life</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
 <?php
	function addCommas($value) {
		$length = strlen($value) ;
		$counter = 0 ;
		$new_value = "" ;
		for ($i = 0; $i < $length; $i++) {
			$counter ++ ;
			if ($counter == 4) {
				$counter == 0 ;
				$new_value = "," .$new_value  ;
			}
			$new_value = substr($value,$length-1-$i,1) . $new_value ;
		}
		return $new_value ;
	}
	
	include "connect_to_database.php" ;
	$query = "SELECT SUM(`accepted_species_names`) , SUM(`accepted_infraspecies_names`) FROM `databases` " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result) ;
	$accepted_species_names = addCommas($row[0]) ;
	$accepted_infraspecies_names = addCommas($row[1]) ;
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
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
                          <p class="formheader" align="center">About the Catalogue 
                            of Life</p>
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
                          <p class="fieldheader">The Catalogue of Life</p>
                          <p>The Species 2000 &amp; ITIS Catalogue of Life is 
                            planned to become a comprehensive catalogue of all 
                            known species of organisms on Earth by the year 2011. 
                            Rapid progress has been made recently and this, the 
                            sixth edition of the Annual Checklist, contains <?php echo "<b>$accepted_species_names</b>" ; ?> 
                            species, approximately half of all known organisms.</p>
                          <p>The present Catalogue is compiled with sectors provided 
                            by 37 taxonomic databases from around the world. Many 
                            of these contain taxonomic data and opinions from 
                            extensive networks of specialists, so that the complete 
                            work contains contributions from an estimated 2-3,000 
                            specialists from throughout the taxonomic profession. 
                            The work of the Species 2000 and ITIS teams is to 
                            peer review databases, select appropriate sectors 
                            and to integrate the sectors into a single coherent 
                            catalogue with a single hierarchical classification. 
                            It is planned in future to introduce alternative taxonomic 
                            treatments and alternative hierarchies, but an important 
                            feature is that for those users who wish to use it, 
                            a single preferred catalogue, based on peer reviews, 
                            will continue to be provided.</p>
                          <p>The Catalogue is published in two products:</p>
                          <p>&#149; <b>Species 2000 &amp; ITIS Catalogue of Life: 
                            2006 Annual Checklist</b><br>
                            The Annual Checklist is published each year as a fixed 
                            edition that can be cited and used as a common catalogue 
                            for comparative purposes by many organisations. A 
                            copy is on CD-ROM, which is distributed free of charge, 
                            and an identical copy is on the web site at <a href="http://www.sp2000.org/" target="_blank" title="Species 2000">www.sp2000.org</a>. 
                            Archive versions of the earlier editions are on the 
                            web site.</p>
                          <p>&#149; <b>Species 2000 &amp; ITIS Catalogue of Life: 
                            Dynamic Checklist</b><br>
                            The Dynamic Checklist is a virtual catalogue operated 
                            on the internet and available both for users and as 
                            an electronic web-service at <a href="http://www.sp2000.org/" target="_blank" title="Species 2000">www.sp2000.org</a>. 
                            The Dynamic Checklist harvests taxonomic sectors and 
                            strands of hierarchical classification dynamically 
                            from the source databases across the internet. The 
                            Dynamic Checklist is presently less extensive than 
                            the Annual Checklist because fewer taxonomic sectors 
                            have been connected so far. It differs in concept 
                            from the Annual Checklist in that:<br>
                            (i) the taxonomic records may be updated and the catalogue 
                            changed more frequently than in the Annual Checklist 
                            and (ii) the Dynamic Checklist contains additional 
                            regional species checklists (such as the Regional 
                            Checklist - Europe, effectively a Pan-European Species 
                            Checklist) not included in the Annual Checklist.</p>
                          <p>&nbsp;</p>
                          <p class="fieldheader">The Catalogue of Life partnership</p>
                          <p>In June 2001 the Species 2000 and ITIS organisations 
                            decided to work together to create the Catalogue of 
                            Life. They declared a target for completing coverage 
                            for all 1.75 million known species by 2011. The two 
                            organisations remain separate and different in structure. 
                            However, by working together in creating a common 
                            product, the partnership has enabled them to reduce 
                            duplication of effort, make better use of resources, 
                            and to accelerate production. The combined Annual 
                            Checklist on CD-ROM has become well established as 
                            a cited reference used for data compilation and composition. 
                            For instance, it is used as the principal taxonomic 
                            index in the GBIF data portal and recognised by the 
                            CBD. The partnership receives support from GBIF.</p>
                          <p>&nbsp;</p>
                          <p class="fieldheader">About ITIS</p>
                          <p><a href="http://www.itis.usda.gov/" target="_blank" title="ITIS"><img src="images/ITIS.gif" width="161" height="80" vspace="20" align="right" hspace="20" lowsrc="10" border="0"></a>The 
                            Integrated Taxonomic Information System (ITIS) is 
                            a partnership of federal agencies and other organisations 
                            from the United States, Canada, and Mexico, with data 
                            stewards and experts from around the world (see <a href="http://www.itis.usda.gov/" target="_blank" title="ITIS">www.itis.usda.gov</a>). 
                            The ITIS database is an automated reference of scientific 
                            and common names of biota of interest to North America. 
                            It contains more than 400,000 names for species in 
                            all kingdoms, and is accessible via the World Wide 
                            Web in English, French, Spanish, and Portuguese (<a href="http://itis.gbif.net" target="_blank" title="ITIS">http://itis.gbif.net</a>). 
                            ITIS is part of the US National Biological Information 
                            Infrastructure (<a href="http://www.nbii.gov/" target="_blank" title="NBII">www.nbii.gov</a>) 
                            and an associate member of GBIF.<br>
                            ITIS is managed by Michael Ruggiero (Director), Thomas 
                            Orrell (Program Manager), David Nicolson (Data Development 
                            Leader), Paula Huddleston (Information Technology 
                            Leader), Roy McDiarmid (Taxonomy Leader), Guy Baillargeon 
                            (ITIS-Canada Director), and Patricia Koleff (SIIT-Mexico 
                            Director). They are advised and supported by the ITIS 
                            Steering Committee and ITIS Data Stewards.</p>
                          <p>&nbsp;</p>
                          <p class="fieldheader">About Species 2000 </p>
                          <p><a href="http://www.sp2000.org" target="_blank" title="Species 2000"><img src="images/sp2000_small.gif" width="142" height="62" vspace="20" align="right" hspace="20" lowsrc="10" border="0"></a>Species 
                            2000 (<a href="http://www.sp2000.org/" target="_blank" title="Species 2000">www.sp2000.org</a>) 
                            is an autonomous federation of taxonomic database 
                            custodians, involving taxonomists throughout the world. 
                            Its goal is to collate a uniform and validated index 
                            to the world's known species. It is a not-for-profit 
                            company limited by guarantee (Registered in England 
                            No. 3479405) with five directors and taxonomic database 
                            organisations from around the world as members. It 
                            is sponsored by CODATA, IUBS and IUMS; is an associate 
                            member of GBIF; and is recognised by UNEP and the 
                            CBD. There are two regional programmes: Species 2000 
                            europa (<a href="http://www.sp2000europa.org/" target="_blank" title="Species 2000 europa">sp2000europa.org</a>), 
                            funded by the European Commission, working with global 
                            and regional databases based in Europe; Species 2000 
                            Asia-Oceania (<a href="http://www.sp2000ao.nies.go.jp/" target="_blank" title="Species 2000 Asia-Oceania">www.sp2000au.nies.go.jp</a>) 
                            working to promote taxonomy and taxonomic databasing 
                            in that region.</p>
                          <p>The Species 2000 scientific programme is led by its 
                            Team (Karen L Wilson (Australia), Junko Shimura (Japan), 
                            Frank A. Bisby (UK), Guy Baillargeon (Canada), Vanderlei 
                            P. Canhos (Brazil), Michael A. Ruggiero (USA), Peter 
                            H. Schalk (The Netherlands) &amp; Richard J. White 
                            (UK)) with further assistance from the Species 2000 
                            europa Steering Group and the Species 2000 Asia-Oceania 
                            Group. </p>
                          <p><img src="images/blank.gif" width="510" height="1" border="0"></p>
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
