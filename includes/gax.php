<?php
/**
 * Generates the Google Analytics code snippet using the given tracker id
 * If this is empty, returns an empty string
 *
 * @param string $trackerId
 * @return string $gax
 */
function getGAXCodeSnippet($trackerId = null)
{
    if(!$trackerId) {
        return '';
    }
    
    $gax = "<script type=\"text/javascript\">" .
    "var gaJsHost = ((\"https:\" == document.location.protocol) ? " .
    "\"https://ssl.\" : \"http://www.\");" .
    "document.write(unescape(\"%3Cscript src='\" + gaJsHost + " .
    "\"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));" .
    "</script>";
    
    $gax .=  "<script type=\"text/javascript\">" .
    "try { " .
    "var pageTracker = _gat._getTracker(\"$trackerId\");" .
    "pageTracker._trackPageview();" .
    "} catch(err) {}</script>";
    
    return $gax;
}
echo getGAXCodeSnippet(isset($gaxTrackerId) ? $gaxTrackerId : null);