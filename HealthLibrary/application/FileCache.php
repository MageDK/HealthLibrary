<?php
/**
 * This object performs the function of caching data requests to the local file system.
 * The can be used to reduce the 
 * number of requests that need to be made to retrieve remote content.  The cache uses a data id to 
 * identify each cache entry.  It is up to the caller to generate unique data id's for each 
 * item that we want to cache.  The easiest method for this is using a Hash function to combine
 * the identifying information of the request into a single unique value.
 * 
 * The FileCache object must have access to read/write to a single directory.  The directory
 * will be where the cache data is kept.
 * 
 * The FileCache will also allow the ability to clear all the cache contents.
 * 
 * @package HealthLibrary
 * @author Emil Diego
 * @copyright 2014
 * emildiego@gmail.com
 * 
 */

class FileCache
{
	/**
	 * @var string The filesystem directory used to store cache data.  Defaults to the current directory "."
	 */
	private $m_sCacheDirectory	= ".";
	
	/**
	 * @var string The number of secids befire an entry in the cache is expired.  Default is 15v minutes.
	 */
	private $m_iExpireInSeconds = "900";
	
	/**
	 * Default Constructor
	 * 
	 * Create an instance of the FileCache object.
	 * 
	 */
	public function __construct( )
	{
		//* Doesn't really do anything here.  Just nice to have incase.
	}
	
	/**
	 * Sets the expiration of the cache.
	 * 
	 * @param integer $iExpirationInSeconds The expiration value for the cache items in seconds.
	 */
	public function setExpiration( $iExpirationInSeconds )
	{
		$this->m_iExpireInSeconds = $iExpirationInSeconds;
	}
	
	/**
	 * Set the directory to use to store our cache data
	 * 
	 * @param string $sDirectory The path to the directory we want to use to store our cache.
	 * 
	 * @return boolean Returns TRUE if the specified directory was successfully set.  Will check to make sure the directory exists and is writable.
	 */
	public function setFileCacheDirectory( $sDirectory )
	{
		//* let's check the directory to make sure there are no issues with it.
		//* 1. Check to make sure the directory exists
		$bIsDirectory = is_dir( $sDirectory );
		if (!$bIsDirectory)
		{
			//* The specified path may not be a directory or may not exist.
			log_error("The specified path may not exist or may not be a directory: $sDirectory");

			return false;
		}
		
		//* 2. Check to make sure we can write to it. 
		$bIsWritable = is_writable( $sDirectory );
		if (!$bIsWritable)
		{
			//* The directory may not exist or be writable.
			log_error("The specified path may not exist or may not be writable: $sDirectory");
		}
		
		//* Set the directory
		$this->m_sCacheDirectory = $sDirectory;
		return true;
	}
	
	/**
	 * A utility method to generate the filename for the item we want to retreive from the cache.
	 * 
	 * @param string sDataId The Unique ID of the data we want to check the cache for.
	 */
	protected function createCacheFileName( $sDataId )
	{
		$sTmpS = $this->m_sCacheDirectory . DIRECTORY_SEPARATOR . $sDataId;
		
		return $sTmpS;
	}
	
	/**
	 * Deletes all the files in the cache folder, effectively expiring all the cached content.
	 * 
	 * @return boolean Returns true if all the content was successfully flushed.
	 */
	public function flushCache()
	{
		//* get all the files in the directory
		$files = glob("" . $this->m_sCacheDirectory . "/*"); 
		
		//* for each file, lets delete it.
		foreach($files as $file)
		{
			//* check to make sure we have a file.
			if(is_file($file))
			{
				//* we have a file, lets delete it.
				unlink($file);
			}
				
		}
		return true;
	}
	
	
	/**
	 * Checks the file cahce for the data represented by the unique id.  If there is a entry in the cache for
	 * it then return True, else return False.
	 * 
	 * @param string sDataId The Unique ID of the data we want to check the cache for.
	 */
	public function doesExistInCache( $sDataId )
	{
		//* make sure we have a non null data id.
		if ($sDataId == null || $sDataId == "")
		{
			return false;
		}
		
		//* Check to see if we have a file with the cached data in it.
		$sCacheFile = $this->createCacheFileName($sDataId);
		$bSuccess = file_exists($sCacheFile);
		
		return $bSuccess;
		
	}
	
	/**
	 * Check to see if we have exceeded the expirtation.
	 *
	 * @param string $sDataId The unique ID of the data we want to check.
	 *
	 * @return boolean Returns TRUE if the content has expired, FALSE if it hasn't.
	 */
	protected function hasExpired( $sDataId )
	{
		log_debug("FileCache::hasExpired");
	
		//* if the expiration time is 0, then the content never expires.
		if ($this->m_iExpireInSeconds == 0)
			return FALSE;
	
		//* get the name of the cache file
		$sCacheFile = $this->createCacheFileName($sDataId);
		log_debug("Cache File: $sCacheFile");
	
		//* Check the last time the file was modified.
		$iModTime = filemtime ( $sCacheFile );
		log_debug("Mod Time: $iModTime (" . date(CFG_DT_SQL_DATE_TIME, $iModTime) . ")");
	
		//* Get the current time.
		$iCurrTime = time();
		log_debug("Current Time: $iCurrTime (" . date(CFG_DT_SQL_DATE_TIME, $iCurrTime) . ")");
		
	
		//* subtract the expiration time from the current time.  That's our time of expiration.
		$iExpirationTime = $iCurrTime - $this->m_iExpireInSeconds;
		log_debug("Expiration Time: $iExpirationTime (" . date(CFG_DT_SQL_DATE_TIME, $iExpirationTime) . ")");
		
	
		//* check to see if the content has expired
		if ( $iModTime < $iExpirationTime)
		{
			//* The content has expired
			log_debug("The item has expired");
			//* If the item has expired it needs to be deleted from the cache.
			$bSuccess = unlink($sCacheFile);
			if (!$bSuccess)
			{
				//* we were unable to delete the cache file.
				log_error("Unable to delete cache file: $sCacheFile");
			}
			log_debug("deleting cache file: $sCacheFile");
			return TRUE;
		}

		//* it hasn't expired.
		return FALSE;
	}
	
	/**
	 * Checks to see if the cache data requested is valid or not.  By valid I mean that it exists
	 * in the cache and is not expired.
	 * 
	 * @param string sDataId The Unique ID of the data we want to check the cache for.
	 */
	public function isValid( $sDataId )
	{
		//* make sure we have a non null data id.
		if ($sDataId == null || $sDataId == "")
		{
			return false;
		}
		
		//* check to see if the data has expired.
		$bHasExpired = $this->hasExpired($sDataId);

		//* if the content has expired, then return false.  If it hasn't, return true.
		return !$bHasExpired;
	}
	
	/**
	 * Write some data to te cache
	 * 
	 * @param string sDataId The Unique ID of the data we want to cache.
	 * @param string sData The data we want to cache.
	 * 
	 * @return boolean Returns TRUE if the data was successfully written to the cache, FALSE if it wasn't.
	 */
	public function writeDataToCache( $sDataId, $sData )
	{
		//* make sure we have a non null data id.
		if ($sDataId == null || $sDataId == "")
		{
			return false;
		}
		//* We need to write the data to a file.
		$sFileName = $this->createCacheFileName($sDataId);
		$iNumBytesWritten = file_put_contents( $sFileName, $sData );
		if ($iNumBytesWritten != FALSE)
		{
			//* We have successfully written the data to the file
			return TRUE;
		}
	}
	
	/**
	 * Read some data from the cahce.
	 * 
	 * @param string sDataId The Unique ID of the data we want to retrieve from the cache.
	 * 
	 * @return mixed The data we want to retreive, or FALSE if it doesn't exist in the cache or is expired.
	 */
	public function readDataFromCache( $sDataId )
	{
		log_debug("FileCache::readDataFromCache($sDataId)");
		
		//* make sure we have a non null data id.
		if ($sDataId == null || $sDataId == "")
		{
			return false;
		}
		
		//* Let's see if the data exists in the cache.
		$bExists = $this->doesExistInCache($sDataId);
		log_debug("bExists: " . var_export($bExists, true));
		if (!$bExists)
		{	
			//* The data does not exist
			return FALSE;
		}
		
		$bIsValid = $this->isValid($sDataId);
		log_debug("bIsValid: " . var_export($bIsValid, true));
		if ($bIsValid)
		{
			//* Read the data from the cache
			$sFileName = $this->createCacheFileName($sDataId);
			
			return file_get_contents( $sFileName );
		
		}
		return FALSE;
	}
}

?>