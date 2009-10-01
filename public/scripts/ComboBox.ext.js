dojo.require('dijit.form.ComboBox');
dojo.extend(dijit.form.ComboBox, {
	_onArrowMouseDown : function(evt) {
		if (this.disabled || this.readOnly) {
			return;
		}
		dojo.stopEvent(evt);
		this.focus();
		if (this._isShowingNow) {
			this._hideResultList();
		} else {
			this._startSearchFromInput();
		}
	}
});
updateParams = function(val) {
    var params = '{"genus":"' + dojo.byId('genus').value + '",' +
                 '"species":"' + dojo.byId('species').value + '",' +
                 '"infraspecies":"' + dojo.byId('infraspecies').value + '"}';
    dojo.byId('params').value = params;
}