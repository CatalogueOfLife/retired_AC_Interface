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
	if(treeNode.i.name.indexOf(' ') != -1 && treeNode.i.name != 'Not assigned') {
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
	createMap();
    getRegions(treeNode.i.id,treeNode.i.type);
}

function createMapPanelContents(treeNode) {
	var div = dojo.doc.createElement('div');
	div.id = 'mapPanel';
	
	var divMapProgressBar = dojo.doc.createElement('div');
	divMapProgressBar.id = 'map_progress_bar';
	divMapProgressBar.appendChild(dojo.doc.createTextNode('Searching for the regions, please wait...'))
	
	var divMap = dojo.doc.createElement('div');
	divMap.id = 'map_canvas';
	
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
	
	
	div.appendChild(closeButton);
	div.appendChild(title);
	div.appendChild(divMapProgressBar);
	div.appendChild(divMap);
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
	addOption('error','Errors_to_be_corrected',type);
	addOption('free_general_comment','Free_general_comment',type);
	addOption('futher_knowledge','Futher_knowledge_to_be_added',type);
	addOption('wrong_branche','Placed_in_the_wrong_branche',type);

	/*var comment = dojo.doc.createElement('span');
	comment.className = 'commentPanelLabel';
	comment.appendChild(dojo.doc.createTextNode('comment:'));*/

	var name = dojo.doc.createElement('input');
	name.type = 'text';
	name.name = 'name';
	
	var email = dojo.doc.createElement('input');
	email.type = 'text';
	email.name = 'email';
	
	var textArea = dojo.doc.createElement('textarea');
	textArea.name = 'comment';
	textArea.style.width = "300px";
	textArea.style.height = "75px";
	
	var hiddenTaxaId = dojo.doc.createElement('input');
	hiddenTaxaId.type = 'hidden';
	hiddenTaxaId.name = 'taxaId';
	hiddenTaxaId.value = treeNode.i.id;
	
	var sendButton = dojo.doc.createElement('input');
	sendButton.type = 'submit';
	sendButton.value = translate('Send');
	
	form.appendChild(closeButton);
	form.appendChild(title);
	form.appendChild(setLabel('name'));
	form.appendChild(name);
	form.appendChild(dojo.doc.createElement('br'));
	form.appendChild(setLabel('e-mail'));
	form.appendChild(email);
	form.appendChild(dojo.doc.createElement('br'));
	form.appendChild(setLabel('type'));
	form.appendChild(type);
	form.appendChild(dojo.doc.createElement('br'));
	form.appendChild(setLabel('comment'));
	form.appendChild(textArea);
	form.appendChild(hiddenTaxaId);
	form.appendChild(dojo.doc.createElement('br'));
	form.appendChild(sendButton);
	
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

function hidePreviousCommentPanels(currentId) {
	dojo.query("[id^='commentPanel_']").forEach(function(panel, i) {
        if("commentPanel_" + currentId != panel.id) {
        	panel.style.display = "none";
        }
    });
}

function sendComment() {
	var form = document.getElementById('commentForm');
	form.action = 'javascript:alert(\''+translate('Comment_already_sending')+'\');';
	// The "xhrGet" method executing an HTTP GET
	dojo.xhrGet({
	    // The URL to request
	    url: jsFeedbackUrl+"?id="+form.taxaId.value+"&comment="+form.comment.value+"&commentType="+form.commentType.value+"&name="+form.name.value+"&email="+form.email.value,
	    // The method that handles the request's successful result
	    // Handle the response any way you'd like!
	    load: function(result) {
	        alert("The message is: " + result);
	        closeComment(form.taxaId.value);
	    }
	});
}