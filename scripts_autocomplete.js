function autoComplete(taxon,fieldValue,theKeyCode,ctrlKeyIsDown,altKeyIsDown) {
	switch (theKeyCode) {
       case 38: //up arrow  
       case 40: //down arrow
       case 37: //left arrow
       case 39: //right arrow
       case 33: //page up  
       case 34: //page down  
       case 36: //home  
       case 35: //end                  
       case 13: //enter  
       case 9: //tab  
       case 27: //esc  
       case 16: //shift  
       case 17: //ctrl  
       case 18: //alt  
       case 20: //caps lock
       case 8: //backspace  
       case 46: //delete
       case 53: // %
       case 56: // *
           return true;
           break;
	} 
	if (getBrowserType() == "SA") {
		return true ;
	}
	if (fieldValue != "" && document.auto_complete) {
		document.auto_complete.old_value.value = fieldValue ;
		searchNames(fieldValue,'',taxon) ;
	}
}

function searchNames(input, response,thisTaxon) {
  if (response != ''){ 
    // Response mode
 
  } else {
    // Input mode
	
	var offSetOfUnderscore = thisTaxon.indexOf("_") ;
	thisTaxon = thisTaxon.substring(offSetOfUnderscore+1) ;
	
	if (document.search_form && document.search_form.kingdom) {
		var kingdom = document.search_form.kingdom.value ;
		var phylum = document.search_form.phylum.value ;
		var order = document.search_form.order.value;
		var tax_class = document.search_form.tax_class.value;
		var superfamily = document.search_form.superfamily.value ;
		var family = document.search_form.family.value ;
		
		kingdom = kingdom.replace("*","%");
		phylum = phylum.replace("*","%");
		tax_class = tax_class.replace("*","%");
		order = order.replace("*","%");
		superfamily = superfamily.replace("*","%");
		family = family.replace("*","%");
	} else {
		var kingdom = "" ;
		var phylum = "" ;
		var tax_class = "" ;
		var order = "" ;
		var superfamily = "" ;
		var family = "" ;
	}
	var genus = document.search_form.genus.value ;
	var species = document.search_form.species.value ;
	var infraspecies = document.search_form.infraspecies.value ;
	
	genus = genus.replace("*","%");
	species = species.replace("*","%");
	
	var page = document.location.href ;
	var lastSlash = page.lastIndexOf("/") ;
	page = page.substring(lastSlash+1) ;
	
	var query = "";
	if (thisTaxon == "kingdom") {
		query = "SELECT `kingdom` FROM `families` WHERE `kingdom` LIKE '" + kingdom + "%' AND `is_accepted_name` = 1" ;
		query += " ORDER BY `kingdom`" ;
	} else if (thisTaxon == "phylum") {
		query = "SELECT `phylum` FROM `families` WHERE `phylum` LIKE '" + phylum + "%' AND `is_accepted_name` = 1" ;
		if (kingdom != "") {
			query += " AND `kingdom` LIKE '" + kingdom + "'" ;
		}
		query += " ORDER BY `phylum`" ;
	} else if (thisTaxon == "class") {
		query = "SELECT `class` FROM `families` WHERE `class` LIKE '" + tax_class + "%' AND `is_accepted_name` = 1" ;
		if (kingdom != "") {
			query += " AND `kingdom` LIKE '" + kingdom + "'" ;
		}
		if (phylum != "") {
			query += " AND `phylum` LIKE '" + phylum + "'" ;
		}
		query += " ORDER BY `class`" ;
	} else if (thisTaxon == "order") {
		query = "SELECT `order` FROM `families` WHERE `order` LIKE '" + order + "%' AND `is_accepted_name` = 1" ;
		if (kingdom != "") {
			query += " AND `kingdom` LIKE '" + kingdom + "'" ;
		}
		if (phylum != "") {
			query += " AND `phylum` LIKE '" + phylum + "'" ;
		}
		if (tax_class != "") {
			query += " AND `class` LIKE '" + tax_class + "'" ;
		}
		query += " ORDER BY `order`" ;
	} else if (thisTaxon == "superfamily") {
		query = "SELECT `superfamily` FROM `families` WHERE `superfamily` LIKE '" + superfamily + "%' AND `is_accepted_name` = 1" ;
		if (kingdom != "") {
			query += " AND `kingdom` LIKE '" + kingdom + "'" ;
		}
		if (phylum != "") {
			query += " AND `phylum` LIKE '" + phylum + "'" ;
		}
		if (tax_class != "") {
			query += " AND `class` LIKE '" + tax_class + "'" ;
		}
		if (order != "") {
			query += " AND `order` LIKE '" + order + "'" ;
		}
		query += " ORDER BY `superfamily`" ;
	} else if (thisTaxon == "family") {
		query = "SELECT `family` FROM `families` WHERE `family` LIKE '" + family + "%' AND `is_accepted_name` = 1" ;
		if (kingdom != "") {
			query += " AND `kingdom` LIKE '" + kingdom + "'" ;
		}
		if (phylum != "") {
			query += " AND `phylum` LIKE '" + phylum + "'" ;
		}
		if (tax_class != "") {
			query += " AND `class` LIKE '" + tax_class + "'" ;
		}
		if (order != "") {
			query += " AND `order` LIKE '" + order + "'" ;
		}
		if (superfamily != "") {
			query += " AND `superfamily` LIKE '" + superfamily + "'" ;
		}
		query += " ORDER BY `family`" ;
	} else if (thisTaxon == "genus") {
		if (kingdom + phylum + tax_class + order + superfamily + family != "") {
			query = "SELECT `scientific_names`.`genus` FROM `scientific_names`,`families` WHERE `scientific_names`.`genus` LIKE '" + genus + "%' AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if (page == "browse_by_classification.php") {
				query += " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
			if (kingdom != "") {
				query += " AND `families`.`kingdom` LIKE '" + kingdom + "'" ;
			}
			if (phylum != "") {
				query += " AND `families`.`phylum` LIKE '" + phylum + "'" ;
			}
			if (tax_class != "") {
				query += " AND `families`.`class` LIKE '" + tax_class + "'" ;
			}
			if (order != "") {
				query += " AND `families`.`order` LIKE '" + order + "'" ;
			}
			if (superfamily != "") {
				query += " AND `families`.`superfamily` LIKE '" + superfamily + "'" ;
			}
			if (family != "") {
				query += " AND `families`.`family` LIKE '" + family + "'" ;
			}
			query += " ORDER BY `scientific_names`.`genus`" ;
		} else {
			query = "SELECT `name` FROM `hard_coded_taxon_lists` WHERE `rank` = 'genus' AND `name` LIKE '" + genus + "%'" ;
			if (page == "browse_by_classification.php") {
				query += " AND `accepted_names_only` = 1 " ;
			} else {
				query += " AND `accepted_names_only` = 0 " ;
			}
		}
	} else if (thisTaxon == "species") {
		if (kingdom + phylum + tax_class + order + family != "") {
			query = "SELECT `scientific_names`.`species` FROM `scientific_names`,`families` WHERE `scientific_names`.`species` LIKE '" + species + "%' AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if (page == "browse_by_classification.php") {
				query += " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
			if (kingdom != "") {
				query += " AND `families`.`kingdom` LIKE '" + kingdom + "'" ;
			}
			if (phylum != "") {
				query += " AND `families`.`phylum` LIKE '" + phylum + "'" ;
			}
			if (tax_class != "") {
				query += " AND `families`.`class` LIKE '" + tax_class + "'" ;
			}
			if (order != "") {
				query += " AND `families`.`order` LIKE '" + order + "'" ;
			}
			if (superfamily != "") {
				query += " AND `families`.`superfamily` LIKE '" + superfamily + "'" ;
			}
			if (family != "") {
				query += " AND `families`.`family` LIKE '" + family + "'" ;
			}
			if (genus != "") {
				query += " AND `scientific_names`.`genus` LIKE '" + genus + "'" ;
			}
			query += " ORDER BY `scientific_names`.`species`" ;
		} else {
			if (genus == "") {
				query = "SELECT `name` FROM `hard_coded_taxon_lists` WHERE `rank` = 'species' AND `name` LIKE '" + species + "%'" ;
				if (page == "browse_by_classification.php") {
					query += " AND `accepted_names_only` = 1 " ;
				} else {
					query += " AND `accepted_names_only` = 0 " ;
				}
			} else {
				query = "SELECT `species` FROM `scientific_names` WHERE `species` LIKE '" + species + "%' " ;
				if (page == "browse_by_classification.php") {
					query += " AND `scientific_names`.`is_accepted_name` = 1 " ;
				}
				if (genus != "") {
					query += " AND `genus` LIKE '" + genus + "'" ;
				}
				query += " ORDER BY `species`" ;
			}
		}
	} else if (thisTaxon == "infraspecies") {
		if (kingdom + phylum + tax_class + order + family != "") {
			query = "SELECT `scientific_names`.`infraspecies` FROM `scientific_names`,`families` WHERE `scientific_names`.`infraspecies` LIKE '" + infraspecies + "%' AND `scientific_names`.`family_id` = `families`.`record_id` " ;
			if (page == "browse_by_classification.php") {
				query += " AND `scientific_names`.`is_accepted_name` = 1 " ;
			}
			if (kingdom != "") {
				query += " AND `families`.`kingdom` LIKE '" + kingdom + "'" ;
			}
			if (phylum != "") {
				query += " AND `families`.`phylum` LIKE '" + phylum + "'" ;
			}
			if (tax_class != "") {
				query += " AND `families`.`class` LIKE '" + tax_class + "'" ;
			}
			if (order != "") {
				query += " AND `families`.`order` LIKE '" + order + "'" ;
			}
			if (superfamily != "") {
				query += " AND `families`.`superfamily` LIKE '" + superfamily + "'" ;
			}
			if (family != "") {
				query += " AND `families`.`family` LIKE '" + family + "'" ;
			}
			if (genus != "") {
				query += " AND `scientific_names`.`genus` LIKE '" + genus + "'" ;
			}
			if (species != "") {
				query += " AND `scientific_names`.`species` LIKE '" + species + "'" ;
			}
			query += " ORDER BY `scientific_names`.`infraspecies`" ;
		} else {
			if (genus + species == "") {
				query = "SELECT `name` FROM `hard_coded_taxon_lists` WHERE `rank` = 'infraspecies' AND `name` LIKE '" + infraspecies + "%'" ;
				if (page == "browse_by_classification.php") {
					query += " AND `accepted_names_only` = 1 " ;
				} else {
					query += " AND `accepted_names_only` = 0 " ;
				}
			} else {
				query = "SELECT `infraspecies` FROM `scientific_names` WHERE `infraspecies` LIKE '" + infraspecies + "%' " ;
				if (page == "browse_by_classification.php") {
					query += " AND `scientific_names`.`is_accepted_name` = 1 " ;
				}
				if (genus != "") {
					query += " AND `genus` LIKE '" + genus + "'" ;
				}
				if (species != "") {
					query += " AND `species` LIKE '" + species + "'" ;
				}
				query += " ORDER BY `infraspecies`" ;
			}
		}
	}
	
	if (query != "") {
	
//window.status = query ;
		query += " LIMIT 0,1" ;
		var url = "classification_autocomplete.php?q=" + escape(query) ;
		loadXMLDoc(url);
	 }
  }
}

var req;
function loadXMLDoc(url) {
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
        req.onreadystatechange = processReqChange;
        req.open("GET", url, true);
        req.send(null);
    } else if (window.ActiveXObject) {
  	  // branch for IE/Windows ActiveX version
        req = new ActiveXObject("Microsoft.XMLHTTP");
        if (req) {
            req.onreadystatechange = processReqChange;
            req.open("GET", url, true);
            req.send();
        }
    }
}

function processReqChange()  {
	if (getBrowserType() == "SA") {
		return true ;
	}
    // only if req shows "complete";
    if (req.readyState == 4) {
        // only if "OK"
        if (req.status == 200) {
            
			// ...processing statements go here...
			
			response  = req.responseXML.documentElement;
			if (response) {
				if (response.getElementsByTagName('record')[0]) {
					first_hit = response.getElementsByTagName('record')[0].firstChild.data;
				} else {
					first_hit = "" ;
				}
				if  (first_hit != "" && first_hit != "-") {
					showSuggestion(first_hit) ;
				}
			}
		}
    }
}

function setSelectedTaxon(thisTaxon) {
	document.auto_complete.selected_taxon.value = thisTaxon ;
}

function showSuggestion(suggested_name) {
	var selected_taxon = document.auto_complete.selected_taxon.value ;
	var theField = "document.search_form." + selected_taxon ;
	var old_value = document.auto_complete.old_value.value ;
	var new_value = eval (theField + ".value") ;
	
	if (old_value == suggested_name || old_value != new_value) {
		return true;
	}
	
	var number_of_names = document.taxon_list.number_of_names_shown.value ;
	
	var new_value = eval (theField + ".value") ;
	if (old_value != new_value) {
		return true;
	}
	if (old_value == suggested_name || old_value != new_value) {
		return true;
	}
	
	eval (theField + ".value = suggested_name") ;
	selectRow("") ;
	
	var theSelectionStart = old_value.length ;
	var theSelectionEnd = suggested_name.length ;
	if (document.all) {
		var theRange = eval (theField + ".createTextRange()") ;
		theRange.moveStart("character", theSelectionStart);
		theRange.moveEnd("character", theSelectionEnd);
		theRange.select();
	} else {
		eval (theField + ".selectionStart='" + theSelectionStart +"'") ;
		eval (theField + ".selectionEnd='" + theSelectionEnd +"'") ;
	}
}

function selectRow(selected_taxon) {
	if (getBrowserType() == "SA") {
		return true ;
	}
 	if (selected_taxon == "") {
		var selected_taxon = document.auto_complete.selected_taxon.value ;
	}
	if (selected_taxon == "class") {
		selected_taxon = "tax_class" ;
	}
	var theField = "document.search_form." + selected_taxon ;
	var theRowToShow = 0 ;
	theValue = eval (theField +".value") ;
	if ("0123456789".indexOf(theValue.charAt(0)) != -1) {
		//ignoring numeric input
		return true ;
	}
	
	theValue = escape (theValue) ;
	var theListOfNames = document.list_of_names.names.value ;
	var theLowerCaseList = theListOfNames.toLowerCase() ;
	var theLowerCaseValue = theValue.toLowerCase() ;
	var theOffSet = theLowerCaseList.indexOf("/" + theLowerCaseValue) ;
	
	if (theOffSet > -1) {
		var thePrecedingList = theListOfNames.substr(0,theOffSet) ;
		var theLastSlashOffSet = thePrecedingList.lastIndexOf("/") ;
		var theRowToShow = (thePrecedingList.substr(theLastSlashOffSet+1,10)-0)+1 ;
		var theName = theListOfNames.substr(theOffSet+1,50) ;
		var theOffSet = theName.indexOf("/") ;
		
		if (theOffSet > -1) {
			if (document.all) {
				eval ("var thisRow = document.all.link_" + theRowToShow) ;
			} else {
				eval ("var thisRow = document.getElementById('link_" + theRowToShow +"')") ;
			}
			if (thisRow) {
				thisRow.scrollIntoView(true) ;
			}
		}
	}
}