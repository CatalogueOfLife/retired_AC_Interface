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
	},
	_startSearch:function(key){
	    this._setLoadingSymbolVisibility(true);
	    // compile complementary field values
	    updateKey();
	    if(!this._popupWidget){
	        var _25=this.id+"_popup";
	        this._popupWidget=new dijit.form._ComboBoxMenu({onChange:dojo.hitch(this,this._selectOption),id:_25});
	        dijit.removeWaiState(this.focusNode,"activedescendant");
	        dijit.setWaiState(this.textbox,"owns",_25);
	    }
	    this.item=null;
	    var _26=dojo.clone(this.query);
	    this._lastInput=key;
	    this._lastQuery=_26[this.searchAttr]=this._getQueryString(key);
	    this.searchTimer=setTimeout(dojo.hitch(this,function(_27,_28){
    	    var _29={queryOptions:{ignoreCase:this.ignoreCase,deep:true},query:_27,onBegin:dojo.hitch(this,"_setMaxOptions"),onComplete:dojo.hitch(this,"_openResultList"),onError:function(_2a){
    	        console.error("dijit.form.ComboBox: "+_2a);
    	        dojo.hitch(_28,"_hideResultList")();
    	    },start:0,count:this.pageSize};
    	    dojo.mixin(_29,_28.fetchProperties);
    	    var _2b=_28.store.fetch(_29);
    	    var _2c=function(_2d,_2e){
    	        _2d.start+=_2d.count*_2e;
    	        _2d.direction=_2e;
    	        this.store.fetch(_2d);
    	    };
    	    this._nextSearch=this._popupWidget.onPage=dojo.hitch(this,_2c,_2b);
	    },_26,this),this.searchDelay);	
	},
	_setLoadingSymbolVisibility : function(visible) {
	    var el = dojo.query("fieldset#fieldset-" + this.id + "Group div.loadingSymbol");
	    if(el.length != 1) {
	        return false;
	    }
	    el[0].style.visibility = visible ? 'visible' : 'hidden';
	    return true;
	},	
	_openResultList : function(_13,_14) {
	    if(this.disabled||this.readOnly||(_14.query[this.searchAttr]!=this._lastQuery)){
	        return;
	    }	    
	    this._popupWidget.clearResultList();
	    if(!_13.length){
	        this._hideResultList();
	        return;
	    }
	    this.item=null;
	    var _15=new String(this.store.getValue(_13[0],this.searchAttr));
	    if(_15&&this.autoComplete&&!this._prev_key_backspace&&(_14.query[this.searchAttr]!="*")){
	        this.item=_13[0];
	        this._autoCompleteText(_15);
	    }
	    _14._maxOptions=this._maxOptions;
	    this._popupWidget.createOptions(_13,_14,dojo.hitch(this,"_getMenuLabelFromItem"));
	    this._showResultList();
	    if(_14.direction){
	        if(1==_14.direction){
	            this._popupWidget.highlightFirstOption();
	        } else {
	            if(-1==_14.direction) {
	                this._popupWidget.highlightLastOption();
	            }
	        }
	        this._announceOption(this._popupWidget.getHighlightedOption());
	    }	    
	    this._setLoadingSymbolVisibility(false);
	}	
});
dojo.addOnLoad(function() {	
    updateKey(null);
    removeValidationElements();
});
removeValidationElements = function() {
    dojo.query("div.dijitValidationIcon").forEach(
        function transform(div) {
            div.className = "dijitReset loadingSymbol";
        }
    );
    dojo.query("div.dijitValidationIconText").forEach(dojo.destroy);
}
keyPress = function(evt) {	
	if(evt.keyCode == 13) {
		var el = dojo.query(
			"fieldset#fieldset-" + evt.explicitOriginalTarget.id + "Group div.loadingSymbol"
		);
	    if(el.length >= 1) {
	    	el[0].style.visibility = 'hidden';
	    }
		dojo.byId("search").click();    
	}
}
updateKey = function() {
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
    updateKey();
    document.clear_form.submit();
}