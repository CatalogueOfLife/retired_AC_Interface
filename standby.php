<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Catalogue of Life : 2005 Annual Checklist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/javascript">
	function closeMe(startTime) {
		currentURL = "" ;
		if (!window.opener.closed) {
			currentURL = window.opener.location.href ;
		}
		closeThisWindow = false ;
		/*
		if (window.opener) {
			if (window.opener.document) {
				if (window.opener.document.page_status) {
					if (window.opener.document.page_status.status) {
						if (window.opener.document.page_status.status.value == 'complete') {
							closeThisWindow = true ;
						}
					}
				}
			}
		}
		*/
		
		if (closeThisWindow == false) {
			if (currentURL == "" ) {
				closeThisWindow = true ;
			} else if (currentURL.indexOf("search.php") == -1 
			  && currentURL.indexOf("search_scientific.php") == -1
			  && currentURL.indexOf("search_by_common_name.php") == -1
			  && currentURL.indexOf("search_by_distribution.php") == -1 
			  && currentURL.indexOf("browse_by_classification.php") == -1 ) {
				closeThisWindow = true ;
			}	
		}
		
		if (closeThisWindow == true) {
			window.close() ;
		} else {
		
		/*
			var currentTime = getCurrentTime() ;
			
			secondsTaken = currentTime - startTime ;
			minutesTaken = Math.floor(secondsTaken/60) ;
			secondsTaken -= (minutesTaken * 60) ;
			if (secondsTaken < 10) {
				secondsTaken = "0" + secondsTaken ;
			}
			window.status = minutesTaken + ":" + secondsTaken ;
		*/
			window.status = " " ;
		
		
			setTimeout("closeMe(" + startTime + ")",1000);
		}
	}
	
	var startTime = getCurrentTime() ;
	window.status = " " ;
	setTimeout("closeMe(" + startTime + ")",1000);
	
	function getCurrentTime() {
		var now = new Date();
		var hours = now.getHours();
		var minutes = now.getMinutes();
		var seconds = now.getSeconds()
		return (hours * 60 * 60) + (minutes * 60) + seconds ;
	}
</SCRIPT>
<style type="text/css">
<!--
p {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px}
body {  margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px}
-->
</style>
<script language="JavaScript" type="text/javascript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
// -->
</script>
</head>
<body bgcolor="#ffffff" style="background-color:#ffffff" text="#000000">
<div id="Layer1" style="position:absolute; z-index:1; top: 10; left: 10";> <img name="sp2000_logo" src="images/wait.gif" width="110" height="120" hspace="220"> 
</div>
<div id="Layer2" style="position:absolute; z-index:2; top: 50px; width: 220px; left: 10; height: 73px"> 
  <?php
	$message = "Please stand by..." ;
	if ( isset($_REQUEST['msg'])) {
		$message = urldecode($_REQUEST['msg']) . " " . $message ;
	}
	echo "<p>$message</p>" ;
?>
</div>
</body>
</html>
