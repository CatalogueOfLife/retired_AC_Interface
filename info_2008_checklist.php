<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title> Catalogue of Life : 2008 Annual Checklist : About the 2008 Annual Checklist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<?php
	include "connect_to_database.php" ;
	$query = "SELECT  SUM(`accepted_species_names`) , 
				   SUM(`accepted_infraspecies_names`), 
				   SUM(`infraspecies_synonyms`), 
				   SUM(`species_synonyms`), 
				   SUM(`common_names`) 
				    FROM `databases` " ;
	$result = mysql_query($query) or die("Error: MySQL query failed");
	$row = mysql_fetch_row($result) ;
	$accepted_species_names = addCommas($row[0]) ;
	$accepted_infraspecies_names = addCommas($row[1]) ;
	$synonyms = addCommas($row[2] + $row[3]) ;
	$common_names = addCommas($row[4]) ;
	
	function addCommas($value) {
	//	$length = strlen($value) ;
	//	$counter = 0 ;
	//	$new_value = "" ;
	//	for ($i = 0; $i < $length; $i++) {
	//		$counter ++ ;
	//		if ($counter == 4) {
	//			$counter == 0 ;
	//			$new_value = "," .$new_value  ;
	//		}
	//		$new_value = substr($value,$length-1-$i,1) . $new_value ;
	//	}
		return number_format($value);
	}
?>
</head>
<body bgcolor="#FFFFFF" text="#000000" onload="moveMenu();" onscroll="moveMenu();">
<?php
	require_once "ac_config.php" ;
	if ($online_or_offline_version == "offline") {
		include "cd_rom_version_icon.php" ;
	}
?>
<div style="margin-top:27px; margin-bottom:18px"> <img src="images/banner.gif" width="760" height="100"> 
</div><div style="margin-left: 15px; margin-right:15px;"> <table border="0" cellspacing="0" cellpadding="0"> 
<tr> <td valign=top> <?php
	require_once "menu.php" ;
?> </td><td valign=top> <img src="images/blank.gif" width="8" height="1" border="0"> 
</td><td valign=top> <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100%"> 
<tr> <td> <table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="#FAFCFE"> 
<tr> <td> <table width="100%" border="0" cellspacing="0" cellpadding="10"> 
<tr> <td> <p class="formheader" align="center">The 2008 Annual Checklist</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="0"> 
<tr><td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td></tr>
</table></td></tr> </table><table border="0" cellspacing="10" cellpadding="0"> 
<tr> <td> <img src="images/2008_checklist_cd_front_cover.jpg" alt="2007 Annual Checklist CD-ROM front cover" width="320" height="320" border="0" align="right" hspace="5" vspace="5" /> 
<p style="margin-bottom:20px">The 2008 Annual Checklist contains contributions 
from 52 databases with information on <?php echo "<b>$accepted_species_names</b>" ; ?> 
species and <?php echo "<b>$accepted_infraspecies_names</b>" ; ?> infraspecific 
taxa, and also includes <?php echo "<b>$synonyms</b>" ; ?> synonyms and 
<?php echo "<b>$common_names</b>" ; ?> common names covering the following 
groups:</p><p> <b class="fieldheader">Viruses</b> &#149; Viruses and Subviral 
agents from <a href="show_database_details.php?database_name=ICTV">ICTV.</a> 
</p><p> <b class="fieldheader">Bacteria</b> and <b class="fieldheader">Archaea</b> 
from <a href="show_database_details.php?database_name=BIOS">BIOS</a> &#149; 
Blue-green algae (Cyanobacteria) from <a href="show_database_details.php?database_name=AlgaeBase">AlgaeBase</a>. 
</p><p> <b class="fieldheader">Chromista</b> &#149; Chromistan fungi from 
<a href="show_database_details.php?database_name=Species+Fungorum">Species 
Fungorum</a> &#149; Chromistan algae from <a href="show_database_details.php?database_name=AlgaeBase">AlgaeBase</a>.
</p><p> <b class="fieldheader">Protozoa</b> &#149; Major groups from <a href="show_database_details.php?database_name=ITIS">ITIS</a> 
and <a href="show_database_details.php?database_name=AlgaeBase">AlgaeBase</a> 
&#149; Protozoan fungi from <a href="show_database_details.php?database_name=Species+Fungorum">Species 
Fungorum</a> and <a href="show_database_details.php?database_name=Trichomycetes">Trichomycetes</a> 
database </a>&#149; <a href="show_database_details.php?database_name=Eumycetozoa.com">Eumycetozoa.com</a> <span class="new">NEW!</span> </p><p> <b class="fieldheader">Fungi</b> &#149; Various taxa in 
whole or in part from CABI Bioscience databases (<a href="show_database_details.php?database_name=Species+Fungorum">Species 
Fungorum</a>, <a href="show_database_details.php?database_name=Phyllachorales">Phyllachorales</a>, 
<a href="show_database_details.php?database_name=Rhytismatales">Rhytismatales</a> 
and <a href="show_database_details.php?database_name=Zygomycetes">Zygomycetes</a> 
databases) and from three other databases covering <a href="show_database_details.php?database_name=Xylariaceae">Xylariaceae</a>, 
<a href="show_database_details.php?database_name=Glomeromycota">Glomeromycota</a> 
and <a href="show_database_details.php?database_name=Trichomycetes">Trichomycetes</a> 
</p><p> <b class="fieldheader">Plantae (Plants)</b> &#149; Mosses from <a href="show_database_details.php?database_name=MOST">MOST</a> 
&#149; Conifers from <a href="show_database_details.php?database_name=Conifer+Database">Conifer 
Database</a> &#149; Cycads and 6 flowering plant families from <a href="show_database_details.php?database_name=IOPI-GPC">IOPI-GPC</a> 
and 94 families from <a href="show_database_details.php?database_name=World+Checklist+of+Selected+Plant+Families">World 
Checklist of Selected Plant Families </a> (<span class="New Sectors:">NEW SECTORS: </span> gnetophytes & monocots)
&#149; Custard Apple family from 
<a href="show_database_details.php?database_name=AnnonBase">AnnonBase</a> &#149; Legumes from <a href="show_database_details.php?database_name=ILDIS">ILDIS</a> 
&#149; Cranesbills from <a href="show_database_details.php?database_name=RJB+Geranium">RJB 
Geranium</a> &#149; Brazil Nut family from 
<a href="show_database_details.php?database_name=LecyPages">LecyPages</a> </p><p> <b class="fieldheader">Animalia (Animals)</b> &#149; Sponges from <a href="show_database_details.php?database_name=Porifera">Porifera 
database</a> &#149; Marine invertebrates 
(13 phyla &amp; 7 classes) and chordates (4 classes) from <a href="show_database_details.php?database_name=URMO">URMO</a> 
&#149; Sea anemones from the 
<a href="show_database_details.php?database_name=Hexacorals">Hexacorallians 
of the World</a> &#149; Rotifers from <a href="show_database_details.php?database_name=Rotifera">Rotifera 
database</a> &#149; Spiders from <a href="show_database_details.php?database_name=SpidCat">SpidCat</a> 
&#149; Ticks from <a href="show_database_details.php?database_name=TicksBase">TicksBase</a> 
&#149; Krill from <a href="show_database_details.php?database_name=ETI+WBD+%28Euphausiacea%29">ETI 
WBD</a> &#149; Diplopods, centipedes, pauropods and symphylans from <a href="show_database_details.php?database_name=SysMyr">SysMyr</a>  and <a href="show_database_details.php?database_name=ChiloBase">ChiloBase</a> 
&#149; Dragonflies from the <a href="show_database_details.php?database_name=Odonata">Odonata 
database</a> &#149; Crickets, grasshoppers, locusts, and katydids from the 
<a href="show_database_details.php?database_name=OSF">OSF</a> &#149; Stick and leaf insects from <a href="show_database_details.php?database_name=PSF">PSF</a> <span class="new">NEW!</span> &#149; Cockroaches from <a href="show_database_details.php?database_name=BSF">BSF</a> <span class="new">NEW!</span> &#149; Planthoppers from <a href="show_database_details.php?database_name=FLOW">FLOW</a> &#149; 
Froghoppers from <a href="show_database_details.php?database_name=COOL">COOL</a> <span class="new">NEW!</span>  
&#149; Scale insects from <a href="show_database_details.php?database_name=ScaleNet">ScaleNet</a> 
&#149; Bees and wasps from <a href="show_database_details.php?database_name=ITIS">ITIS</a>, <a href="show_database_details.php?database_name=UCD">UCD</a> 
and <a href="show_database_details.php?database_name=ZOBODAT+%28Vespoidea%29">ZOBODAT</a> 
&#149; Scarab beetles from the <a href="show_database_details.php?database_name=Scarabs">World 
Scarabaeidae Database</a> &#149; Longhorn beetles from <a href="show_database_details.php?database_name=TITAN">TITAN</a> 
&#149; Weevils from <a href="show_database_details.php?database_name=WTaxa">WTaxa</a>  &#149; Fleas from <a href="show_database_details.php?database_name=Parhost">Parhost</a> 
&#149; Flies, craneflies, mosquitoes, bots, 
midges and gnats from <a href="show_database_details.php?database_name=BDWD">BDWD</a>, 
<a href="show_database_details.php?database_name=CCW">CCW</a>, <a href="show_database_details.php?database_name=CIPA">CIPA</a> 
and <a href="show_database_details.php?database_name=ITIS">ITIS</a> &#149; Butterflies and moths from <a href="show_database_details.php?database_name=LepIndex">LepIndex</a>, 
<a href="show_database_details.php?database_name=Tineidae+NHM">Tineidae 
NHM</a>,  <a href="show_database_details.php?database_name=World+Gracillariidae">World Gracillariidae</a> 
<span class="new">NEW!</span> &#149 and <a href="show_database_details.php?database_name=GloBIS+%28GART%29">GloBIS/GART</a> 
&#149; Snails and slugs (some groups) from <a href="show_database_details.php?database_name=AFD+%28Pulmonata%29">AFD</a> 
and <a href="show_database_details.php?database_name=ITIS">ITIS</a> &#149; Fishes from <a href="show_database_details.php?database_name=FishBase">FishBase</a> 
&#149; Reptiles from <a href="show_database_details.php?database_name=TIGR Reptiles">TIGR Reptiles</a> &#149; Amphibians, birds and mammals from 
<a href="show_database_details.php?database_name=ITIS">ITIS</a>.</p><p> 
<span class="fieldheader">PLUS</span> additional species of many groups 
from <a href="show_database_details.php?database_name=ITIS">ITIS</a>.</p><p><b>Structure 
of the Annual Checklist</b><p>The goal is to list every distinct species 
in each group of organisms. At present, some groups are globally complete, 
some are represented by global sectors that are nearing completion, and 
others are represented by partial sectors. The global sectors, whether complete 
or not, are provided by selected, peer reviewed global species databases 
(GSDs - see definition below) in the Species 2000 federation or by equivalent 
global sectors of ITIS. The partial sectors are supplied principally by 
ITIS (N America), but also Species Fungorum and the Australian Faunal Directory, 
with the result that N American species are sometimes the only species represented 
for these incomplete groups.</p><div style="padding-top:5px; padding-bottom:5px"> 
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000"> 
<tr> <td> <table width="100%" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"> 
<tr> <td> <p>Definition of a Global Species Database (GSD)</p><p>GSDs aspire 
to the following properties: <br> &#149; <b> </b>Cover one taxon worldwide 
<br> &#149; <b> </b>Contain a taxonomic checklist of all species within 
that taxon <br> &#149; <b> </b>Deal with species as taxa, and contain synonymy 
and taxonomic opinion <br> &#149; <b> </b>Have an explicit mechanism for 
seeking at least one responsible/consensus taxonomy, and for applying it 
consistently <br> &#149; <b> </b>Cross-index significant alternative taxonomies 
in their synonymy</p></td></tr> </table></td></tr> </table></div><p> Each 
species is listed with an accepted scientific name, a cited reference and 
its family and/or position in the hierarchical classification. Additional 
common names and synonyms may be provided, but these data are not complete, 
and for some species none may exist. The complete list of fields (known 
as the Catalogue of Life Standard Dataset) is given below:</p><div style="padding-top:5px; padding-bottom:5px"> 
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000"> 
<tr> <td> <table width="100%" border="0" cellspacing="0" cellpadding="10" bgcolor="#FFFFFF"> 
<tr> <td> <p>The Catalogue of Life Standard Dataset</p><p>(1) Accepted scientific 
name (and reference) <br> (2) Synonyms (and references) <br> (3) Common 
names (and references) <br> (4) Latest taxonomic scrutiny (name of person 
and date) <br> (5) Source database <br> (6) Additional data (optional) <br> 
(7) Family to which species belongs <br> (8) Classification above family, 
and highest taxon in database <br> (9) Distribution <br> (10) Reference(s) 
(optional)</p></td></tr> </table></td></tr> </table></div><p>More detailed 
information about the Standard Dataset is available on the Species 2000 
website (<a href="http://www.sp2000.org">www.sp2000.org</a>).</p><p>Each 
species is linked via the genus and family to the taxonomic classification. 
Above the node of attachment of each data sector this classification has 
been agreed by Species 2000 and ITIS as a practical management tool to provide 
access to the Catalogue, the Catalogue of Life Taxonomic Classification Edition 1. This top level classification has remained unchanged 
in 2005-2008. However, below the node of attachment the classification 
originates from the supplier databases and so may change from year to year. Because of this lower level changes there are therefore annual versions of Edition 1 for 2005, 2006, 2007 and 2008.</p><p>Where 
available from the suppliers, infraspecific taxa such as subspecies and 
varieties have also been included but this coverage is variable between 
taxonomic sectors.</p><p>Where possible, a web link back to the supplier's 
own database is provided at the bottom of each species detail page.</p><p><b>Functionality of the Annual Checklist</b> </p><p>&#149; 
Species (and infraspecific taxa) can be located either by searching by name 
or by tracking down through the hierarchical classification.<br> &#149; 
Searching by name can be done using accepted scientific name, synonym or 
common name. Automatic synonymic and common name indexing takes the user 
directly to the species under its accepted name. The search can use part 
names, or be restricted to complete words.<br> &#149; Tracking down the 
tree or classification uses accepted names for taxa.<br> &#149; On each species details page the relevant higher taxa are listed, and provide a link to the relevant node of the hierarchical classification.<br> &#149; The species details pages link to the source database, usually showing further information.</p><p>This CD contains the Annual Checklist dataset and the software identical to that used on the Web. The structure 
of the Annual Checklist database has been optimised for performance with 
the user interface but is not ideal for importing to other systems. The 
content may be copied subject to the copyright conditions given on the inside 
cover of this booklet.</p><p><b>The 2008 Annual 
Checklist Editors</b><p>The Catalogue of Life programme combines the outputs 
of the Species 2000 programme (led by Frank Bisby from Reading, UK) and 
the ITIS programme (led by Thomas Orrell from Washington, USA). Assembly 
and publication of the Annual Checklist is managed by Yuri Roskov in Reading 
working with colleagues around the world:</p><p> &#149; <b>Frank Bisby</b>, Species 
2000 Executive Director and Team member, at the Species 2000 Secretariat, 
Reading, UK.</p><p>&#149; <b>Yuri Roskov</b>, Executive Editor 
at the Species 2000 Secretariat, Reading, 
UK.</p><p>&#149; <b>Thomas Orrell</b>,  
Acting Director, ITIS at the ITIS Secretariat, Smithsonian Institution, Washington DC, USA.</p><p>&#149; <b>David Nicolson</b>,   ITIS Data Development Coordinator, USGS/Smithsonian Institution, Washington DC, USA.</p><p>&#149; <b>Luvie Paglinawan</b>,   Annual Checklist Dataset 
Compiler, at the Species 2000 Philippines Office, WorldFish Center, Los-Ba&ntilde;os, 
Philippines.</p><p>&#149; <b>Nicolas Bailly</b>,   
Species 2000 Philippines Office Manager at the WorldFish Center, Los-Ba&ntilde;os, 
Philippines.</p><p>&#149; <b>Paul Kirk</b>, Taxonomic Editor of Fungi and Chromista sectors at CABI, Egham, UK.</p><p>&#149; <b>Thierry Bourgoin</b>, Museum National d’Histoire Naturelle, Paris, France. </p><p>&#149; <b>Jorrit van Hertum</b>, Software and User Interface 
Programmer at ETI BioInformatics, Amsterdam, The Netherlands.</p>
</td></tr></table>
<table width="100%" border="0" cellspacing="0" cellpadding="10"><tr><td>
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="0">
<tr><td bgcolor="#333366"><img src="images/blank.gif" width="1" height="1" border="0"></td></tr></table>
<?php
	include "info_arrow_buttons.php" ;
?>
</td></tr></table>
</td></tr></table>
</td></tr></table>
</td></tr></table>
</div>
</body>
</html>
