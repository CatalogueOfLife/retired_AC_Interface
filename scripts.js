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

function showBackLink() {
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
	if (entering_the_site == false) {
		document.write ("<a href=\"JavaScript:history.back()\">Back to last page</a> | ") ;
	}
}

function storeValues() {
	if (document.search_form && document.show_names) {
		if (document.search_form.kingdom && document.show_names.kingdom) {
			document.show_names.kingdom.value = document.search_form.kingdom.value ;
		}
		if (document.search_form.phylum && document.show_names.phylum) {
			document.show_names.phylum.value = document.search_form.phylum.value ;
		}
		if (document.search_form.tax_class && document.show_names.tax_class) {
			document.show_names.tax_class.value = document.search_form.tax_class.value ;
		}
		if (document.search_form.order && document.show_names.order) {
			document.show_names.order.value = document.search_form.order.value ;
		}
		if (document.search_form.superfamily && document.show_names.superfamily) {
			document.show_names.superfamily.value = document.search_form.superfamily.value ;
		}
		if (document.search_form.family && document.show_names.family) {
			document.show_names.family.value = document.search_form.family.value ;
		}
		if (document.search_form.genus && document.show_names.genus) {
			document.show_names.genus.value = document.search_form.genus.value ;
		}
		if (document.search_form.species && document.show_names.species) {
			document.show_names.species.value = document.search_form.species.value ;
		}
		if (document.search_form.infraspecies && document.show_names.infraspecies) {
			document.show_names.infraspecies.value = document.search_form.infraspecies.value ;
		}
	}
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
		if (document.search_form.kingdom) {
			document.search_form.kingdom.blur() ;
		}
		if (document.search_form.phylum) {
			document.search_form.phylum.blur() ;
		}
		if (document.search_form.tax_class) {
			document.search_form.tax_class.blur() ;
		}
		if (document.search_form.order) {
			document.search_form.order.blur() ;
		}
		if (document.search_form.genus) {
			document.search_form.genus.blur() ;
		}
		if (document.search_form.infraspecies) {
			document.search_form.infraspecies.blur() ;
		}
		if (document.search_form.search_string) {
			document.search_form.search_string.blur() ;
		}
		if (document.search_form.common_name) {
			document.search_form.common_name.blur() ;
		}
		if (document.search_form.area) {
			document.search_form.area.blur() ;
		}
	}
}

function selectForm() {
	setTimeout("selectForm2()",300);
}
function selectForm2() {
	if (!document.getElementById('menu_browse') || !document.getElementById('menu_search') || !document.getElementById('menu_info') || !document.search_form) {
		return true ;
	}
	if (document.getElementById('menu_browse').style.visibility == "visible") {
		return true ;
	}
	if (document.getElementById('menu_search').style.visibility == "visible") {
		return true ;
	}
	if (document.getElementById('menu_info').style.visibility == "visible") {
		return true ;
	}
	if (document.search_form.search_string) {
		document.search_form.search_string.focus() ;
	} else if (document.search_form.common_name) {
		document.search_form.common_name.focus() ;
	} else if (document.search_form.area) {
		document.search_form.area.focus() ;
	} else if (document.auto_complete) {
		var selected_taxon = document.auto_complete.selected_taxon.value ;
		if (selected_taxon != "") {
			eval("document.search_form." + selected_taxon + ".focus()") ;
		}
	}
}

function ShowLayer(id, action){
	if (document.all)  {
		if (eval("document.all." + id)) {
			eval("document.all." + id + ".style.visibility='" + action + "'");
		}
	} else if (document.getElementById) {
		if (eval("document.getElementById('" + id + "')")) {
			eval("document.getElementById('" + id + "').style.visibility='" + action + "'");
		}
	} else {
		if (eval("document." + id)) {
			eval("document." + id + ".visibility='" + action + "'");
		}
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

function selectTaxon(theItem) {
	storeValues() ; 
	theItem = "link_" + theItem ;
	theName = document.getElementById(theItem).innerHTML ;
	var theTaxonShown = document.taxon_list.taxon_shown.value ;
	if (theTaxonShown == "class") {
		theTaxonShown = "tax_class" ;
	}
	eval("document.show_names." + theTaxonShown + ".value=unescape('" + theName + "');") ;
	document.show_names.show_taxon.value = '' ;
	eval("document.show_names.select_taxon.value=unescape('" + theTaxonShown + "');") ;
	document.show_names.submit() ;
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
	if (document.show_common_name.name) {
		document.show_common_name.name.value=commonName ;
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

function showCommonNameReferenceDetails(name,language,country,name_code) {
	if (document.show_reference_details.name) {
		document.show_reference_details.name.value=name ;
	}
	if (document.show_reference_details.language) {
		document.show_reference_details.language.value=language ;
	}
	if (document.show_reference_details.country) {
		document.show_reference_details.country.value=country ;
	}
	if (document.show_reference_details.name_code) {
		document.show_reference_details.name_code.value=name_code ;
	}
	
	document.show_reference_details.submit() ;
	
}

function showTaxonomicTree(selected_taxon) {
	document.show_tree.selected_taxon.value = selected_taxon ;
	document.show_tree.submit() ;
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

function sortByColumn(column) {
	document.sort_by_column.sort_by_column.value = column ;
	document.sort_by_column.submit() ;
}

function showStatus(message) {
    window.status = unescape(message) ;
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
	  ',status=no,titlebar=no,alwaysRaised,dependent,scrollbars=no,resizable=no');
	  
}
