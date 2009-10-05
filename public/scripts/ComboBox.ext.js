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
dojo.addOnLoad(function() {
    updateKey(null); 
});
var formInputElements = null;
getFormInputElements = function () {
    if(formInputElements == null) {
        formInputElements = dojo.query('form .dijitInputField input');
    }
    return formInputElements;
}
updateKey = function(val) {
    var elements = getFormInputElements();    
    var key = new Object;    
    for(var i = 0; i < elements.length; i++) {
        key[elements[i].id] = elements[i].value;
    }
    dojo.byId('key').value = dojo.toJson(key);
}
clearForm = function() {
    var elements = getFormInputElements();
    dojo.forEach(elements,
        function(inputEl, index, array) {
            inputEl.value = '';
        }
    );
}