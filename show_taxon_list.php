<?php
	// show results
	if ($show_taxon != "" && $number_of_names == 0) {
		echo "<table border='0' cellspacing='0' cellpadding='4'>\n" ;
		echo "<tr>\n<td valign=top>\n" ;
		if ($show_taxon == "kingdom") {
			echo "<p>No top level groups found<p>\n" ;
		} else {
			echo "<p>No $show_taxon names found<p>\n" ;
		}
		echo "</td>\n</tr>\n" ;
		echo "</table>\n" ;
	} elseif ($number_of_names > 0) {
		if ($selected_letter != "") {
			echo "<table border='0' cellspacing='0' cellpadding='1' width='96%'>\n" ;
			echo "<tr>\n" ;
			echo "<td colspan=27><img src=\"images/blank.gif\" height=\"2px\" width=\"1px\" border=\"0px\"></td>" ;
			echo "</tr>\n" ;
			echo "<tr>\n" ;
			for ($i = 0; $i <= 26; $i++) {
				$this_letter = substr("ABCDEFGHIJKLMNOPQRSTUVWXYZ#",$i,1) ;
				echo "<td>\n" ;
				if ($this_letter == $selected_letter) {
					echo "<p class='selectedLetter'><u>$this_letter</u></p>\n" ;
				} else {
					echo "<p class='listlink'><a href=\"JavaScript:selectLetter('$show_taxon','$this_letter')\">$this_letter</a></p>\n" ;
				}
				echo "</td>\n" ;
			}
			echo "</tr>\n" ;
			echo "<tr>\n" ;
			echo "<td colspan=27><img src=\"images/blank.gif\" height=\"1px\" width=\"1px\" border=\"0px\"></td>" ;
			echo "</tr>\n" ;
			echo "</table>\n" ;
		}
		echo "<div id='nameslayer' style='position:relative; z-index:0; width:259px; height:97px; background-color: #FFFFFF; overflow: auto; border: 1px solid #333366; margin-top: 1px; margin-right: 1px; margin-bottom: 1px; margin-left: 1px;'>\n" ;
		echo "<div id='nameslayercontents' style='visibility:hidden;'>\n" ;
		echo "<script language='JavaScript' type='text/javascript'>resizeNamesLayer()</script>\n" ;
		echo "<table border='0' cellspacing='0' cellpadding='4'>\n" ;
		echo "<tr>\n<td valign=top>\n" ;
		echo "<table border='0' cellspacing='0' cellpadding='0'>\n" ;
		
		if ($selected_letter == "") {
			$number_of_names = count($list_of_names) ;
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				echo "<tr><td class='listlink'>" ;
				echo "<a id='link_$counter' href='JavaScript:" ;
				echo "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
				echo "</td>\n</tr>\n" ;
			}
		} elseif ($selected_letter == "#") {
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				$this_letter = strtoupper(substr(trim($found_name),0,1)) ;
				if (strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ",$this_letter) === FALSE ) {
					echo "<tr><td class='listlink'>" ;
					echo "<a id='link_$counter' href='JavaScript:" ;
					echo "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
					echo "</td>\n</tr>\n" ;
				}
			}
		} else {
			for ($i = 0; $i < $number_of_names ; $i++) {
				$found_name = $list_of_names[$i] ;
				$counter = $i + 1 ;
				if (strtoupper(substr($found_name,0,1)) == $selected_letter) {
					echo "<tr><td class='listlink'>" ;
					echo "<a id='link_$counter' href='JavaScript:" ;
					echo "selectTaxon($counter);'>" . stripslashes($found_name) . "</a>" ;
					echo "</td>\n</tr>\n" ;
				}
			}
			
		}
		echo "</table>\n" ;
		echo "</td>\n</tr>\n</table>\n" ;
		echo "</div>\n" ;
		echo "</div>\n" ;
	}
?>
