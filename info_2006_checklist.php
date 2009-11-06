<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2006 Annual Checklist : About the 2006 Annual Checklist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
 <?php
	include "connect_to_database.php" ;
	$query = "SELECT  SUM(`accepted_species_names`) , SUM(`accepted_infraspecies_names`) FROM `databases` " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result) ;
	$accepted_species_names = addCommas($row[0]) ;
	$accepted_infraspecies_names = addCommas($row[1]) ;
	
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
                          <p class="formheader" align="center">The 2006 Annual 
                            Checklist</p>
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
                          <p>The 2006 Annual Checklist contains information on 
                            <?php echo "<b>$accepted_species_names</b>" ; ?>
                            species and 
                            <?php echo "<b>$accepted_infraspecies_names</b>" ; ?>
                            infraspecific taxa in the following groups:</p>
                          <p><b class="aboutcolheader">Viruses</b> &#149; Viruses 
                            and Subviral agents from ICTVdB</p>
                          <p><b class="aboutcolheader">Bacteria</b> and <b class="aboutcolheader">Archaea</b> 
                            from BIOS &#149; Blue-green algae (Cyanobacteria) 
                            from AlgaeBase</p>
                          <p><b class="aboutcolheader">Chromista</b> &#149; Chromistan 
                            fungi from Species Fungorum &#149; Chromistan algae 
                            from AlgaeBase</p>
                          <p><b class="aboutcolheader">Protozoa</b> &#149; Major 
                            groups from ITIS and AlgaeBase &#149; Protozoan fungi 
                            from Species Fungorum and Trichomycetes database</p>
                          <p><b class="aboutcolheader">Fungi</b> &#149; Various 
                            taxa in whole or in part from CABI Bioscience databases 
                            (Species Fungorum, Phyllachorales, Rhytismatales and 
                            Zygomycetes databases) and from three other databases 
                            covering Xylariaceae, Glomeromycota <span class="new">NEW!</span> 
                            and Trichomycetes</p>
                          <p><b class="aboutcolheader">Plantae (Plants)</b> &#149; 
                            Mosses from MOST &#149; Cycads and 6 flowering plant 
                            families from IOPI-GPC and 10 families from RBG Kew 
                            Checklist <span class="new">NEW!</span> &#149; Legumes 
                            from ILDIS &#149; Seagrasses from AlgaeBase</p>
                          <p><b class="aboutcolheader">Animalia (Animals)</b> 
                            &#149; Marine invertebrates (13 phyla &amp; 7 classes) 
                            and chordates (4 classes) from URMO &#149; Sea anemones 
                            from the Hexacorallians of the World &#149; Snails 
                            and slugs (some groups) from AFD and ITIS &#149; Krill 
                            from ETI WBD &#149; Spiders from SpidCat &#149; Ticks 
                            from TicksBase <span class="new">NEW!</span> &#149; 
                            Dragonflies from the Odonata database <span class="new">NEW!</span> 
                            &#149; Crickets, grasshoppers, locusts, and katydids 
                            from the OSF &#149; Planthoppers from FLOW <span class="new">NEW!</span> 
                            &#149; Scale insects from ScaleNet &#149; Butterflies 
                            and moths from LepIndex <span class="new">NEW!</span> 
                            and Tineidae NHM &#149; Flies, craneflies, mosquitoes, 
                            bots, midges and gnats from BDWD, CCW, CIPA and ITIS 
                            &#149; Fleas from Parhost &#149; Wasps from UCD <span class="new">NEW!</span> 
                            and ZOBODAT <span class="new">NEW!</span> &#149; Scarab 
                            beetles from the World Scarabaeidae Database &#149; 
                            Longhorn beetles from TITAN &#149; Fishes from FishBase 
                            &#149; Reptiles from EMBL Reptiles <span class="new">NEW!</span> 
                            &#149; Various groups of amphibians, birds and mammals 
                            from ITIS.</p>
                          <p><span class="aboutcolheader">PLUS</span> additional 
                            species of various groups from ITIS.<span class="new"><br>
                            </span></p>
                          <p>&nbsp;</p>
                          <p class="fieldheader">Structure of the Annual Checklist</p>
                          <p>The goal is to list every distinct species in each 
                            group of organisms. At the present stage some groups 
                            are globally complete, some are represented by global 
                            sectors that are nearing completion, and others are 
                            represented with partial sectors. The global sectors, 
                            whether complete or not, are provided by selected, 
                            peer reviewed global species databases (GSDs) in the 
                            Species 2000 federation or by equivalent global sectors 
                            of ITIS. Figure 1 provides the definition of a GSD. 
                            The partial sectors are supplied principally by ITIS 
                            (N America), but also Species Fungorum and the Australian 
                            Faunal Directory.</p>
                         <div style="padding-top:5px; padding-bottom:5px">
                           <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
                            <tr> 
                              <td> 
                                <table width="100%" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF">
                                  <tr> 
                                    <td> 
                                      <p>Fig. 1. Global Species Databases (GSDs) 
                                        or GSD sectors</p>
                                      <p>GSDs aspire to the following properties: 
                                        <br>
                                        &#149; <b></b>Cover one taxon worldwide 
                                        <br>
                                        &#149; <b></b>Contain a taxonomic checklist 
                                        of all species within that taxon <br>
                                        &#149; <b></b>Deal with species as taxa, 
                                        and contain synonymy and taxonomic opinion 
                                        <br>
                                        &#149; <b></b>Have an explicit mechanism 
                                        for seeking at least one responsible/consensus 
                                        taxonomy, and for applying it consistently 
                                        <br>
                                        &#149; <b></b>Cross-index significant 
                                        alternative taxonomies in their synonymy</p>
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
						  </div>
                          <p> Each species is listed with an Accepted scientific 
                            name, a cited reference and its Family and/or position 
                            in the hierarchical classification. Additionally Common 
                            Names and Synonyms may be provided, but these data 
                            are not complete, and for some species none may exist. 
                            The complete Standard Dataset is given in Figure 2. 
                            Where available from the suppliers, infra-specific 
                            taxa such as subspecies and varieties have been included 
                            but this coverage is variable between taxonomic sectors.</p>
                          <div style="padding-top:5px; padding-bottom:5px">
                          <table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
                            <tr> 
                              <td> 
                                <table width="100%" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF">
                                  <tr> 
                                    <td> 
                                      <p>Fig. 2. The Catalogue of Life Standard 
                                        Dataset</p>
                                      <p>(1) Accepted scientific name (and reference) 
                                        <br>
                                        (2) Synonyms (and references) <br>
                                        (3) Common names (and references) <br>
                                        (4) Latest taxonomic scrutiny (Name of 
                                        person and date) <br>
                                        (5) Source database <br>
                                        (6) Additional data (optional) <br>
                                        (7) Family to which species belongs <br>
                                        (8) Classification above family, and highest 
                                        taxon in database <br>
                                        (9) Distribution <br>
                                        (10) Reference(s) (optional)</p>
                          </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
						  </div>
                          <p>Each species is linked via the Genus and Family to 
                            the hierarchical classification. Above the node of 
                            attachment of each data sector this classification 
                            has been agreed by Species 2000 and ITIS as a practical 
                            management tool to provide access to the Catalogue. 
                            Version 1 has remained unchanged in 2005 and 2006. 
                            Below the node of attachment the classification originates 
                            from the supplier databases. Where possible, a web 
                            link back to the supplier's own database is provided 
                            at the bottom of each species detail page.</p>
                          <p>&nbsp;</p>
                          <p><span class="fieldheader">Functionality of the Annual 
                            Checklist</span></p>
                          <p>&#149; Species (and infra-specific taxa) can be located 
                            either by searching by name or by tracking down through 
                            the hierarchical classification.<br>
                            &#149; Searching by name can be done using accepted 
                            scientific name, synonym or common name. Automatic 
                            synonymic and common name indexing takes the user 
                            directly to the species under its accepted name. The 
                            search can use part names, or be restricted to complete 
                            words.<br>
                            &#149; Tracking down the tree or hierarchy uses accepted 
                            names for taxa.<br>
                            &#149; The CD contains an identical copy of the Annual 
                            Checklist dataset and of the software used on the 
                            Web.<br>
                            &#149; The structure of the Annual Checklist database 
                            has been optimised for performance with the user interface 
                            but is not ideal for importing to other systems.<br>
                            &#149; The content may be copied subject to the Copyright 
                            conditions given on the inside cover of this booklet.</p>
                          <p>&nbsp;</p>
                          <p class="fieldheader">The Annual Checklist Editors</p>
                          <p>The Catalogue of Life programme combines the outputs 
                            of the Species 2000 programme (led by Frank Bisby 
                            from Reading, UK) and the ITIS programme (led by Michael 
                            Ruggiero from Washington, USA). Assembly and publication 
                            of the Annual Checklist is managed by Yuri Roskov 
                            in Reading working with colleagues in the Philippines, 
                            Paris, Egham and Amsterdam.</p>
                          <p><i>Frank A Bisby</i>, Species 2000 Executive Director 
                            and Team member, at the Species 2000 Secretariat, 
                            Reading, UK.</p>
                          <p><i>Michael A Ruggiero</i>, Director of ITIS, ITIS 
                            Secretariat c/o Smithsonian Institution, Washington 
                            DC, USA.</p>
                          <p><i>Yuri R Roskov</i>, Species 2000 Content Manager 
                            at the Species 2000 Secretariat, Reading, UK.</p>
                          <p><i>Monalisa Cachuela-Palacio</i>, Annual Checklist 
                            Dataset Compiler at the Species 2000 Philippines office, 
                            Los-Ba&ntilde;os, Philippines.</p>
                          <p><i>Susanah W Kimani</i>, Annual Checklist Manager 
                            (April - October 2005) at the Species 2000 Secretariat, 
                            Reading, UK.</p>
                          <p><i>Paul M Kirk</i>, Taxonomic Editor of Fungi and 
                            Chromista sectors at CABI Bioscience, Egham, UK.</p>
                          <p><i>Adeline Soulier-Perkins</i>, Content management 
                            of European GSDs at the Species 2000 europa office, 
                            Paris, France.</p>
                          <p><i>Jorrit van Hertum</i>, Software and User Interface 
                            programmer at the ETI Bioinformatics, Amsterdam, The 
                            Netherlands.</p>
                          <div align="center" style="padding-top:25px;padding-bottom:25px"><img src="images/2006_checklist_logos.jpg" width="517" height="75" border="0" alt="logos" usemap="#Map"></div>
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
<map name="Map"> 
  <area shape="rect" coords="1,2,92,74" href="http://europa.eu.int/" target="_blank" alt="European Union" title="European Union">
  <area shape="rect" coords="147,0,255,73" href="http://www.nbii.gov/index.html" target="_blank" alt="NBII" title="NBII">
  <area shape="rect" coords="283,1,384,74" href="http://www.gbif.org/" target="_blank" alt="GBIF" title="GBIF">
  <area shape="rect" coords="413,1,516,74" href="http://www.nies.go.jp/" target="_blank" alt="NIES" title="NIES">
</map>
</body>
</html>
