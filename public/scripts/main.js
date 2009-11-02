function changeLSID(id)
{
    //change class collapsable to collapsed
    //change class lsidhide to lsidshow
    div = document.getElementById('hierachyId_' + id);
    for (i = 0; i < div.childNodes.length; i++) {
        node = div.childNodes[i];
        if (node.className == "collapsable") {
            node.className = "collapsed";
        }
        else if (node.className == "collapsed") {
            node.className = "collapsable";
        }
        if (node.className == "lsidhide") {
            node.className = "lsidshow";
        }
        else if (node.className == "lsidshow") {
            node.className = "lsidhide";
        }
    }
}

function changeLSIDclass()
{
	
}

var formInputElements = null;
getFormInputElements = function () {
    if(formInputElements == null) {
        formInputElements = dojo.query('form .dijitInputField input');
    }
    return formInputElements;
}
showLoader = function() {
    if(isFormValid()) {
        dojo.byId('loader').style.visibility = 'visible';
    }
}
removeKey = function () {
    dojo._destroyElement(dojo.byId('key'));
}
submitMultiForm = function() {
    showLoader();
    removeKey();
}
submitSimpleForm = function() {
    showLoader();
}
isFormValid = function() {
    var elements = getFormInputElements();    
    if(elements.length > 0) {        
        var val = '';
        dojo.forEach(elements,
            function(inputEl, index, array) {
                val += inputEl.value;
            }
        );
        return dojo.trim(val).length > 0 ? true : false;
    }
    return dojo.trim(dojo.byId('key').value).length > 1 ? true : false;
}
startList = function() {
    if (document.all && document.getElementById) {
        navRoot = document.getElementById("nav");
        for (i = 0; i < navRoot.childNodes.length; i++) {
            node = navRoot.childNodes[i];
            if (node.nodeName == "LI") {
                node.onmouseover = function() {
                    this.className += " over";
                }
                node.onmouseout = function() {
                    this.className = this.className.replace(" over", "");
                }
                ulNodes = node.getElementsByTagName("UL");
                for (j = 0; j < ulNodes[0].childNodes.length; j++) {
                    ulLiNode = ulNodes[0].childNodes[j];                
                    if (ulLiNode.nodeName == "LI") {
                        ulLiNode.onmouseover = function() {
                            this.className += " over";
                        }
                        ulLiNode.onmouseout = function() {
                            this.className = this.className.replace(" over", "");
                        }
                    }
                }
            }
        }
    }
}
moveMenu = function() {
    var menuLayer = document.getElementById('menu');    
    var theScroll = 0;
    if (window.pageYOffset) {
        theScroll = window.pageYOffset;        
    } else if (window.document.documentElement            
            && window.document.documentElement.scrollTop) {
        //IE6
        theScroll = window.document.documentElement.scrollTop;        
    } else if (window.document.body) {
        //Firefox
        theScroll = window.document.body.scrollTop;        
    }
    var newY = theScroll + "px";
    if (menuLayer) {
        menuLayer.style.top = newY;
        setTimeout("moveMenu()", 500);
    }
}
init = function() {
    startList();
    moveMenu();
}
window.onload = init;
window.onscroll = moveMenu;