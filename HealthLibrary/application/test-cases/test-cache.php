<?php

require_once '../config.php';
require_once '../utility.php';
require_once '../FileCache.php';

log_debug("******************** Starting test-cache.php ********************");

$xFileCache = new FileCache();

$sCacheDirectory = "/Library/WebServer/Documents/UM-Web-Workspace/HealthLibrary/cache";
log_debug("Set the cache directory: $sCacheDirectory");
$xFileCache->setFileCacheDirectory($sCacheDirectory);

$iExpirationInSeconds = 15;
log_debug("Set the expiration to (seconds): $iExpirationInSeconds");
$xFileCache->setExpiration( $iExpirationInSeconds );


log_debug("Create 3 cache entries");
$sData1 = uniqid();
$sData2 = uniqid();
$sData3 = uniqid();
$bSuccess = $xFileCache->writeDataToCache("Data1", $sData1);
log_debug("Data1 written to cache: $sData1");
log_debug("Return: " . var_export($bSuccess, true));

$bSuccess = $xFileCache->writeDataToCache("Data2", $sData2);
log_debug("Data2 written to cache: $sData2");
log_debug("Return: " . var_export($bSuccess, true));

$bSuccess = $xFileCache->writeDataToCache("Data3", $sData3);
log_debug("Data3 written to cache: $sData3");
log_debug("Return: " . var_export($bSuccess, true));


log_debug("Sleep for 10 seconds");
sleep(10);

$xRetData1 = $xFileCache->readDataFromCache("Data1");
log_debug("Retreive Data 1: " . var_export($xRetData1, true));

$xRetData2 = $xFileCache->readDataFromCache("Data2");
log_debug("Retreive Data 2: " . var_export($xRetData2, true));


log_debug("Sleep for 10 seconds");
sleep(10);

$xRetData3 = $xFileCache->readDataFromCache("Data3");
log_debug("Retreive Data 3: " . var_export($xRetData3, true));

log_debug("Now we want to test to make sure we can kepp a cache entry updated.");
$iExpirationInSeconds = 10;
log_debug("Set the expiration to (seconds): $iExpirationInSeconds");
$xFileCache->setExpiration( $iExpirationInSeconds );

log_debug("Write the cache entry every 5 seconds to keep it updated.  then after 30 seconds see if its still available.");

for($i=0; $i < 6; $i++)
{
	$bSuccess = $xFileCache->writeDataToCache("Data3", $sData3);
	log_debug("Data3 written to cache: $sData3");
	log_debug("Return: " . var_export($bSuccess, true));

	log_debug("Sleep for 5 seconds");
	sleep(5);
}

$xRetData3 = $xFileCache->readDataFromCache("Data3");
log_debug("Retreive Data 3: $xRetData3");

log_debug("Now lets wait 20 seconds and try and access it again.");
sleep(20);

$xRetData3 = $xFileCache->readDataFromCache("Data3");
log_debug("Retreive Data 3: $xRetData3");

log_debug("Flushing Cache");
$xFileCache->flushCache();

?>
