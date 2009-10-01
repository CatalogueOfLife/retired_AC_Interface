dojo.provide('ACI.dojo.TxReadStore');
dojo.require('dojox.data.QueryReadStore');
dojo.addOnLoad(function() {
    dojo.declare('ACI.dojo.TxReadStore', dojox.data.QueryReadStore, {
        fetch : function(request) {
            request.count = null;
            request.serverQuery = {
                q : request.query.name,
                p : dojo.byId('params') ? dojo.byId('params').value : ''
            };
            // Call superclasses' fetch
            return this.inherited('fetch', arguments);
        }
    });
});