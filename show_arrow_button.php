<?php
	if (isset($show_taxon) && isset($this_taxon)) {
		$arrow_button_id =  $this_taxon . "_arrow" ;
		if ($show_taxon != $this_taxon) {
			echo "<a href='JavaScript: showTaxonList(\"$this_taxon\")' " ;
			echo "onmouseover='changeImages(\"$arrow_button_id\", \"images/arrow_down_red.jpg\")' " ;
			echo "onmouseout='changeImages(\"$arrow_button_id\", \"images/arrow_down.jpg\")' " ;
			echo "onMouseDown='changeImages(\"$arrow_button_id\", \"images/arrow_down_mousedown.jpg\")' " ;
			echo "onMouseUp='changeImages(\"$arrow_button_id\", \"images/arrow_down.jpg\")'>" ;
			echo "<img name='$arrow_button_id' src='images/arrow_down.jpg' width='17' height='18' border='0' alt='Show the list of available $this_taxon names'></a>" ;
		}  else {
			echo "<a href='JavaScript: hideTaxonList();'" ;
			echo "onmouseover='changeImages(\"$arrow_button_id\", \"images/arrow_up_red.jpg\")' " ;
			echo "onmouseout='changeImages(\"$arrow_button_id\", \"images/arrow_up.jpg\")' " ;
			echo "onMouseDown='changeImages(\"$arrow_button_id\", \"images/arrow_up_mousedown.jpg\")' " ;
			echo "onMouseUp='changeImages(\"$arrow_button_id\", \"images/arrow_up.jpg\")'>" ;
			echo "<img name='$arrow_button_id' src='images/arrow_up.jpg' width='17' height='18' border='0' alt='Hide the list of available $this_taxon names'></a>" ;
		}
	}
?>