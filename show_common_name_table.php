	<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#D8D8D8">
	  <tr> 
		<td> 
		  <table border="0" cellspacing="0" cellpadding="3" width="100%">
			<tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
			  <td valign=top width="180px"> 
				<p class="fieldheader">Common name:</p>
			  </td>
			  
          <td valign=top> 
            <p class="fieldvalue"> 
              <?php echo $reference . $name ?>
            </p>
			  </td>
			</tr>
			<tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
			  <td valign=top width="180px"> 
				<p class="fieldheader">Language:</p>
			  </td>
			  <td valign=top> 
				<p class="fieldvalue"> 
				  <?php echo $language ?>
				</p>
			  </td>
			</tr>
			<tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
			  <td valign=top width="180px"> 
				<p class="fieldheader">Country:</p>
			  </td>
			  <td valign=top> 
				<p class="fieldvalue"> 
				  <?php echo $country ?>
				</p>
			  </td>
			</tr>
			<tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
			  <td valign=top width="180px"> 
				
            <p class="fieldheader">Accepted scientific name:</p>
			  </td>
			  <td valign=top> 
				<p class="fieldvalue"> 
				  <?php echo $scientific_name ?>
				</p>
			  </td>
			</tr>
			<tr bgcolor="<?php $table_row_color=getTableRowColor($table_row_color) ; ?>"> 
			  <td valign=top width="180px"> 
				
            <p class="fieldheader">Source database:</p>
			  </td>
			  <td valign=top> 
				<p class="fieldvalue"> 
				  <?php echo $database ?>
				</p>
			  </td>
			</tr>
		  </table>
		</td>
	  </tr>
	</table>