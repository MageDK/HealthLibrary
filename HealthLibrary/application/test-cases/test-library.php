<?php

require_once '../config.php';
require_once '../utility.php';
require_once '../FileCache.php';
require_once '../HealthLibraryRequest.php';
require_once '../Library.php';

log_debug("******************** Starting test-library.php ********************");

$sCacheLoation = "C:\\inetpub\\wwwroot\\test-php\\HealthLibrary\\cache\\";

writelineBR("Creating new Library object");
writelineBR("Cache: $sCacheLoation");
$xLibrary = new Library($sCacheLoation);

$sContentId 		= "P02730";
$sContentTypeId 	= "90";

writelineBR("Getting Content: $sContentId, $sContentTypeId");
$bSuccess = $xLibrary->getContent($sContentId, $sContentTypeId);

writelineBR("Resonse:");
writeline($bSuccess);
