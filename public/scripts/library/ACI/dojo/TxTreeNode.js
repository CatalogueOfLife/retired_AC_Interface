dojo.provide('ACI.dojo.TxTreeNode');
dojo.declare('ACI.dojo.TxTreeNode', dijit._TreeNode, {                
    setLabelNode : function(label) {
        if (this.item.root) {
            return this.inherited(arguments);
        }
        var lsid = dojo.doc.createElement('span');
        lsid.className = 'lsid';
        lsid.appendChild(dojo.doc.createTextNode(
            this.tree.model.store.getValue(this.item, 'lsid')));
        var type = this.tree.model.store.getValue(this.item, 'type');
        if (this.tree.model.store
                .getValue(this.item, 'url') == null) {  
            var rank = dojo.doc.createElement('span');            
            rank.className = 'rank';
            rank.id = 'sn-' + this.tree.model.store.getValue(this.item, 'id');
            rank.appendChild(dojo.doc
                    .createTextNode(type));
            this.labelNode.appendChild(rank);
            var taxon = dojo.doc.createElement('span');
            taxon.className = 'nodeLabel node-' + type;
            taxon.appendChild(dojo.doc
                    .createTextNode(' ' + label));
            this.labelNode.appendChild(taxon);
            this.labelNode.appendChild(lsid);
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
            leaf.appendChild(lsid);
            this.labelNode.innerHTML = '';
            this.expandoNode.parentNode.className += ' dijitTreeLeafLabel'
            this.labelNode.appendChild(leaf);
        }
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