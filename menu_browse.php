<div id="menu_browse" defaultTop=0 style="position:absolute; z-index:3; left: 67; top:-6; visibility: hidden"> 
<table border="0" cellspacing="0" cellpadding="0"> <tr> <td> </td><td> <table border="0" cellspacing="0" cellpadding="1" bgcolor="#333366"> 
<tr> <td> <?php
	for ($j = 4; $j <= 5; $j++) {
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#FAFCFE\">" ;
		echo "<tr class=\"menulink\" id=\"menu_row$j\" height=\"16px\" " ;
		showMouseUpTag($j,$current_file_name) ; 
		echo " onmouseover=\"selectMenuRow(this.id);\" " ;
		echo " onmouseout=\"deSelectMenuRow(this.id);\">" ;
		echo "<td>" ;
		showMenuLink($j,$current_file_name) ;
		echo "</td>" ;
		echo "</tr>" ;
		echo "</table>" ;
	}
?> </td></tr> </table></td></tr> <tr> <td> <img src="images/blank.gif" width="30" height="1" border="0"> 
</td><td> <img src="images/blank.gif" width="210" height="1" border="0"> 
</td></tr> </table></div>