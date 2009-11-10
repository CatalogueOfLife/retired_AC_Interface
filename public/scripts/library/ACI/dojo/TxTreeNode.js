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
        if (this.tree.model.store
                .getValue(this.item, 'url') == null) {            
            var rank = dojo.doc.createElement('span');
            rank.className = 'rank';
            rank.appendChild(dojo.doc
                    .createTextNode(this.tree.model.store.getValue(
                            this.item, 'type')));
            this.labelNode.appendChild(rank);
            var taxon = dojo.doc.createElement('span');
            
            taxon.appendChild(dojo.doc
                    .createTextNode(' ' + label));
            this.labelNode.appendChild(taxon);
            this.labelNode.appendChild(lsid);
        } else {
            var leaf = dojo.doc.createElement('span');
            leaf.className = "leaf";            
            var a = dojo.doc.createElement('a');
            a.href = this.tree.model.store.getValue(this.item, 'url');
            a.appendChild(dojo.doc.createTextNode(label));
            var subsp = this.tree.model.store.getValue(this.item, 'subsp');
            if(subsp != null) {
                var span = dojo.doc.createElement('span');
                span.className = 'subsp';
                span.appendChild(dojo.doc.createTextNode(' subsp. '));
                a.appendChild(span);
                a.appendChild(dojo.doc.createTextNode(subsp));
            }
            leaf.appendChild(a);
            leaf.appendChild(lsid);
            this.labelNode.innerHTML = '';            
            this.expandoNodeText.parentNode.removeChild(this.expandoNodeText);
            this.expandoNode.parentNode.className += " dijitTreeLeafLabel";
            this.expandoNode.parentNode.appendChild(leaf);
        }
    }
});