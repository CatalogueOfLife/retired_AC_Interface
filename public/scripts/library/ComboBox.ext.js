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