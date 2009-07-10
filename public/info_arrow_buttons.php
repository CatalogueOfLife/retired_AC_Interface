<?php
	$info_pages = array(
	  1 => "info_about_col.php",
	  2 => "info_2009_checklist.php",
	  3 => "info_source_dbs.php",
	  4 => "info_hierarchy.php",
	  5 => "info_copyright.php",
	  6 => "info_how_to_cite.php",
	  7 => "info_web_sites.php",
	  8 => "info_contact_us.php",
	  9 => "info_acknowledgements.php") ;

	$info_pages_names = array(
	  1 => "About the Catalogue of Life",
	  2 => "About the 2009 Annual Checklist",
	  3 => "Source databases",
	  4 => "Towards Management Hierarchy",
	  5 => "Copyrights, reproduction & sale",
	  6 => "How to cite this work",
	  7 => "Web sites",
	  8 => "Contact us",
	  9 => "Acknowledgements") ;

	$current_url = split("/",$_SERVER['PHP_SELF']) ;
	$current_page = $current_url[count($current_url)-1] ;
	$info_page_number = array_search ($current_page,$info_pages) ;
	echo "<br><div align=center>\n" ;
	if ($info_page_number > 1 ) {
		$prev_page = $info_page_number-1 ;
		echo "<input type='button' name='goPrev' class='formbutton' value='<< Previous' onClick=\"JavaScript: document.location.href='$info_pages[$prev_page]'\">" ;
		echo "<img src='images/blank.gif' width='8' height='1' border='0'>" ;
	}
	echo "<select name='select_page' onchange='document.location.href=this.options[this.selectedIndex].value'>" ;
	for ($i = 1; $i < count($info_pages_names)+1; $i++){
		if ($i == $info_page_number) {
			echo "<option selected" ;
		} else {
			echo "<option" ;
		}
		echo " value='$info_pages[$i]'>$info_pages_names[$i]</option>" ;
	}
	echo "</select>" ;
	
	if ($info_page_number < count($info_pages)) {
		$next_page = $info_page_number+1 ;
		echo "<img src='images/blank.gif' width='8' height='1' border='0'>" ;
		echo "<input type='button' name='goNext' class='formbutton' value=' Next >> ' onClick=\"JavaScript: document.location.href='$info_pages[$next_page]'\">" ;
	}
	echo "</div>\n" ;
	echo "<p>&nbsp;</p>\n" ;
?>
