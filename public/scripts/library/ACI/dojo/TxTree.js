dojo.provide('ACI.dojo.TxTree');
dojo.require('dijit.Tree');
dojo.declare('ACI.dojo.TxTree', dijit.Tree, {
    showRoot : false,
    _clickTarget : null,
    _createTreeNode : function(args) {
        return new ACI.dojo.TxTreeNode(args);
    },
    _onClick : function(evt) {
        this._clickTarget = evt.target;
        if (this._clickTarget.nodeName == 'A' || this._clickTarget.className == 'lsid') {
            var nodeWidget = dijit.getEnclosingWidget(this._clickTarget);
            this.onClick(nodeWidget.item, nodeWidget);
            return;
        }
        return this.inherited(arguments);
    }
});