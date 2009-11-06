checkBrowser();

function getBrowserType() {
	if (navigator.userAgent.indexOf("Opera")!=-1 && document.getElementById) type="OP";		//Opera
	else if (navigator.userAgent.indexOf("Safari")!=-1) type="SA";							//Safari
	else if (navigator.userAgent.indexOf("iCab")!=-1) type="IC";							//iCab
	else if (document.all) type="IE";														//Internet Explorer e.g. IE4 upwards
	else if (document.layers) type="NN";													//Netscape Communicator 4
	else if (!document.all && document.getElementById) type="MO";							//Mozila e.g. Netscape 6 upwards
	else type = "??";		//I assume it will not get here
	return type ;
}

function getPlatform() {
	browserInfo = navigator.userAgent.toLowerCase() ;
	if (browserInfo.indexOf("win")!=-1) {
		platform = "windows" ;
	} else if (browserInfo.indexOf("mac")!=-1) {
		platform = "macintosh" ;
	} else {
		platform = "??" ;
	}
	return platform ;
}

function checkBrowser() {
	// when entering the site, check if proper browser is used
	var referringpage = unescape(document.referrer) ;
	var thispage = unescape(document.location.href) ;
	entering_the_site = true ;
	if (referringpage == thispage) {
		entering_the_site = false ;
	}
	var lastSlash = thispage.lastIndexOf("/") ;
	if (referringpage.substr(0,7) == "http://") {
		if (referringpage.substr(0,lastSlash) == thispage.substr(0,lastSlash)) {
			entering_the_site = false ;
		}
	}
	
	if (entering_the_site == true) {
		//check browser used
		type = getBrowserType() ;
		platform = getPlatform() ;
		version = (navigator.appVersion).substr(0,1) ;
		if (type =="NN" && version < 7) {
			document.location.href = "browser_warning.php" ;
		} else if (type =="MO" && version < 1) {
			document.location.href = "browser_warning.php" ;
		} else if (type =="IE" && platform == "macintosh") {
			document.location.href = "browser_warning.php" ;
		} else if (type != "IE" && type !="NN" && type !="MO" && type !="SA") {
			document.location.href = "browser_warning.php" ;
		} else if (thispage.indexOf(".index.html") != -1 || lastSlash == (thispage.length)-1) {
			document.location.href = "search.php" ;
		}
	}
}

function storeValues() {
	if (document.search_form.search_kingdom) {
		document.show_names.search_kingdom.value = document.search_form.search_kingdom.value ;
	}
	if (document.search_form.search_phylum) {
		document.show_names.search_phylum.value = document.search_form.search_phylum.value ;
	}
	if (document.search_form.search_class) {
		document.show_names.search_class.value = document.search_form.search_class.value ;
	}
	if (document.search_form.search_order) {
		document.show_names.search_order.value = document.search_form.search_order.value ;
	}
	if (document.search_form.search_family) {
		document.show_names.search_family.value = document.search_form.search_family.value ;
	}
	document.show_names.search_genus.value = document.search_form.search_genus.value ; 
	document.show_names.search_species.value = document.search_form.search_species.value ; 
	document.show_names.search_infraspecies.value = document.search_form.search_infraspecies.value ;
}

function showTaxonList(this_taxon) {
	storeValues() ; 
	document.show_names.show_taxon.value=this_taxon ;
	document.show_names.submit();
}

function hideTaxonList() {
	storeValues() ; 
	document.show_names.show_taxon.value='' ;
	document.show_names.submit();
}

function selectLetter(this_taxon,this_letter) {
	storeValues() ; 
	document.show_names.show_taxon.value=this_taxon ;
	document.show_names.selected_letter.value=this_letter ;
	document.show_names.submit();
}

function DeSelectForm(){
	if (document.search_form ) {
		if (document.search_form.search_kingdom) {
			document.search_form.search_kingdom.blur() ;
		}
		if (document.search_form.search_phylum) {
			document.search_form.search_phylum.blur() ;
		}
		if (document.search_form.search_class) {
			document.search_form.search_class.blur() ;
		}
		if (document.search_form.search_order) {
			document.search_form.search_order.blur() ;
		}
		if (document.search_form.search_genus) {
			document.search_form.search_genus.blur() ;
		}
		if (document.search_form.search_species) {
			document.search_form.search_species.blur() ;
		}
		if (document.search_form.search_infraspecies) {
			document.search_form.search_infraspecies.blur() ;
		}
		if (document.search_form.search_simple) {
			document.search_form.search_simple.blur() ;
		}
		if (document.search_form.search_common_name) {
			document.search_form.search_common_name.blur() ;
		}
		if (document.search_form.search_distribution) {
			document.search_form.search_distribution.blur() ;
		}
	}
}

function ShowLayer(id, action){
	if (document.all)  {
		eval("document.all." + id + ".style.visibility='" + action + "'");
	} else if (document.getElementById) {
		eval("document.getElementById('" + id + "').style.visibility='" + action + "'");
	} else {
		eval("document." + id + ".visibility='" + action + "'");
	}
}

function resizeNamesLayer() {
	type = getBrowserType() ;
	platform = getPlatform() ;
	if (platform == "windows" && type == "IE") {
		document.all.nameslayer.style.width = 260 ;
	} else if (platform == "macintosh" && type == "IE") {
		document.all.nameslayer.style.width = 245 ;
	} else if (platform == "macintosh" && (type == "MO")) {
		document.getElementById('nameslayer').style.width = 326 ;
	}
}

function selectTaxon (theName) {
	storeValues() ; 
	var theTaxonShown = document.list_of_names.taxon.value ;
	eval("document.show_names.search_" + theTaxonShown + ".value=unescape('" + theName + "');") ;
	document.show_names.show_taxon.value = '' ;
	eval("document.show_names.select_taxon.value=unescape('" + theTaxonShown + "');") ;
	document.show_names.submit();
}

function showSpeciesDetails(recordID) {
	document.show_species_details.record_id.value = recordID ;
	document.show_species_details.submit() ;
}

function showDatabaseDetails(recordID) {
	document.show_database.database_name.value=recordID ;
	document.show_database.submit() ;
}

function showCommonNameDetails(commonName) {
	if (document.show_common_name.common_name) {
		document.show_common_name.common_name.value=commonName ;
	}
	document.show_common_name.submit() ;
}

function showReferenceDetails(name,genus,species,infraspecies_marker,infraspecies,author,status) {
	if (document.show_reference_details.name) {
		document.show_reference_details.name.value=name ;
	}
	if (document.show_reference_details.genus) {
		document.show_reference_details.genus.value = genus ;
	}
	if (document.show_reference_details.species) {
		document.show_reference_details.species.value = species ;
	}
	if (document.show_reference_details.infraspecies_marker) {
		document.show_reference_details.infraspecies_marker.value = infraspecies_marker ;
	}
	if (document.show_reference_details.infraspecies) {
		document.show_reference_details.infraspecies.value = infraspecies ;
	}
	if (document.show_reference_details.author) {
		document.show_reference_details.author.value = author ;
	}
	if (document.show_reference_details.status) {
		document.show_reference_details.status.value = status ;
	}
	document.show_reference_details.submit() ;
}

function showCommonNameReferenceDetails(referenceID,name) {
	if (document.show_reference_details.reference_id) {
		document.show_reference_details.reference_id.value=referenceID ;
	}
	if (document.show_reference_details.name) {
		document.show_reference_details.name.value=name ;
	}
	document.show_reference_details.submit() ;
	
}

function showTaxonomicTree(selected_taxon) {
	document.show_tree.selected_taxon.value = selected_taxon ;
	document.show_tree.submit() ;
}

function autoComplete(theTaxonToComplete,theKeyCode,ctrlKeyIsDown,altKeyIsDown) {
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
           return true;
           break;
   } 
	var theTaxonShown = document.list_of_names.taxon.value ;
	var browserType = getBrowserType() ;
	if (browserType == "IE") {
		var theField = "document.getElementById('search_" + theTaxonToComplete +"')" ;
	} else if (document.all) {
		var theField = "document.all.search_" + theTaxonToComplete ;
	} else if (document.search_form) {
		var theField = "document.search_form.search_" + theTaxonToComplete ;
	} else  {
		return true;
	}
	theValue = eval (theField +".value") ;
	if ("0123456789".indexOf(theValue.charAt(0)) != -1) {
		//ignoring numeric input
		return true;
	}
	theValue = escape (theValue) ;
	if (theTaxonToComplete == theTaxonShown) {
		var theListOfNames = document.list_of_names.names.value ;
		var theLowerCaseList = theListOfNames.toLowerCase() ;
		var theLowerCaseValue = theValue.toLowerCase() ;
		var theOffSet = theLowerCaseList.indexOf("/" + theLowerCaseValue) ;
		
		if (theOffSet > -1) {
			var thePrecedingList = theListOfNames.substr(0,theOffSet) ;
			var thePrecedingListArray = thePrecedingList.split("/")
			var theRowToShow = thePrecedingListArray.length ;
			
			var theName = theListOfNames.substr(theOffSet+1,50) ;
			var theOffSet = theName.indexOf("/") ;
			if (theOffSet > -1) {
				theName = theName.substr(0,theOffSet) ;
				eval (theField + ".value=unescape(theName)") ;
				var theSelectionStart = unescape(theValue).length ;
				var theSelectionEnd = theName.length ;
				if (document.all) {
					var theRange = eval (theField + ".createTextRange()") ;
               		theRange.moveStart("character", theSelectionStart);
               		theRange.moveEnd("character", theSelectionEnd);
               		theRange.select();
					eval ("var thisRow = document.all.name_" + theRowToShow) ;
					if (thisRow) {
						thisRow.scrollIntoView() ;
					}
				} else {
					eval (theField + ".selectionStart='" + theSelectionStart +"'") ;
					eval (theField + ".selectionEnd='" + theSelectionEnd +"'") ;
					eval ("var thisRow = document.getElementById('name_" + theRowToShow +"')") ;
					if (thisRow) {
						thisRow.scrollIntoView(true) ;
					}
				}
			}
		}
	}
}

function selectMenuRow(theRow) {
   if (document.getElementById) {
      var tr = eval("document.getElementById(\"" + theRow + "\")");
   } else {
      return;
   }
   if (tr.style) {
       tr.style.backgroundColor = "#EAF2F7";
   }
}

function deSelectMenuRow(theRow) {
   if (document.getElementById) {
		var tr = eval("document.getElementById(\"" + theRow + "\")");
		if (tr.style) {
			tr.style.backgroundColor = "";
		}
   }
}

function moveMenu() {
	menuLayer = document.getElementById('menu_layer') ;
	theScroll = 0;
	if (window.pageYOffset) {
		theScroll = window.pageYOffset;
	} else if (window.document.documentElement && window.document.documentElement.scrollTop) {
		theScroll = window.document.body.scrollTop;
	} else if (window.document.body) {
		theScroll = window.document.body.scrollTop;
	}
	var newY = theScroll + "px";
	if (menuLayer) {
		menuLayer.style.top = newY;
		setTimeout("moveMenu()",500);
	}
}

function getSearchMode() {

	if ( document.search_form.find_whole_words ) {
		if ( document.search_form.find_whole_words.checked == true ) {
			document.search_form.search_mode.value = 'whole words' ;
		} else {
			document.search_form.search_mode.value = '' ;
		}
	}
}

function sortByColumn(column) {
	document.sort_by_column.sort_by_column.value = column ;
	document.sort_by_column.submit() ;
}

function showStatus(message) {
    window.status = message ;
    return true ;
}

function insertEmailAddress(a,b) {
	document.write("<a href='mailto:" + a + "@" + b + "'>") ;
	document.write(a + "@" + b + "</a>") ;
}

function newImage(arg) {
	if (document.images) {
		rslt = new Image();
		rslt.src = arg;
		return rslt;
	}
}

function changeImages() {
	if (document.images) {
		for (var i=0; i<changeImages.arguments.length; i+=2) {
			document[changeImages.arguments[i]].src = changeImages.arguments[i+1];
		}
	}
}

function preloadImages() {
	if (document.images) {
		arrow_down_red = newImage("images/arrow_down_red.jpg");
		arrow_up_red = newImage("images/arrow_up_red.jpg");
		arrow_down_mousedown = newImage("images/arrow_down_mousedown.jpg");
		arrow_up_mousedown = newImage("images/arrow_up_mousedown.jpg");
		waitGraphic = newImage("images/wait.gif") ;
	}
}

function showWaitScreen(message) {
	var childWidth = 350 ;
	var childHeight = 125 ;
	
	var topPos = 300, leftPos = 400, parentWidth = 0, parentHeight = 0; // default values
	if (window.screenTop) {
	  topPos = window.screenTop;
	  leftPos = window.screenLeft;
	} else if (window.screenX){
	  topPos = window.screenX;
	  leftPos = window.screenY;
	}
	
	if (window.innerWidth) {
    	parentWidth = window.innerWidth;
    	parentHeight = window.innerHeight;
	} else if ( document.body.clientWidth) {
		parentWidth = document.body.clientWidth;
		parentHeight = document.body.clientHeight;
	}
	
	leftPos = leftPos + (parentWidth/2) - (childWidth/2) ;
	topPos = topPos + (parentHeight/2) - (childHeight/2) ;
	
	browser = getBrowserType() ;
	platform = getPlatform() ;
	if (browser == "Mozilla") {
		topPos += 80 ;
	} else if (browser== "Opera") {
		leftPos = 210 ;
		topPos = 230 ;
	} else if (browser== "Internet Explorer" && platform == "Macintosh") {
		leftPos = 300 ;
		topPos = 300 ;
	} else if (browser== "Safari") {
		topPos -= 50 ;
	}
	
	childWin = window.open('standby.php?msg=' + escape(message), 'baby', 
	  'top='+ topPos +',screenY= '+ topPos +',left=' + leftPos +',screenX=' + leftPos + 
	  ',height=' + childHeight + ',width=' + childWidth + 
	  ',status,titlebar=no,alwaysRaised,dependent,scrollbars=no');
	  
}
