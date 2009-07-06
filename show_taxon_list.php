<?php
	// show results
	$list_output = "" ;
	if ($show_taxon != "" && $number_of_names == 0) {
		$list_output .= "<table border='0' cellspacing='0' cellpadding='4'>\n" ;
		$list_output .= "<tr>\n<td valign=top>\n" ;
		if ($show_taxon == "kingdom") {
			$list_output .= "<p>No top level groups found<p>\n" ;
		} else {
			$list_output .= "<p>No $show_taxon names found<p>\n" ;
		}
		$list_output .= "</td>\n</tr>\n" ;
		$list_output .= "</table>\n" ;
	} elseif ($number_of_names > 0) {
		if ($selected_letter != "") {
			$list_output .= "<table border='0' cellspacing='0' cellpadding='1' width='96%'>\n" ;
			$list_output .= "<tr>\n" ;
			$list_output .= "<td colspan=27><img src=\"images/blank.gif\" height=\"2px\" width=\"1px\" border=\"0px\"></td>" ;
			$list_output .= "</tr>\n" ;
			$list_output .= "<tr>\n" ;
			for ($i = 0; $i <= 26; $i++) {
				$this_letter = substr("ABCDEFGHIJKLMNOPQRSTUVWXYZ#",$i,1) ;
				$list_output .= "<td>\n" ;
				if ($this_letter == $selected_letter) {
					$list_output .= "<p class='selectedLetter'><u>$this_letter</u></p>\n" ;
				} else {
					$list_output .= "<p class='listlink'><a href=\"JavaScript:selectLetter('$show_taxon','$this_letter')\">$this_letter</a></p>\n" ;
				}
				$list_output .= "</td>\n" ;
			}
			$list_output .= "</tr>\n" ;
			$list_output .= "<tr>\n" ;
			$list_output .= "<td colspan=27><img src=\"images/blank.gif\" height=\"1px\" width=\"1px\" border=\"0px\"></td>" ;
			$list_output .= "</tr>\n" ;
			$list_output .= "</table>\n" ;
		}
		$list_output .= "<div id='nameslayer' style='position:relative; z-index:0; width:259px; height:97px; background-color: #FFFFFF; overflow: auto; border: 1px solid #333366; margin-top: 1px; margin-right: 1px; margin-bottom: 1px; margin-left: 1px;'>\n" ;
		$list_output .= "<div id='nameslayercontents' style='visibility:hidden;'>\n" ;
		$list_output .= "<script language='JavaScript' type='text/javascript'>resizeNamesLayer()</script>\n" ;
		$list_output .= "<table border='0' cellspacing='0' cellpadding='4'>\n" ;
		$list_output .= "<tr>\n<td valign=top>\n" ;
		$list_output .= "<table border='0' cellspacing='0' cellpadding='0'>\n" ;
		
		if ($selected_letter == "") {
			$number_of_names = count($list_of_names) ;
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				$list_output .= "<tr><td class='listlink'>" ;
				$list_output .= "<a id='link_$counter' href='JavaScript:" ;
				$list_output .= "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
				$list_output .= "</td>\n</tr>\n" ;
			}
		} elseif ($selected_letter == "#") {
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				$this_letter = strtoupper(substr(trim($found_name),0,1)) ;
				if (strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ",$this_letter) === FALSE ) {
					$list_output .= "<tr><td class='listlink'>" ;
					$list_output .= "<a id='link_$counter' href='JavaScript:" ;
					$list_output .= "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
					$list_output .= "</td>\n</tr>\n" ;
				}
			}
		} else {
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				if (strtoupper(substr($found_name,0,1)) == $selected_letter) {
					$list_output .= "<tr><td class='listlink'>" ;
					$list_output .= "<a id='link_$counter' href='JavaScript:" ;
					$list_output .= "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
					$list_output .= "</td>\n</tr>\n" ;
				}
			}
			
		}
		$list_output .= "</table>\n" ;
		$list_output .= "</td>\n</tr>\n</table>\n" ;
		$list_output .= "</div>\n" ;
		$list_output .= "</div>\n" ;
	}
	echo $list_output ;
?>
