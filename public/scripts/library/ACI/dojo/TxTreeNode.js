var showCommentFeedback;
dojo.provide('ACI.dojo.TxTreeNode');
dojo.declare('ACI.dojo.TxTreeNode', dijit._TreeNode, {
    _onMouseEnter:function(evt){
    	if(dojo.byId("infoPanel_" + this.item.i.id)) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "inline-block");
    	}
    	if(dojo.byId("commentPanel_" + this.item.i.id) && jsFeedbackUrl) {
    		dojo.style(dojo.byId("commentPanel_" + this.item.i.id), "display", "inline-block");
    	}
    },
    _onMouseLeave:function(evt){
    	if (dojo.byId("infoPanel_" + this.item.i.id) && 
    		!dojo.byId("infoPanel_" + this.item.i.id + "_dropdown")) {
    		dojo.style(dojo.byId("infoPanel_" + this.item.i.id), "display", "none");
    	}
    	if (dojo.byId("commentPanel_" + this.item.i.id) && 
        		!dojo.byId("commentPanel_" + this.item.i.id + "_dropdown") && jsFeedbackUrl) {
        		dojo.style(dojo.byId("commentPanel_" + this.item.i.id), "display", "none");
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
	tr1.id = 'trName';
	var tr2 = dojo.doc.createElement('tr');
	tr2.id = 'trEmail';
	var tr4 = dojo.doc.createElement('tr');
	tr4.id = 'trComment';
	var tr5 = dojo.doc.createElement('tr');
	var th1 = dojo.doc.createElement('th');
	var th2 = dojo.doc.createElement('th');
	var th4 = dojo.doc.createElement('th');
	var td1 = dojo.doc.createElement('td');
	var td2 = dojo.doc.createElement('td');
	var td4 = dojo.doc.createElement('td');
	var td5 = dojo.doc.createElement('td');
	td5.colSpan = 2;
	td5.align = 'right';
	
	table.appendChild(tr1);
	table.appendChild(tr2);
	table.appendChild(tr4);
	table.appendChild(tr5);
	
	tr1.appendChild(th1);
	tr1.appendChild(td1);
	tr2.appendChild(th2);
	tr2.appendChild(td2);
	tr4.appendChild(th4);
	tr4.appendChild(td4);
	tr5.appendChild(td5);
	
	var form = dojo.doc.createElement('form');
	form.method = 'post';
	form.id = 'commentForm';
	form.action = jsFeedbackUrl;
	
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
	
	var hiddenGsdIdString = dojo.doc.createElement('input');
	hiddenGsdIdString.type = 'hidden';
	hiddenGsdIdString.name = 'gsd_id_string';
	var gsd_ids = new Array();
	for(i in treeNode.i.source_databases) {
		gsd_ids[i] = treeNode.i.source_databases[i].source_database_id;
	}
	hiddenGsdIdString.value = gsd_ids.join('|');
	
	var name = dojo.doc.createElement('input');
	name.id = 'nameField';
	name.type = 'text';
	name.name = 'name';
	name.setAttribute("dojoType","dijit.form.TextBox");
	
	var email = dojo.doc.createElement('input');
	email.id = 'emailField';
	email.type = 'text';
	email.name = 'email';
	email.setAttribute("dojoType","dijit.form.TextBox");
	
	var textArea = dojo.doc.createElement('textarea');
	textArea.id = 'commentField';
	textArea.name = 'comment';
	textArea.style.width = "300px";
	textArea.setAttribute("dojoType","dijit.form.Textarea");
	
	var hiddenTaxonId = dojo.doc.createElement('input');
	hiddenTaxonId.type = 'hidden';
	hiddenTaxonId.name = 'taxonId';
	hiddenTaxonId.value = treeNode.i.id;
	
	var hiddenVersion = dojo.doc.createElement('input');
	hiddenVersion.type = 'hidden';
	hiddenVersion.name = 'version';
	hiddenVersion.value = dojo.query(".app-version")[0].innerHTML;
	
	var hiddenTaxonString = dojo.doc.createElement('input');
	hiddenTaxonString.id = 'taxonString';
	hiddenTaxonString.type = 'hidden';
	hiddenTaxonString.name = 'taxon_string';
	hiddenTaxonString.value = '';
	
	var sendButton = dojo.doc.createElement('button');
	sendButton.type = 'submit';
	sendButton.id = 'submitFormButton';
	sendButton.innerHTML = translate('Send');
	sendButton.setAttribute("dojoType","dijit.form.Button");
	
	form.appendChild(closeButton);
	form.appendChild(title);
	
	form.appendChild(table);
	th1.appendChild(setLabel('name'));
	td1.appendChild(name);
	th2.appendChild(setLabel('e-mail'));
	td2.appendChild(email);
	th4.appendChild(setLabel('comment'));
	td4.appendChild(textArea);
	form.appendChild(hiddenTaxonId);
	form.appendChild(hiddenTaxonString);
	form.appendChild(hiddenGsdIdString);
	form.appendChild(hiddenVersion);
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

function sendForm() {
    var form = dojo.byId("commentForm");

    dojo.connect(form, "onsubmit", function(event) {
        //Stop the submit event since we want to control form submission.
        dojo.stopEvent(event);
        var allFieldsEnteredCheck = true;
    	var trName = document.getElementById('trName');
    	var trEmail = document.getElementById('trEmail');
    	var trComment = document.getElementById('trComment');
        var emailField = document.getElementById('emailField');
        var nameField = document.getElementById('nameField');
        var commentField = document.getElementById('commentField');
        nameField.style.backgroundColor = '#fff';
    	emailField.style.backgroundColor = '#fff';
    	commentField.style.backgroundColor = '#fff';
        if(document.getElementById('emailField').value == '') {
        	emailField.style.backgroundColor = '#f66';
        	allFieldsEnteredCheck = false;
        }
        if(document.getElementById('nameField').value == '') {
        	nameField.style.backgroundColor = '#f66';
        	allFieldsEnteredCheck = false;
        }
        if(document.getElementById('commentField').value == '') {
        	commentField.style.backgroundColor = '#f66';
        	allFieldsEnteredCheck = false;
        }
        if(!allFieldsEnteredCheck) {
        	alert(translate("You_have_to_enter_all_fields")+".");
        }
        var emailPatern = /[a-zA-Z0-9\!\#\$\%\'\*\+\-\/\=\?\^\_\`\{\|\}\~\.]+@[a-zA-Z0-9\!\#\$\%\'\*\+\-\/\=\?\^\_\`\{\|\}\~\.]+\.[a-zA-Z]{1,3}/;
        if (!emailPatern.exec(emailField.value)) {
        	emailField.style.backgroundColor = '#f66';
        	alert(translate("Please_enter_a_valid_email_address")+".");
        	allFieldsEnteredCheck = false;
        }
        if(!allFieldsEnteredCheck) {
        	return;
        }
        
    	dojo.xhrGet({
    	    // The URL to request
    	    url: baseUrl+"/ajax/get-feedback-information-by-taxon-id/taxonId/"+document.getElementById('commentForm').taxonId.value,
    	    // The method that handles the request's successful result
    	    // Handle the response any way you'd like!
    	    load: function(result) {
    			document.getElementById('taxonString').value = result;
    			/*sendFeedbackWithPost(form);*/
    	        //The parameters to pass to xhrPost, the form, how to handle it, and the callbacks.
    	        //Note that there isn't a url passed.  xhrPost will extract the url to call from the form's
    	        //'action' attribute.  You could also leave off the action attribute and set the url of the xhrPost object
    	        //either should work.
    	        var xhrArgs = {
    	            form: dojo.byId("commentForm"),
    	            handleAs: "text",
    	            load: function(result) {
    	        		if(result == 0) {
    	        			alert(translate("Could_not_connect_to_feedback_database")+" (#0).");
    	        		} else if(result == 1) {
    	        			alert(translate("Your_feedback_has_been_submitted_successfully")+".");
    	        		} else if(result == 2) {
    	        			alert(translate("Your_feedback_has_already_been_submitted")+".");
    	        		} else if(result == 3) {
    	        			alert(translate("Feedback_form_incomplete")+" (#3).");
    	        		} else if(result == 4) {
    	        			alert(translate("Could_not_connect_to_feedback_database")+" (#4).");
    	        		} else {
    	        			alert(translate("Could_not_connect_to_feedback_database")+" (#789).");
    	        		}
    	            },
    	            error: function(error) {
    	            	alert(translate("Could_not_connect_to_feedback_server")+".");
    	            	console.dir(error);
    	            }
    	        }
    	        var deferred = dojo.xhrPost(xhrArgs);
    	    }
    	});

        document.getElementById('submitFormButton').disabled = true;
        closeComment(document.getElementById('commentForm').taxonId.value);
        //Call the asynchronous xhrPost
    });
}
dojo.addOnLoad(sendForm);