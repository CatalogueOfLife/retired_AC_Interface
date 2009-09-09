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