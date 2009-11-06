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
    
    $gax = "<script type=\"text/javascript\">" . PHP_EOL .
    "var gaJsHost = ((\"https:\" == document.location.protocol) ? " .
    "\"https://ssl.\" : \"http://www.\");" . PHP_EOL .
    "document.write(unescape(\"%3Cscript src='\" + gaJsHost + " .
    "\"google-analytics.com/ga.js' " .
    "type='text/javascript'%3E%3C/script%3E\"));" . PHP_EOL .
    "</script>" . PHP_EOL;
    
    $gax .=  "<script type=\"text/javascript\">" . PHP_EOL .
    "try { " . PHP_EOL .
    "var pageTracker = _gat._getTracker(\"$trackerId\");" . PHP_EOL .
    "pageTracker._trackPageview();" . PHP_EOL .
    "} catch(err) {}</script>";
    
    return $gax;
}
echo getGAXCodeSnippet(isset($gaxTrackerId) ? $gaxTrackerId : null);