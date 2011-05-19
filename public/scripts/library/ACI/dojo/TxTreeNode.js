dojo.provide('ACI.dojo.TxTreeNode');
dojo.declare('ACI.dojo.TxTreeNode', dijit._TreeNode, {
    _onMouseEnter:function(evt){
    	if(dojo.byId("infoPanel_" + this.item.i.id)) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "inline-block");
    	}
    },
    _onMouseLeave:function(evt){
    	if (dojo.byId("infoPanel_" + this.item.i.id) && 
    		!dojo.byId("infoPanel_" + this.item.i.id + "_dropdown")) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "none");
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
            		' - '
            	)
            );
/*	        var statistics = dojo.doc.createElement('span');
	        statistics.className = 'treeStatistics';
	        if(this.tree.model.store.getValue(this.item, 'estimation')) {
		        var temp = dojo.clone(bullet);
		        statistics.appendChild(temp);
		        statistics.appendChild(
		        	dojo.doc.createTextNode(
		        		this.tree.model.store.getValue(this.item, 'total') + ' spp;' +
		        		' est ' + this.tree.model.store.getValue(this.item, 'estimation') + ';' +
		        		' ' + this.tree.model.store.getValue(this.item, 'percentage') + '%'
		        	)
		        );
	        }*/
            var statistics = createStatistics(this.item, dojo.clone(bullet), "treeStatistics");
	        	        
	        var source_databases = this.tree.model.store.getValue(this.item, 'source_databases');
	        var source_database = createDatabaseLinks(this.item, dojo.clone(bullet));
	        
	        /*	        var source_databases = this.tree.model.store.getValue(this.item, 'source_databases');
	        var gsdCounter = 0;
	    	var source_database = dojo.doc.createElement('span');
	    	var title = '';
	        var temp = dojo.clone(bullet);
	    	source_database.appendChild(temp);
	    	separator = ',';
	    	for(var i in source_databases)
	        {
	            var a = dojo.doc.createElement('a');
	            a.href = baseUrl + '/details/database/id/' + source_databases[i].source_database_id;
	            a.title = source_databases[i].full_name;
	            a.appendChild(dojo.doc.createTextNode(source_databases[i].short_name));
	    		if(gsdCounter > 0) {
	    			source_database.appendChild(dojo.doc.createTextNode(separator));
	    			title = title + separator + ' ';
	    		}
	            title = title + source_databases[i].short_name;
	        	source_database.appendChild(a);
	            gsdCounter++;
	        }
*/
	        if(source_databases.length > 5) {
	        	source_database = dojo.doc.createElement('span');
	        	//source_database.title = title;
	        	source_database.appendChild(dojo.clone(bullet));
	        	source_database.appendChild(dojo.doc.createTextNode(translate('Multiple_providers')));
	        }
	    	source_database.className = 'treeSourceDatabase';
	    	var showGSDCheckbox = dojo.byId('showGSDCheckbox');
	    	if(showGSDCheckbox.checked == true) {
	    		source_database.style.visibility = "visible";
	    		source_database.style.position = "relative";
	    	} else {
	    		source_database.style.visibility = "hidden";
	    		source_database.style.position = "fixed";
	    	}
	    	var showStatisticsCheckbox = dojo.byId('showStatisticsCheckbox');
	    	if(showStatisticsCheckbox.checked == true) {
	    		statistics.style.visibility = "visible";
	    		statistics.style.position = "relative";
	    	} else {
	    		statistics.style.visibility = "hidden";
	    		statistics.style.position = "fixed";
	    	}
	    	
	        //console.dir(this.item);
	        var panel = createInfoPanel(this.item);
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
        	statsText += ';' + ' ' + treeNode.i.percentage + '%';
        }
        statistics.appendChild(
        	dojo.doc.createTextNode(statsText)
        );
    }
    return statistics;
}

function createInfoPanel(treeNode) {
	if(treeNode.i.name.indexOf(' ') != -1) {
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
	
	var closeButton = dojo.doc.createElement('a');
	closeButton.href = "javascript:closeInfo(" + treeNode.i.id + ")";
	closeButton.title = translate('Close_window');
	closeButton.className = "closeButton";
	//closeButton.appendChild(dojo.doc.createTextNode('X'));

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
	title.className = 'infoPanel infoPanelTitle';
	
	var dbLabel = (treeNode.i.source_databases.length == 1) ? 'Source_database' : 'Source_databases';
	var databaseLinks = dojo.doc.createElement('span');
	databaseLinks.appendChild(setLabel(dbLabel));
	databaseLinks.appendChild(createDatabaseLinks(treeNode));
	databaseLinks.className = 'infoPanel';
	
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

function addInfoPanelLine(thisParent, thisLabel, thisValue) {
	var thisVar = dojo.doc.createElement('span')
	thisVar.className = 'infoPanel';
	thisVar.appendChild(setLabel(thisLabel));
	thisVar.appendChild(dojo.doc.createTextNode(thisValue));
	thisVar.appendChild(dojo.doc.createElement('br'));
	thisParent.appendChild(thisVar);
}

function createInfoPanelStatistics(treeNode) {
	var statistics = dojo.doc.createElement('span');
	addInfoPanelLine(
		statistics, 
		'Number_of_species', 
		treeNode.i.total
	);
    if(treeNode.i.estimation) {
    	addInfoPanelLine(
    		statistics, 
    		'Estimated_number', 
			treeNode.i.estimation
		);
        if (treeNode.i.percentage != '?') {
        	addInfoPanelLine(
    			statistics, 
    			'Percentage_covered', 
    			treeNode.i.percentage + '%'
    		);
        }
        if (treeNode.i.estimate_source) {
        	addInfoPanelLine(
    			statistics, 
    			'Estimation_source', 
    			treeNode.i.estimate_source
    		);
        }
    }
    return statistics;
}

function closeInfo(id){
	dijit.popup.close(dialog);
	dojo.style(dojo.byId("infoPanel_" + id), "display", "none");
}

function hidePreviousInfoPanels(currentId) {
	dojo.query("[id^='infoPanel_']").forEach(function(panel, i) {
        if("infoPanel_" + currentId != panel.id) {
        	panel.style.display = "none";
        }
    }); 
}