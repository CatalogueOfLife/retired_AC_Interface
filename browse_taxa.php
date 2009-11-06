<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist : Browse taxa</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="sp2000ac.css" type="text/css">
<script type="text/javascript">
	function storeTree(link, selected_taxon, collapsed) {
		document.store_path.path.value = link ;
		document.store_path.selected_taxon.value = selected_taxon ;
		document.store_path.submit() ;
	}
</script>
</head>

<body bgcolor="#FFFFFF" text="#000000" onLoad="scrollWindowToSelected(); moveMenu();" onScroll="moveMenu();">
<div style="margin-top:27px; margin-bottom:18px"><img src="images/banner.gif" width="760" height="100"> </div>
<div style="margin-left: 15px; margin-right:15px;">
<table border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td valign=top> 
      <?php
	include "menu.php" ;
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
		global $selected_taxon,$levels,$count,$selected_row;
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
		
		$query = "SELECT `name_with_italics`, `taxon`, `name_code`, `record_id`
							   FROM `taxa` 
							   WHERE `parent_id` = '$parent_id' AND `is_accepted_name` = 1
							   ORDER BY INSTR(`name`, 'Not assigned'),`name`";
		$result = mysql_query($query) or die (mysql_error());
		$childnr = 1;
		
		//	process children row by row; 
		//	each row is a node in the tree, each row is displayed in a table inside a table row

		while($row = mysql_fetch_row($result)) {
			$child = $row[0];
			$taxon = $row[1];
			$child_name_code = $row[2];
			$child_id = $row[3];
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
				// setup link to info page in BIS (if available)
				$childlink = "<a href=\"show_species_details.php?name_code=$child_name_code\">";
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
				echo "<a href=\"JavaScript:storeTree('$this_link','$parent_id')\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'>";
				// if selected, display taxon name in bold
				if($selected_taxon == $child_id) {
					echo "<b>$full_link</b></span>";
				} else {
					echo "$full_link</span>" ;
				}
				echo "</td>\n</tr>\n</table>\n</td>\n</tr>\n";
				//  update treestem, if has children add vertline to array, otherwise blank
				array_push($treestem, ($hassiblings) ? "vertline" : "blank");
				handleSubtree($level, $treestem, $this_link.",$child_id", $parents);
			} else { 
				// node is not in selection, so don't build subtree
				
				$query2 = "SELECT `name_with_italics`
						  FROM `taxa`
						  WHERE `parent_id` = '$child_id' AND `is_accepted_name` = 1" ;
				$result2 = mysql_query($query2) or die (mysql_error());
				$number_of_rows2 = mysql_num_rows($result2) ;
				mysql_free_result($result2) ;
				// test whether this is the last node (no children)		
				if($number_of_rows2 == 0) {
					echo "<img src=\"images/tree/".$last."node.gif\" border=\"0\"></td>\n" ;
					echo "<td><span class='treenode'>$full_link</span></td>\n";
				} else {
					$image = "p" . $last. "node.gif" ;
					if ($parent_id == 0) {
						if ($first == "first") {
							$image = "ptopnode.gif" ;
						}
					}
					if($selected_taxon == $child_id )
						// display selected taxon in bold and add anchor
						echo "<a href=\"JavaScript:storeTree('$this_link,$child_id','$child_id')\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'><b>$full_link</b></span></td>\n";
					else
						echo "<a href=\"JavaScript:storeTree('$this_link,$child_id','$child_id')\"><img src=\"images/tree/$image\" border=\"0\"></a></td>\n<td><span class='treenode'>$full_link</span></td>\n";
				}
				echo "</tr>\n</table>\n</td>\n</tr>\n";
			}
			$childnr++;
		}
		
		mysql_free_result($result);
	}
	
	function getTreePath($record_id) {
		$parents = "" ;
		$found_parent = TRUE ;
		$parent_id = $record_id ;
		while($found_parent != FALSE) {
			$query = "SELECT `parent_id` FROM `taxa` WHERE `record_id` = '$parent_id'" ;
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
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
	
	include "connect_to_database.php" ;
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
	$tree_path = "" ;
	$show_back_link = FALSE ;
	$selected_taxon = "" ;
	if ( isset($_REQUEST["selected_taxon"]) ) {
		$selected_taxon = $_REQUEST["selected_taxon"] ;
		$tree_path = getTreePath($selected_taxon) ;
	} else if ( isset($_REQUEST["path"]) ) {
		$tree_path = urldecode($_REQUEST["path"]) ;
	}
	if ($tree_path == "") {
		$parents = Array("0") ;
	} else {
		$parents = split(",", $tree_path);
		$show_back_link = TRUE ;
	}
	if ( isset($_REQUEST["selected_taxon"]) ) {
		$selected_taxon = urldecode($_REQUEST["selected_taxon"]) ;
	}
	
	$count = 0 ;
	
	//if ($selected_taxon != "" && $show_back_link = TRUE && $tree_path == "") {
	//	echo "<p>Error: could not find that taxon in the taxonomic hierarchy.</p>" ;
	//}
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "<tr><td><img src=images/blank.gif width=505px height=1 border=0></td></tr>";

	handleSubtree($level, $treestem, $this_link, $parents);
	echo "</table>\n";
	mysql_close($link) ;
	
	if ($show_back_link == TRUE) {
		echo "<p align=center>" ;
		echo "<br>" ;
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referring_page = $_SERVER['HTTP_REFERER'] ;
		} else {
			$referring_page = "" ;
		}
		if (strpos($referring_page,"search_results") > 0) {
			echo "<a href='JavaScript:history.back()'>Back to search results</a>" ;
		} else if (strpos($referring_page,"show_species_details") > 0) {
			echo "<a href='JavaScript:history.back()'>Back to species details</a>" ;
		} else if (strpos($referring_page,"browse_taxa") <= 0) {
			echo "<a href='JavaScript:history.back()'>Back to last page</a>" ;
		}
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
<form name="store_path" method="post" action="browse_taxa.php">
<input type="hidden" name="path" value="">
<input type="hidden" name="selected_taxon" value="">
<input type="hidden" name="name" value="">
</form>
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
