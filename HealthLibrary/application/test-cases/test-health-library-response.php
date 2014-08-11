<?php

require_once '../config.php';
require_once '../utility.php';
require_once '../FileCache.php';
require_once '../HealthLibraryRequest.php';
require_once '../HealthLibraryResponse.php';

log_debug("******************** Starting test-health-library-response.php ********************");

$sBaseUrl 		= "http://external.ws.staywell.com/";
$sSiteName 		= "umiamikbws";
$sServiceName 	= "Content.svc";

$xHLR = new HealthLibraryRequest($sBaseUrl, $sSiteName, $sServiceName, "GET");


writelineBR("HealthLibraryRequest object created.");

$sContentTypeId = "85";
$sContentId = "P09506";

//* Get the license
$bResult = $xHLR->getResponse("GetLicense");
writelineBR("Getting License Information");
writelineBR("Result: " .  htmlentities(var_export($bResult, true)));


writelineBR("");
writelineBR("Let's get some content");

writelineBR("Lets add some paramenters");
writelineBR("Add: ContentTypeId: $sContentTypeId");
$bSuccess = $xHLR->addParameter("ContentTypeId", $sContentTypeId);
writelineBR( var_export($bSuccess, true) );

writelineBR("Add: ContentId: $sContentId");
$bSuccess = $xHLR->addParameter("ContentId", $sContentId);
writelineBR( var_export($bSuccess, true) );

writelineBR("Submitting Request");
$bResult = $xHLR->getResponse();

writelineBR("Createing the Respnse XML Document");
$xLibraryResponse = new HealthLibraryResponse( $bResult );

writelineBR("Check to see if we have a valid XML Document");
writelineBR( var_export( $xLibraryResponse->isValidXMLDocument(), true) );

writelineBR("Parse out the content we want");
$bSuccess = $xLibraryResponse->parseContent();
writelineBR( var_export($bSuccess, true) );

writelineBR("Regular Title:" . $xLibraryResponse->getRegularTitle() );
writelineBR("Body:" . $xLibraryResponse->getBody() );



?>
