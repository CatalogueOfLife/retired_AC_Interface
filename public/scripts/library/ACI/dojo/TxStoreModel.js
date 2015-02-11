dojo.provide('ACI.dojo.TxStoreModel');
dojo.require('dijit.tree.ForestStoreModel');
dojo.declare('ACI.dojo.TxStoreModel', dijit.tree.ForestStoreModel, {
    getChildren : function(parentItem, complete_cb, error_cb) {
        if (parentItem.root) {
            return this.inherited(arguments);
        }
        var parentId = this.store.getValue(parentItem, 'id');
        if (!parentId) {
            parentId = 0;
        }
        this.store.fetch( {
            query : {
                id : parentId,
                hash: treeHash
            },
            onComplete : complete_cb,
            onError : error_cb
        });
    },
    mayHaveChildren : function(item) {
        if (item.root)
            return true;

        if (this.store.getValue(item, 'numChildren') > 0)
            return true;

        return false;
    }
});