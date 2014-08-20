<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once '../config.php';
require_once '../utility.php';
require_once '../FileCache.php';
require_once '../HealthLibraryRequest.php';
require_once '../HealthLibraryResponse.php';
require_once '../Library.php';

log_debug("******************** Starting test-library.php ********************");
log_debug("* PHP Version: " . phpversion());
log_debug("*******************************************************************");

$sCacheLoation = "C:\\inetpub\\wwwroot\\test-php\\HealthLibrary\\cache\\";
//$sCacheLoation = "c:\\phplogs\\";


writelineBR("Creating new Library object");
writelineBR("Cache: $sCacheLoation");
$xLibrary = new Library($sCacheLoation);

$sContentId 		= "P02730";
$sContentTypeId 	= "90";

writelineBR("Getting Content: $sContentId, $sContentTypeId");
$xResponse = $xLibrary->getContent($sContentId, $sContentTypeId);
if ($xResponse !== FALSE)
{
	//* we have a valid response object
	writelineBR("Resonse:");
	writelineBR($xResponse->getBody());	
}
else
{
	writelineBR("An error occured.");
}



