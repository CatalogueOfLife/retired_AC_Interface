dojo.provide('ACI.dojo.TxReadStore');
dojo.require('dojox.data.QueryReadStore');
dojo.addOnLoad(function() {
    dojo.declare('ACI.dojo.TxReadStore', dojox.data.QueryReadStore, {
        fetch : function(request) {
            request.serverQuery = {
                q : request.query.name,
                p : dojo.byId('key') ? dojo.byId('key').value : ''
            };        
            return this.inherited('fetch', arguments);
        }
    });
});