<script language="JavaScript" src="scripts.js" type="text/javascript">
</script>

<?php
	$current_url = split("/",$_SERVER['PHP_SELF']) ;
	$current_file_name = $current_url[count($current_url)-1] ;
	
	function getLinkNames() {
		return array(
		   1 => "Browse",
		   2 => "Search",
		   3 => "Info",
		   4 => "Taxonomic tree",
		   5 => "Taxonomic classification",
		   6 => "Search all names",
		   7 => "Search for scientific names",
		   8 => "Search for common names",
		   9 => "Search by distribution",
		  10 => "About the Catalogue of Life",
		  11 => "Source databases",
		  12 => "Copyrights, reproduction & sale",
		  13 => "How to cite this work",
		  14 => "Web sites",
		  15 => "Contact us",
		  16 => "Project team",
		  17 => "Sponsors and contributors") ;
	}
	
	function getLinkUrls() {
		return array(
		   1 => "browse_taxa.php",
		   2 => "search.php",
		   3 => "info_about_col.php",
		   4 => "browse_taxa.php",
		   5 => "browse_by_classification.php?show_taxon=kingdom",
		   6 => "search.php",
		   7 => "search_scientific.php",
		   8 => "search_by_common_name.php",
		   9 => "search_by_distribution.php",
		  10 => "info_about_col.php",
		  11 => "info_source_dbs.php",
		  12 => "info_copyright.php",
		  13 => "info_how_to_cite.php",
		  14 => "info_web_sites.php",
		  15 => "info_contact_us.php",
		  16 => "info_project_team.php",
		  17 => "info_sponsors.php") ;
	}
	
	function getIcons() {
		return array(
		   1 => "images/icon_mini_folder.gif",
		   2 => "images/icon_mini_search.gif",
		   3 => "images/icon_mini_faq.gif") ;
	}
	
	function showMouseUpTag($thisMenuItem,$current_file_name) {
		$link_urls = getLinkUrls();
		$link_url = $link_urls[$thisMenuItem] ;
		if ($thisMenuItem > 3 && $link_url != $current_file_name) {
			echo " onMouseUp=\"document.location.href='$link_url'\" " ;
		}
	}
	
	function showMenuLink($thisMenuItem,$current_file_name) {
		$link_urls = getLinkUrls();
		$link_url = $link_urls[$thisMenuItem] ;
		$link_names = getLinkNames();
		$link_name = $link_names[$thisMenuItem] ;
		
		$link_url_without_query_string = $link_url ;
		if ( strpos($link_url_without_query_string ,"?") > 0) {
			$link_url_without_query_string = substr($link_url_without_query_string,0,strpos($link_url_without_query_string ,"?")) ;
		}
		
		if ($link_url_without_query_string == $current_file_name) {
			echo "<span class='menuselected'>$link_name</span>" ;
		} else if ($thisMenuItem > 3) {
			echo "<span class='menulink'><a style='cursor:default;' href='$link_url'>$link_name</a></span>" ;
		} else if ( ($thisMenuItem == 1 && substr($current_file_name,0,6) == "browse") ||
					($thisMenuItem == 2 && substr($current_file_name,0,6) == "search") || 
					($thisMenuItem == 3 && substr($current_file_name,0,4) == "info") ) {
			$current_url = split("/",$_SERVER['PHP_SELF']) ;
			$current_file_name = $current_url[count($current_url)-1] ;
			echo "<span class='menuselected' onMouseUp=\"document.location.href='$link_url'\">$link_name</span>" ;
		} else {
			echo "<span class='menumain' onMouseUp=\"document.location.href='$link_url'\">$link_name</span>" ;
		}
	}
	
	function showIcon($thisMenuItem) {
		$icon_links = getIcons() ;
		$icon = "<img src='" . $icon_links[$thisMenuItem] . "' hspace='2' width='13' height='13' border='0'>" ;
		$link_urls = getLinkUrls();
		$link_name = $link_urls[$thisMenuItem] ;
		$current_url = split("/",$_SERVER['PHP_SELF']) ;
		$current_file_name = $current_url[count($current_url)-1] ;
		if ($link_name != $current_file_name) {
			echo "<a style='cursor: default;' href='$link_name'>$icon</a>" ;
		} else {
			echo $icon ;
		}
	}
?>
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
<div id="menu_layer" style="position:relative">
<table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366" width="100px">
  <tr> 
    <td> 
<table border="0" cellspacing="0" cellpadding="0" width="100px" bgcolor="#FAFCFE">
  <tr height=2> 
    <td> 
    </td> 
  </tr> 
  <tr> 
    <td> 
	<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#FAFCFE">
	<tr id="menu_row1" height="18px" <?php showMouseUpTag(1,$current_file_name) ; ?>
 onMouseOver="selectMenuRow(this.id); DeSelectForm(); ShowLayer('menu_browse','visible'); " 
 onMouseOut="deSelectMenuRow(this.id); ShowLayer('menu_browse','hidden');">
		<td width=15>
<?php
	showIcon(1) ;
?>
		</td>
		<td>
<?php
		showMenuLink(1,$current_file_name) ;
		include "menu_browse.php" ;

?>
		</td>
		<td align="left" width="8">
		<img src="images/menu_arrow.gif" width="4" height="13" border="0">
		</td>
	</tr>
	<tr id="menu_row2" height="18px" <?php showMouseUpTag(2,$current_file_name) ; ?>
 onMouseOver="selectMenuRow(this.id); DeSelectForm(); ShowLayer('menu_search','visible'); " 
 onMouseOut="deSelectMenuRow(this.id); ShowLayer('menu_search','hidden');">
		<td width=15>
<?php
	showIcon(2) ;
?>
		</td>
		<td>
<?php
		showMenuLink(2,$current_file_name) ;
		include "menu_search.php" ;

?>
		</td>
		<td align="left" width="8">
		<img src="images/menu_arrow.gif" width="4" height="13" border="0">
		</td>
	</tr>
	<tr id="menu_row3" height="18px" <?php showMouseUpTag(3,$current_file_name) ; ?>
 onMouseOver="selectMenuRow(this.id); DeSelectForm(); ShowLayer('menu_info','visible'); " 
 onMouseOut="deSelectMenuRow(this.id); ShowLayer('menu_info','hidden');">
		<td width=15>
<?php
	showIcon(3) ;
?>		</td>
		<td>
<?php
		showMenuLink(3,$current_file_name) ;
		include "menu_info.php" ;

?>
		</td>
		<td align="left" width="8">
		<img src="images/menu_arrow.gif" width="4" height="13" border="0">
		</td>
	</tr>
</table>
    </td>
  </tr>
</table>
    </td>
  </tr>
</table>
</div>
<img src="images/blank.gif" width="100" height="1" border="0">