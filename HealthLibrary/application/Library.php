<?php
/**
 * The main object for interacting with the Krames StayWell library.  This object will handle 
 * all the requests for content from the library.
 * 
 * The Library will use a file cache to store the responses from the library.  The Library will serve 
 * up the content from the Cache if the content is recent and has not expired.  By default the cache expiration is set 
 * to 30 minutes (1800 seconds).
 * 
 * 
 * @package HealthLibrary
 * @author Emil Diego
 * @copyright 2014
 * emildiego@gmail.com
 * 
 */

class Library
{
	/**
	 * @var object m_xCache An object of type FileCache used to store request responses.
	 */
	private $m_xCache;
	
	/**
	 * @var object m_xLibraryRequest An object of type HealthLibraryRequest used to request content from the libreary and retreive the response.
	 */
	private $m_xLibraryRequest;
	
	
	/**
	 * Default Constructor
	 *
	 * Create an instance of our Library object.
	 * 
	 * @param string sCacheLocation The local folder that will be used to store our cache data.  Defaults to current directory ".".
	 */
	public function __construct($sCacheLocation = ".")
	{
		
		//* Initilaize our Cache.
		$this->m_xCache = new FileCache();
		$this->m_xCache->setFileCacheDirectory($sCacheLocation);
		$this->m_xCache->setExpiration(1800);
		
		//* Initialize our request object.
		$this->m_xLibraryRequest = new HealthLibraryRequest();
		
	}
	
	/**
	 * Generate the data id from the content id and content type.  The data id 
	 * is used to identify the request in the caceh.
	 * 
	 * @param string $sContentId The content ID
	 * @param string $sContentTypeId The id of the content type
	 */
	private function generateDataId( $sContentId, $sContentTypeId )
	{
		//* The Hast function used
		$sHashFunction = "ripemd160";
		
		//* store the 2 ID's in an array
		$xData[0] = $sContentId;
		$xData[1] = $sContentTypeId;
		
		$sData = implode("|", $xData);
		
		//* hash the data
		$sHash = hash( $sHashFunction,  $sData);

		return $sHash;
	}
	
	/**
	 * Retreive the content for the specified content id and content type.
	 * 
	 * @param string $sContentId The Content ID
	 * @param string $sContentTypeId The id of the content type
	 * 
	 * @return string Returns a string containing the response data.  NULL if an error was encountered.
	 */
	public function getContent($sContentId, $sContentTypeId)
	{
		//* First thing we need to do is check to see if the response is in the 
		//* cache.
		$sDataId = $this->generateDataId($sContentId, $sContentTypeId);
		$bInCache = $this->m_xCache->doesExistInCache($sDataId);
		if ($bInCache)
		{
			//* The response is in the cahce.  We need to check to see if its expired
			$bSuccess = $this->m_xCache->isValid( $sDataId );
			if ($bSuccess)
			{
				//* The response has not expired.  Let's return it.
				return $this->m_xCache->readDataFromCache($sDataId);
			}
		}
		
		//* The data is either not in te cache or expired.
		$this->m_xLibraryRequest = new HealthLibraryRequest();
		$bSuccess = $this->m_xLibraryRequest->addParameter("ContentTypeId", $sContentTypeId);
		$bSuccess = $this->m_xLibraryRequest->addParameter("ContentId", $sContentId);
		
		//* Get the response from the library.
		$sResponse = $this->m_xLibraryRequest->getResponse();
		
		//* Check the response code and if it was "200" then we got a valid response
		if ($this->m_xLibraryRequest->getHTTPResponseCode() != "200")
		{
			//* We encountered an error
			return  null;
			
		}
		else
		{
			//* There was no error.  Everything was returned successfully.
			return $sResponse;
		}
		
		return null;
		
	}
}



?>