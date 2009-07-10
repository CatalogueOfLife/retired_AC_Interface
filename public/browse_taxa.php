<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2009 Annual Checklist : <?php
	$selected_taxon = "" ;
	$tree_path = "" ;
	
	require_once "connect_to_database.php" ;
	if ( isset($_REQUEST["selected_taxon"]) ) {
		$selected_taxon = $_REQUEST["selected_taxon"] ;
		$tree_path = getTreePath($selected_taxon) ;
	} else if ( isset($_REQUEST["path"]) ) {
		$tree_path = urldecode($_REQUEST["path"]) ;
	}
	$selected_taxon_name = "" ;
	if ($selected_taxon != "") {
		$query = "SELECT `name`, `taxon` FROM `taxa` WHERE `record_id` = '$selected_taxon' " ;
		$result = mysql_query($query) or die (mysql_error());
		if (mysql_num_rows($result) != 0) {
			$row = mysql_fetch_row($result) ;
			$name = $row[0] ;
			$rank = $row[1] ;
			mysql_free_result($result) ;
			if (strpos(strtolower($name), "not assigned") > -1) {
				$selected_taxon_name = "" ;
			} else if (strpos(strtolower($rank), "species") > -1 || strpos(strtolower($rank), "kingdom") > -1) {
				$selected_taxon_name = $name ;
			} else {
				$selected_taxon_name = "$rank $name" ;
			}
		}
	}
	if ($selected_taxon_name == "") {
		echo "Browse taxa" ;
	} else {
		echo $selected_taxon_name ;
	}
?>
</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="en-GB" />
<meta name="keywords" content="<?php echo $selected_taxon_name ?> biodiversity species 2000 itis taxonomy taxa" />
<meta name="description" content="The Species 2000/ITIS Catalogue of Life : 2006 Annual Checklist 
  is a comprehensive index of all known plants, animals, fungi and micro-organisms. 
  It can be used to search multiple databases simultaneously for the scientific name of an 
  organism." />
<meta name="language" content="en-GB" />
<meta name="robots" content="all" />
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000" onload="scrollWindowToSelected(); moveMenu();" onscroll="moveMenu();">
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
                        <p class="formheader" align="center">Browse taxonomic 
                          tree</p>
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
                        <?php

//	function used to determine children and build subtree; this is done recursively
//	level is the current depth of the subtree
//	treestem is used to display the right image (blank or node between children)
//	link stores the current tree path (Animalia,Arthropoda,etc)
//	parents is the path to the selected taxon

	function handleSubtree($level, $treestem, $this_link, $parents) {
		global $selected_taxon,$levels,$count,$selected_row,$show_accepted_names_only;
		$blank = "<img src=\"images/tree/blank.gif\" border=\"0\">";
		$vertline = "<img src=\"images/tree/vertline.gif\" border=\"0\">";
		//	test for root node (parenttaxon = none equals level = 0)
		if (count($parents) == 0 || $level == 0) {
			$parent_id = 0 ;
			$level = 0 ;
		} else {
			$parent_id = $parents[$level];
		}
		
		$level++;
		
		//	collect records of children by taxon name (to display in tree) and id (to create link to BIS record)
		
		$query = "SELECT `name_with_italics`, `taxon`, `name_code`, `record_id`, `is_accepted_name`,  `name`, `LSID` 
							   FROM `taxa` 
							   WHERE `parent_id` = '$parent_id' " .
		  (($show_accepted_names_only === TRUE) ? " AND `is_accepted_name` = 1 " : "") . 
		  "ORDER BY `taxon` != 'Superfamily', INSTR(`name`, 'Not assigned'),`name`";
		$result = mysql_query($query) or die (mysql_error());
		$childnr = 1;
		
		//	process children row by row; 
		//	each row is a node in the tree, each row is displayed in a table inside a table row

		while($row = mysql_fetch_row($result)) {
			$child = $row[0];
			$taxon = $row[1];
			$child_name_code = $row[2];
			$child_id = $row[3];
			$child_is_accepted_name = $row[4];
			$child_name_no_italics=$row[5];
			$lsid=$row[6];
			$lsidlink="";
$requesturi = $_SERVER['PHP_SELF'];
if($_SERVER['QUERY_STRING']>' ')
{
	$requesturi .="?".$_SERVER['QUERY_STRING'];
}

		if(strpos($requesturi,"?")==0)
		{
			$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$requesturi."?showlsid=$lsid";
		}
		else
		{
			$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$requesturi."&showlsid=$lsid";
		}
			if($lsid!="")
			{
				if(isset($_REQUEST["showlsid"]))
				{
					
					$param_pos = strpos($requesturi,"&showlsid=");
					if($param_pos==0)
					{
						$param_pos = strpos($requesturi,"?showlsid=");
						$already_there = substr($requesturi,0,$param_pos);
						$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$already_there."?showlsid=$lsid";
					}
					else
					{
						$already_there = substr($requesturi,0,$param_pos);
						$show_lsid_url = "http://".$_SERVER['HTTP_HOST'].$already_there."&showlsid=$lsid";
					}

					if($lsid==$_REQUEST["showlsid"])
					{
						$lsidlink="<img src=\"images/tree/blank.gif\" height=1 width=4 border=\"0\"><font face=\"sans-serif\"><small><small>$lsid</small></small></font>\n";
					}
					else
					{
						$lsidlink= "<img src=\"images/tree/blank.gif\" height=1 width=4 border=\"0\"><a href=\"$show_lsid_url\" title=\"Display LSID\"><font face=\"sans-serif\"><small><small>LSID</small></small></font></a>";
					}
				}
				else
				{
				$lsidlink= "<img src=\"images/tree/blank.gif\" height=1 width=4 border=\"0\"><a href=\"$show_lsid_url\" title=\"Display LSID\"><font face=\"sans-serif\"><small><small>LSID</small></small></font></a>";
				}
			}

			if ($child_is_accepted_name == 0) {
				$child = "<span style=\"color:red\">$child</child" ;
			}
			//	build main table
			$count++;
			echo "<tr id='tree_row$count'>\n<td>\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n<tr>\n<td>";
			//	draw blank or vertical node based on previous subtrees 
			for($i = 0; $i < $level-1; $i++) {
				echo ($treestem[$i] == "vertline") ? $vertline : $blank;
			}
			//  determine if last branch in tree
			$hassiblings = ($childnr < mysql_num_rows($result));
			$first = "" ;
			if ($childnr == 1) {
				$first = "first" ;
			}
			$last = "";
			if($hassiblings == false) {
				$last = "last";
			}
			//  used to build link to specific record
			$childlink = $childlink2 = "";
			if($child_name_code != "") {
				$query2 = "SELECT `record_id` 
						  FROM `scientific_names` WHERE `name_code` = '" . addslashes($child_name_code) . 
						    "' AND `name_code` LIKE BINARY '" . addslashes($child_name_code) . "' " ;
				$result2 = mysql_query($query2) or die("Error: MySQL query failed");
				if (mysql_num_rows($result2) == 0) {
					$child_record_id = 0 ;
				} else {
					$row2 = mysql_fetch_row($result2);
					$child_record_id = $row2[0] ;
				}
				$childlink = "<a href=\"show_species_details.php?record_id=$child_record_id\">";
				$childlink2 = "</a>"; 
			}
			
			if (strpos(strtolower($child), "inside series of bacteria") === FALSE) {
				if (strpos(strtolower($child), "not assigned") === FALSE) {
				
					if (strpos(strtolower($taxon), "species") > -1 || strpos(strtolower($taxon), "kingdom") > -1) {
						$full_link  = "" ;
					} else {
						$full_link = "$taxon " ;
					}
					$full_link .= "$childlink$child$childlink2" ;
				} else {
					$taxon_lc = strtolower($taxon) ;
					$full_link = $childlink . "Not assigned to a" . 
						((strpos("aehiou",substr($taxon_lc,0,1)) === false) ? " " : "n ") . 
						$taxon_lc . $childlink2 ;
				}
			} else {
				$full_link = "$childlink$child$childlink2" ;
			}
				
			if($selected_taxon == $child_id) {
				$selected_row = $count ;
			}
			
			//  start child belongs to current selection, build subtree
			
			if(isset($parents[$level]) && $parents[$level] == $child_id) {
				$image = "m" . $last. "node.gif" ;
				if ($parent_id == 0) {
					if ($first == "first") {
						$image = "mtopnode.gif" ;
					}
				}
				echo "<a href=\"browse_taxa.php?path=$this_link&amp;selected_taxon=$parent_id\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'>";
				// if selected, display taxon name in bold
				if($selected_taxon == $child_id) {
					echo "<b>$full_link</b></span></td>\n</tr>\n</table>\n</td><td>$lsidlink</td>";
				} else {
					echo "$full_link</span></td>\n</tr>\n</table>\n</td><td>$lsidlink</td>" ;
				}
				echo "\n</tr>\n";
				//  update treestem, if has children add vertline to array, otherwise blank
				array_push($treestem, ($hassiblings) ? "vertline" : "blank");
				handleSubtree($level, $treestem, $this_link.",$child_id", $parents);
			} else { 
				// node is not in selection, so don't build subtree
				
				$query2 = "SELECT `name_with_italics`
						  FROM `taxa`
						  WHERE `parent_id` = '$child_id' " .
		  		  (($show_accepted_names_only === TRUE) ? " AND `is_accepted_name` = 1 " : "") ;
				$result2 = mysql_query($query2) or die (mysql_error());
				$number_of_rows2 = mysql_num_rows($result2) ;
				mysql_free_result($result2) ;
				// test whether this is the last node (no children)		
				if($number_of_rows2 == 0) {
					echo "<img src=\"images/tree/".$last."node.gif\" border=\"0\"></td>\n" ;
					echo "<td><span class='treenode'>$full_link</span></td></tr>\n</table><td>$lsidlink";
				} else {
					$image = "p" . $last. "node.gif" ;
					if ($parent_id == 0) {
						if ($first == "first") {
							$image = "ptopnode.gif" ;
						}
					}
					if($selected_taxon == $child_id )
						// display selected taxon in bold and add anchor

						echo "<a href=\"browse_taxa.php?path={$this_link},{$child_id}&amp;selected_taxon={$child_id}\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'><b>$full_link</b></span></td></tr>\n</table><td>$lsidlink\n";
					else
						echo "<a href=\"browse_taxa.php?path={$this_link},{$child_id}&amp;selected_taxon={$child_id}\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'>$full_link</span></td></tr>\n</table><td>$lsidlink\n";
				}
				echo "\n</td>\n</tr>\n";
			}
			$childnr++;
		}
		
		mysql_free_result($result);
	}
	
	function getTreePath($record_id) {
		global $link ;
		$parents = "" ;
		$found_parent = TRUE ;
		$parent_id = $record_id ;
		while($found_parent != FALSE) {
			$query = "SELECT `parent_id` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
			$result = mysql_query($query) or die("Error: MySQL query failed");
			$row = mysql_fetch_row($result);
			mysql_free_result($result) ;
			if ($row[0] == "") {
				$found_parent = FALSE ;
			} else if ($parents == "") {
				$parents = $row[0] ;
			} else  {
				$parents = $row[0] . "," . $parents;
			}
			$parent_id = $row[0] ;
			if ($row[0] == "0") {
				$found_parent = FALSE ;
			}
		}
		if ($parents != "") {
			$parents .= ",$record_id" ;
		}
		return $parents ;
	}
	
	$levels = array(
	  -1 => "",
	  0 => "Kingdom",
	  1 => "Phylum",
	  2 => "Class",
	  3 => "Order",
	  4 => "Family",
	  5 => "Genus",
	  6 => "Species",
	  7 => "Infraspecies") ;
	$level = 0;
	$treestem = array();
	$parents = array() ;
	$this_link = 0; // root level
	$show_back_link = FALSE ;
	if ($tree_path == "") {
		$parents = Array("0") ;
	} else {
		$parents = split(",", $tree_path);
		$show_back_link = TRUE ;
	}
	
	$count = 0 ;
	
	//if ($selected_taxon != "" && $show_back_link = TRUE && $tree_path == "") {
	//	echo "<p>Error: could not find that taxon in the taxonomic hierarchy.</p>" ;
	//}
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	##echo "<tr><td><img src=images/blank.gif width=505px height=1 border=0></td></tr>";

	handleSubtree($level, $treestem, $this_link, $parents);
	echo "</table>\n";

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "<tr><td><img src=images/blank.gif width=505px height=1 border=0></td></tr>";
	mysql_close($link) ;
	
	if ($show_back_link == TRUE) {
		echo "<p align=\"center\">" ;
		echo "<br />" ;
		echo "<a href=\"JavaScript:history.back()\">Back to last page</a>" ;
		echo "</p>" ;
	}
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
<script type="text/javascript">
	function scrollWindowToSelected() {
		theRowToShow = 
<?php
		global $selected_row;
		if (isset($selected_row)) {
			echo $selected_row;
		} else {
		 echo "\"\"" ;
		}
?>
					;
		type = getBrowserType() ;
		platform = getPlatform() ;
		if (theRowToShow != "" && !(type == "IE" && platform == "macintosh")) {
			if (document.all) {
				eval ("document.all.tree_row" + theRowToShow + ".scrollIntoView()") ;
			} else {
				eval ("document.getElementById('tree_row" + theRowToShow +"').scrollIntoView(true)") ;
			}
			theScroll = 0;
			if (window.pageYOffset) {
				theScroll = window.pageYOffset;
			} else if (window.document.documentElement && window.document.documentElement.scrollTop) {
				theScroll = window.document.body.scrollTop;
			} else if (window.document.body) {
				theScroll = window.document.body.scrollTop;
			}
			theScroll = parseInt(theScroll)-22 ;
			if (theScroll < 0) {
				theScroll = 0 ;
			}
			if (window.scrollTo) {
				window.scrollTo(0,theScroll) ;
			}
		}
	}
</script>
</div>
</body>
</html>
