var showCommentFeedback;
showCommentFeedback = true;
dojo.provide('ACI.dojo.TxTreeNode');
dojo.declare('ACI.dojo.TxTreeNode', dijit._TreeNode, {
    _onMouseEnter:function(evt){
    	if(dojo.byId("infoPanel_" + this.item.i.id)) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "inline-block");
    	}
    	if(dojo.byId("commentPanel_" + this.item.i.id)) {
    		dojo.style(dojo.byId("commentPanel_" + this.item.i.id), "display", "inline-block");
    	}
    	if(dojo.byId("mapPanel_" + this.item.i.id) &&
    		this.item.i.type != "" &&
    		this.item.i.type != "phylum") {
    		dojo.style(dojo.byId("mapPanel_" + this.item.i.id), "display", "inline-block");
    	}
    },
    _onMouseLeave:function(evt){
    	if (dojo.byId("infoPanel_" + this.item.i.id) && 
    		!dojo.byId("infoPanel_" + this.item.i.id + "_dropdown")) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "none");
    	}
    	if (dojo.byId("commentPanel_" + this.item.i.id) && 
        		!dojo.byId("commentPanel_" + this.item.i.id + "_dropdown")) {
        		dojo.style(dojo.byId("commentPanel_" + this.item.i.id), "display", "none");
        	}
    	if (dojo.byId("mapPanel_" + this.item.i.id) && 
        		!dojo.byId("mapPanel_" + this.item.i.id + "_dropdown") &&
        		this.item.i.type != "" &&
        		this.item.i.type != "phylum") {
        		dojo.style(dojo.byId("mapPanel_" + this.item.i.id), "display", "none");
        	}
    },
    setLabelNode : function(label) {
        if (this.item.root) {
            return this.inherited(arguments);
        }
        var type = this.tree.model.store.getValue(this.item, 'type');
        var rankName = this.tree.model.store.getValue(this.item, 'rank');
        //Checks if the checkbox showGSDCheckbox and showStatisticsCheckbox both exist.
        //If not, the module is most likely disabled.
        if(dojo.byId('showGSDCheckbox') && dojo.byId('showStatisticsCheckbox')) {
            var bullet = dojo.doc.createElement('span');
            bullet.className = 'bullet';
            bullet.appendChild(
            		dojo.doc.createTextNode(
            		' • '
            	)
            );
            var statistics = createStatistics(this.item, dojo.clone(bullet), "treeStatistics");
	        var source_databases = this.tree.model.store.getValue(this.item, 'source_databases');
	        var source_database = createDatabaseLinks(this.item, dojo.clone(bullet));

	        if(source_databases.length > 5) {
	        	source_database = dojo.doc.createElement('span');
	        	//source_database.title = title;
	        	source_database.appendChild(dojo.clone(bullet));
	        	source_database.appendChild(dojo.doc.createTextNode(translate('Multiple_providers')));
	        }
	    	source_database.className = 'treeSourceDatabase';
	    	var showGSDCheckbox = dojo.byId('showGSDCheckbox');
	    	if(showGSDCheckbox.checked == true) {
	    		source_database.style.display = "inline-block";
	    		//source_database.style.position = "relative";
	    	} else {
	    		source_database.style.display = "none";
	    		//source_database.style.position = "fixed";
	    	}
	    	var showStatisticsCheckbox = dojo.byId('showStatisticsCheckbox');
	    	if(showStatisticsCheckbox.checked == true) {
	    		statistics.style.display = "inline-block";
	    		//statistics.style.position = "relative";
	    	} else {
	    		statistics.style.display = "none";
	    		//statistics.style.position = "fixed";
	    	}
	        var panel = createInfoPanel(this.item);
	        var commentPanel = createCommentPanel(this.item);
        }
        var mapPanel = createMapPanel(this.item);
        
/*        var commentSpan;
        commentSpan = dojo.doc.createElement('span');
        if(showCommentFeedback == true) {
        	var commentLink;
        	commentLink = dojo.doc.createElement('a');
        	commentLink.href = "javascript:openCommentWindow();";
        	var commentIcon;
        	commentIcon = dojo.doc.createElement('img');
        	dojo.attr(commentIcon, {
        	    src: "../images/comment.jpg",
        	    alt: "Comment"
        	});
        	commentLink.appendChild(commentIcon);
        	commentSpan.appendChild(commentLink);
        }*/
        if (document.getElementById('showIconsCheckbox') != null && this.item.i.image != 0) {
	        var iconSpan = document.createElement('span');
	        iconSpan.className = 'iconSpan';
	        this.labelNode.appendChild(iconSpan);
	        var icon = document.createElement('img');
	        icon.src = this.item.i.image;
	        icon.className = 'treeIcon'
	    	if(document.getElementById('showIconsCheckbox').checked == true) {
	    		iconSpan.style.display = "inline-block";
	    	} else {
	    		iconSpan.style.display = "none";
	    	}
	        iconSpan.appendChild(icon);
        }
        
        if (this.tree.model.store
                .getValue(this.item, 'url') == null) {  
            var rank = dojo.doc.createElement('span');            
            rank.className = 'rank';
            rank.id = 'sn-' + this.tree.model.store.getValue(this.item, 'id');
            rank.appendChild(dojo.doc
                    .createTextNode(rankName));
            this.labelNode.appendChild(rank);
            var taxon = dojo.doc.createElement('span');
            taxon.className = 'nodeLabel node-' + type;
            taxon.appendChild(dojo.doc
                    .createTextNode(' ' + label));
            this.labelNode.appendChild(taxon);
            //Checks if the checkbox exists (the checkbox is enabled by the module)
            if(dojo.byId('showGSDCheckbox')) {
            	if(this.tree.model.store.getValue(this.item, 'estimation') != 0 || this.tree.model.store.getValue(this.item, 'total') != 0) {
            		this.labelNode.appendChild(statistics);
            	}
            }
            //Checks if the checkbox exists (the checkbox is enabled by the module)
            if(dojo.byId('showGSDCheckbox')) {
            	this.labelNode.appendChild(source_database);
            }
            
        } else {
            var leaf = dojo.doc.createElement('span');
            leaf.className = 'leaf';
            leaf.id = 'sn-' + this.tree.model.store.getValue(this.item, 'id');
            var a = dojo.doc.createElement('a');
            a.href = this.tree.model.store.getValue(this.item, 'url');
            if(type == 'Infraspecies') {
                for(var i in label) {
                    if(i > 0) {
                        a.appendChild(dojo.doc.createTextNode(' '));
                    }
                    // is a marker
                    if(label[i][1] == true) {
                        var span = dojo.doc.createElement('span');
                        span.className = 'marker';
                        span.appendChild(dojo.doc.createTextNode(label[i][0]));
                        a.appendChild(span);
                    }
                    else {
                        a.appendChild(dojo.doc.createTextNode(label[i][0]));
                    }
                }
            }
            else {
                a.appendChild(dojo.doc.createTextNode(label));
            }            
            leaf.appendChild(a);
            this.labelNode.innerHTML = '';
            //Checks if the checkbox exists (the checbox is enabled by the module)
            if(dojo.byId('showGSDCheckbox')) {
            	leaf.appendChild(source_database);
            }
            this.expandoNode.parentNode.className += ' dijitTreeLeafLabel'
            this.labelNode.appendChild(leaf);
        }
        this.labelNode.appendChild(panel);
        this.labelNode.appendChild(commentPanel);
        this.labelNode.appendChild(mapPanel);
    },    
    expand : function() {
        this.inherited(arguments);
        if(!hierarchy.length) {
            return;
        }
        var tree = this.tree;
        var pos = null;
        dojo.forEach(this.getChildren(), function(node, index, array) {
            pos = dojo.indexOf(hierarchy, node.item.i.id);
            // in array
            if(pos >= 0) {
                tree._expandNode(node);
                // latest element
                if(pos == hierarchy.length - 1) {
                    tree.focusNode(node);
                    setTimeout("scrollToEl(\"" + node.labelNode.children[0].id + "\")", 100);
                }
                else {
                    scrollToEl(node.labelNode.children[0].id);
                }
                hierarchy.unshift();
                return;
            }
        });
    }
});

function createDatabaseLinks(treeNode, bullet) {
	var source_databases = treeNode.i.source_databases;
	var sourceDatabaseString = dojo.doc.createElement('span');
	sourceDatabaseString.id = 'sourceDatabaseString';
	var title = '';
    if(bullet) {
    	sourceDatabaseString.appendChild(bullet);
    }
    var separator = ', ';
	for(var i in source_databases)
    {
        var a = dojo.doc.createElement('a');
        a.href = baseUrl + '/details/database/id/' + source_databases[i].source_database_id;
        a.title = source_databases[i].full_name;
        a.appendChild(dojo.doc.createTextNode(source_databases[i].short_name));
		if(i > 0) {
			sourceDatabaseString.appendChild(dojo.doc.createTextNode(separator));
			title = title + separator + ' ';
		}
        title = title + source_databases[i].short_name;
        sourceDatabaseString.appendChild(a);
    }
	return sourceDatabaseString;
}

function createStatistics(treeNode, bullet, className) {
    var statistics = dojo.doc.createElement('span');
    if (className) {
    	statistics.className = className;
    }
    if(treeNode.i.estimation) {
        if (bullet) {
        	statistics.appendChild(bullet);
        }
        var statsText = treeNode.i.total + ' spp;' +
		' ' + translate('est') + ' ' + treeNode.i.estimation;
        if (treeNode.i.percentage != '?') {
        	statsText += ';' + ' ' + treeNode.i.percentage;
        }
        statistics.appendChild(
        	dojo.doc.createTextNode(statsText)
        );
    }
    return statistics;
}

function createMapPanel(treeNode) {
	if(mapInTaxonomicTree == 0 || (treeNode.i.name.indexOf(' ') != -1 && treeNode.i.name != 'Not assigned')) {
		return dojo.create("span");
	}
    var panel = dojo.create("span", {
        id: "mapPanel" + '_' + treeNode.i.id,
        className: "mapIcon",
        style: "display: none;"
    });
    dojo.connect(panel, 'onclick', function(evt) {
    	showMap(treeNode);
    });
	return panel;
}

function showMap(treeNode){
	hidePreviousInfoPanels(treeNode.i.id);
    dialog.attr("content", createMapPanelContents(treeNode));
    dijit.popup.open({ 
        popup: dialog, 
        around: dojo.byId("mapPanel_" + treeNode.i.id) 
    });
    var dijitToolContainer = document.getElementById("mapPanel_" + treeNode.i.id + '_dropdown').firstElementChild.firstElementChild;
    dijitToolContainer.className = dijitToolContainer.className + ' dijitMapContainer';
	createMap();
    getRegions(treeNode.i.id,treeNode.i.type);
}

function createMapPanelContents(treeNode) {
	var table = dojo.doc.createElement('table');
	table.className = 'panelTable';
	var tr1 = dojo.doc.createElement('tr');
	var tr2 = dojo.doc.createElement('tr');
	var td1 = dojo.doc.createElement('td');
	var td2 = dojo.doc.createElement('td');
	
	tr1.appendChild(td1);
	tr2.appendChild(td2);
	table.appendChild(tr1);
	table.appendChild(tr2);
	
	var div = dojo.doc.createElement('div');
	div.id = 'mapPanel';
	
	var divMapProgressBar = dojo.doc.createElement('div');
	divMapProgressBar.id = 'map_progress_bar';
	divMapProgressBar.appendChild(dojo.doc.createTextNode(translate('Searching_for_the_regions_please_wait')))
	
	var divMap = dojo.doc.createElement('div');
	divMap.id = 'map_canvas';
	
	var closeButton = dojo.doc.createElement('span');
	dojo.connect(closeButton, 'onclick', function(evt) {
		closeMap(treeNode.i.id);
    });
	//closeButton.href = "javascript:closeInfo(" + treeNode.i.id + ")";
	closeButton.title = translate('Close_window');
	closeButton.className = "closeButton";
	//closeButton.appendChild(dojo.doc.createTextNode('X'));*/

	var rankName = '';
	if(treeNode.i.rank) {
		rankName = treeNode.i.rank + ' ';
	}
	
	var rank = dojo.doc.createElement('span');
	rank.appendChild(dojo.doc.createTextNode(rankName));
	
	var scientificName = dojo.doc.createElement('span');
	scientificName.appendChild(dojo.doc.createTextNode(treeNode.i.name));
	scientificName.className = 'node-' + treeNode.i.rank;
	
	var title = dojo.doc.createElement('span');
	title.appendChild(rank);
	title.appendChild(scientificName);
	title.className = 'commentPanelSection commentPanelTitle';
	
	
	div.appendChild(closeButton);
	div.appendChild(title);
	td1.appendChild(divMap);
	td2.appendChild(divMapProgressBar);
	div.appendChild(table);
	return div;
}

function createInfoPanel(treeNode) {
	if(treeNode.i.name.indexOf(' ') != -1 && treeNode.i.name != 'Not assigned') {
		return dojo.create("span");
	}
    var panel = dojo.create("span", {
        id: "infoPanel" + '_' + treeNode.i.id,
        className: "infoIcon",
        style: "display: none;"
    });
    dojo.connect(panel, 'onclick', function(evt) {
    	showInfo(treeNode);
    });
	return panel;
}

function showInfo(treeNode){
	hidePreviousInfoPanels(treeNode.i.id);
    dialog.attr("content", createInfoPanelContents(treeNode));
    dijit.popup.open({ 
        popup: dialog, 
        around: dojo.byId("infoPanel_" + treeNode.i.id) 
    });
} 

function createInfoPanelContents(treeNode) {
	var p = dojo.doc.createElement('p');
	
	var closeButton = dojo.doc.createElement('span');
	dojo.connect(closeButton, 'onclick', function(evt) {
    	closeInfo(treeNode.i.id);
    });
	//closeButton.href = "javascript:closeInfo(" + treeNode.i.id + ")";
	closeButton.title = translate('Close_window');
	closeButton.className = "closeButton";
	//closeButton.appendChild(dojo.doc.createTextNode('X'));*/

	var rankName = '';
	if(treeNode.i.rank) {
		rankName = treeNode.i.rank + ' ';
	}
	
	var rank = dojo.doc.createElement('span');
	rank.appendChild(dojo.doc.createTextNode(rankName));
	
	var scientificName = dojo.doc.createElement('span');
	scientificName.appendChild(dojo.doc.createTextNode(treeNode.i.name));
	scientificName.className = 'node-' + treeNode.i.rank;
	
	var title = dojo.doc.createElement('span');
	title.appendChild(rank);
	title.appendChild(scientificName);
	title.className = 'infoPanelSection infoPanelTitle';
	
	var dbLabel = (treeNode.i.source_databases.length == 1) ? 'Source_database' : 'Source_databases';
	var databaseLinks = dojo.doc.createElement('span');
	databaseLinks.appendChild(setLabel(dbLabel));
	databaseLinks.appendChild(createDatabaseLinks(treeNode));
	databaseLinks.className = 'infoPanelSection';
	
	p.appendChild(closeButton);
	p.appendChild(title);
	p.appendChild(databaseLinks);
	p.appendChild(createInfoPanelStatistics(treeNode));
	
	return p;
}

function setLabel(str) {
	var label = dojo.doc.createElement('span');
	label.className = 'infoPanelLabel';
	label.appendChild(
		dojo.doc.createTextNode(translate(str) + ': ')
	);
	return label;
}

function addInfoPanelSection(thisParent, thisLabel, thisValue) {
	var thisVar = dojo.doc.createElement('span')
	thisVar.className = 'infoPanelSection';
	thisVar.appendChild(setLabel(thisLabel));
	thisVar.appendChild(dojo.doc.createTextNode(thisValue));
	thisVar.appendChild(dojo.doc.createElement('br'));
	thisParent.appendChild(thisVar);
}

function createInfoPanelStatistics(treeNode) {
	var statistics = dojo.doc.createElement('span');
	addInfoPanelSection(statistics, 'Number_of_species', treeNode.i.total);
    if(treeNode.i.estimation) {
    	addInfoPanelSection(statistics, 'Estimated_number', treeNode.i.estimation);
        if (treeNode.i.percentage != '?') {
        	addInfoPanelSection(statistics, 'Percentage_covered', treeNode.i.percentage);
        }
        if (treeNode.i.estimate_source) {
        	addInfoPanelSection(statistics, 'Estimation_source', treeNode.i.estimate_source);
        }
    }
    return statistics;
}

function closeInfo(currentId){
	//alert('blah');
	//dijit.popup.close(dojo.byId('TooltipDialog_0'));
	dijit.popup.close(dialog);
	dojo.style(dojo.byId("infoPanel_" + currentId), "display", "none");
	return false;
}

function hidePreviousInfoPanels(currentId) {
	dojo.query("[id^='infoPanel_']").forEach(function(panel, i) {
        if("infoPanel_" + currentId != panel.id) {
        	panel.style.display = "none";
        }
    });
}

function openCommentWindow() {
    alert('clickerdieclick');
	var createCommentPanelContents;
	createCommentsPanel = dojo.doc.createElement('span');
    dialog.attr("content", createCommentPanelContents);
    dijit.popup.open({ 
        popup: dialog, 
        around: dojo.byId("commentPanel") 
    });
}

/////////////////////////////////////////////////////////

function createCommentPanel(treeNode) {
	if(treeNode.i.name.indexOf(' ') != -1 && treeNode.i.name != 'Not assigned') {
		return dojo.create("span");
	}
    var panel = dojo.create("span", {
        id: "commentPanel" + '_' + treeNode.i.id,
        className: "commentIcon",
        style: "display: none;"
    });
    dojo.connect(panel, 'onclick', function(evt) {
    	showComment(treeNode);
    });
	return panel;
}

function showComment(treeNode){
	hidePreviousCommentPanels(treeNode.i.id);
    dialog.attr("content", createCommentPanelContents(treeNode));
    dijit.popup.open({ 
        popup: dialog, 
        around: dojo.byId("commentPanel_" + treeNode.i.id) 
    });
} 

function createCommentPanelContents(treeNode) {
	var table = dojo.doc.createElement('table');
	table.className = 'panelTable';
	var tr1 = dojo.doc.createElement('tr');
	var tr2 = dojo.doc.createElement('tr');
	var tr3 = dojo.doc.createElement('tr');
	var tr4 = dojo.doc.createElement('tr');
	var tr5 = dojo.doc.createElement('tr');
	var th1 = dojo.doc.createElement('th');
	var th2 = dojo.doc.createElement('th');
	var th3 = dojo.doc.createElement('th');
	var th4 = dojo.doc.createElement('th');
	var td1 = dojo.doc.createElement('td');
	var td2 = dojo.doc.createElement('td');
	var td3 = dojo.doc.createElement('td');
	var td4 = dojo.doc.createElement('td');
	var td5 = dojo.doc.createElement('td');
	td5.colSpan = 2;
	td5.align = 'right';
	
	table.appendChild(tr1);
	table.appendChild(tr2);
	table.appendChild(tr3);
	table.appendChild(tr4);
	table.appendChild(tr5);
	
	tr1.appendChild(th1);
	tr1.appendChild(td1);
	tr2.appendChild(th2);
	tr2.appendChild(td2);
	tr3.appendChild(th3);
	tr3.appendChild(td3);
	tr4.appendChild(th4);
	tr4.appendChild(td4);
	tr5.appendChild(td5);
	
	var form = dojo.doc.createElement('form');
	form.method = 'get';
	form.id = 'commentForm';
	form.action = 'javascript:sendComment();';
	
	var closeButton = dojo.doc.createElement('span');
	dojo.connect(closeButton, 'onclick', function(evt) {
    	closeComment(treeNode.i.id);
    });
	//closeButton.href = "javascript:closeInfo(" + treeNode.i.id + ")";
	closeButton.title = translate('Close_window');
	closeButton.className = "closeButton";
	//closeButton.appendChild(dojo.doc.createTextNode('X'));*/

	var rankName = '';
	if(treeNode.i.rank) {
		rankName = treeNode.i.rank + ' ';
	}
	
	var rank = dojo.doc.createElement('span');
	rank.appendChild(dojo.doc.createTextNode(rankName));
	
	var scientificName = dojo.doc.createElement('span');
	scientificName.appendChild(dojo.doc.createTextNode(treeNode.i.name));
	scientificName.className = 'node-' + treeNode.i.rank;
	
	var title = dojo.doc.createElement('span');
	title.appendChild(rank);
	title.appendChild(scientificName);
	title.className = 'commentPanelSection commentPanelTitle';
	
	var type = dojo.doc.createElement('select');
	/*var type = dojo.doc.createElement('span');
	type.className = 'commentPanelLabel';
	type.appendChild(dojo.doc.createTextNode('type:'));*/
	type.name = 'commentType';
	addOption('general_comment',translate('general_comment'),type);
	addOption('correction',translate('correction'),type);
	addOption('additional_information',translate('additional_information'),type);
	addOption('wrong_branch',translate('wrong_branch'),type);

	/*var comment = dojo.doc.createElement('span');
	comment.className = 'commentPanelLabel';
	comment.appendChild(dojo.doc.createTextNode('comment:'));*/

	var name = dojo.doc.createElement('input');
	name.id = 'commentName';
	name.type = 'text';
	name.name = 'name';
	
	var email = dojo.doc.createElement('input');
	email.id = 'commentEmail';
	email.type = 'text';
	email.name = 'email';
	
	var textArea = dojo.doc.createElement('textarea');
	textArea.id = 'commentText';
	textArea.name = 'comment';
	textArea.style.width = "300px";
	textArea.style.height = "75px";
	
	var hiddenTaxaId = dojo.doc.createElement('input');
	hiddenTaxaId.type = 'hidden';
	hiddenTaxaId.name = 'taxaId';
	hiddenTaxaId.value = treeNode.i.id;
	
	var hiddenTaxonString = dojo.doc.createElement('input');
	hiddenTaxonString.id = 'commentTaxonString';
	hiddenTaxonString.type = 'hidden';
	hiddenTaxonString.name = 'taxonString';
	hiddenTaxonString.value = treeNode.i.name;
	
	var sendButton = dojo.doc.createElement('input');
	sendButton.type = 'submit';
	sendButton.value = translate('Send');
	
	form.appendChild(closeButton);
	form.appendChild(title);
	
	form.appendChild(table);
	th1.appendChild(setLabel(translate('Name')));
	td1.appendChild(name);
	th2.appendChild(setLabel(translate('Email')));
	td2.appendChild(email);
	th3.appendChild(setLabel(translate('Type')));
	td3.appendChild(type);
	th4.appendChild(setLabel(translate('Comment')));
	td4.appendChild(textArea);
	form.appendChild(hiddenTaxaId);
	form.appendChild(hiddenTaxonString);
	td5.appendChild(sendButton);
	
	return form;
}

function addOption(value, innerHTML, select) {
	var option = dojo.doc.createElement('option');
	option.value = value;
	option.innerHTML = translate(innerHTML);
	select.appendChild(option);
}

function addCommentPanelSection(thisParent, thisLabel, thisValue) {
	var thisVar = dojo.doc.createElement('span')
	thisVar.className = 'commentPanelSection';
	thisVar.appendChild(setLabel(thisLabel));
	thisVar.appendChild(dojo.doc.createTextNode(thisValue));
	thisVar.appendChild(dojo.doc.createElement('br'));
	thisParent.appendChild(thisVar);
}

function closeComment(currentId){
	//alert('blah');
	//dijit.popup.close(dojo.byId('TooltipDialog_0'));
	dijit.popup.close(dialog);
	dojo.style(dojo.byId("commentPanel_" + currentId), "display", "none");
	return false;
}

function closeMap(currentId){
	//alert('blah');
	//dijit.popup.close(dojo.byId('TooltipDialog_0'));
	dijit.popup.close(dialog);
	dojo.style(dojo.byId("mapPanel_" + currentId), "display", "none");
	return false;
}

function hidePreviousCommentPanels(currentId) {
	dojo.query("[id^='commentPanel_']").forEach(function(panel, i) {
        if("commentPanel_" + currentId != panel.id) {
        	panel.style.display = "none";
        }
    });
}

function sendComment() {
	var form = document.getElementById('commentForm');
	if(form.commentName.value == "" || form.commentEmail.value == "" || form.commentText.value == "") {
		alert(translate('All_fields_are_required'));
		return;
	}
	var submitUrl = baseUrl + "/ajax/feedback/ID/" + form.taxaId.value + 
		"/Comment/" + form.comment.value + 
		"/CommentType/" + form.commentType.value + 
		"/UserName/" + form.name.value + 
		"/UserMail/" + form.email.value +
		"/TaxonString/" + form.taxonString.value;
	form.action = 'javascript:alert(\''+translate('Comment_being_processed')+'\');';
	// The "xhrGet" method executing an HTTP GET
	dojo.xhrGet({
	    // The URL to request
	    url: submitUrl,
	    // The method that handles the request's successful result
	    // Handle the response any way you'd like!
	    load: function(result) {
	        alert(result);
	        closeComment(form.taxaId.value);
	    }
	});
}