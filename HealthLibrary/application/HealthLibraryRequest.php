<?php
/**
 * This object queries the Health Library knowledge base provided by Krames StayWell.
 * 
 * General Info about StayWell Knowlege Base:
 * The Web Services are available at URLs under: http://external.ws.StayWell.com/[SiteName]/ 
 * (where [SiteName] would be substituted with the appropriate value for the client license).
 * 
 * The Schema that defines the XML structure of the Content.svc responses can be found at 
 * http://external.ws.StayWell.com/DocumentationV2/Content.xsd.
 * 
 * The Schemas which define the XML structure of the old document requests and responses 
 * can be found at http://external.ws.StayWell.com/DocumentationV2/Types.xsd and 
 * http://external.ws.StayWell.com/DocumentationV2/Markup.xsd
 * 
 * The Krames StayWell Web Service interface allows clients to:
 * Retrieve content with associated metadata
 * Execute searches for content
 * Retrieve lists of content related to other content or a set of MeSH concepts
 * 
 * @package HealthLibrary
 * @author Emil Diego
 * @copyright 2014
 * emildiego@gmail.com
 * 
 */

class HealthLibraryRequest
{
	/**
	 * @var string m_sBaseUrl The base URL for the Library.
	 */
	private $m_sBaseUrl;
	
	/**
	 * @varstring m_sHttpMethod Specify if we will use HTTP GET or POST to pass the parameters.  Defaults to POST.
	 */
	private $m_sHttpMethod;
	
	/**
	 * @var string m_sSiteName The site name used to identify our particular license.
	 */
	private $m_sSiteName;
	
	/**
	 * @var string m_sServiceName The name of the Web Service class being accessed. 
	 * Currently the choices are "Content.svc", "Documents.svc.  Defaults to Content.svc
	 */
	private $m_sServiceName;
	
	/**
	 * @var string m_sParamArray The array of parameters that will be passed in the request.
	 */
	private $m_sParamArray;
	
	/**
	 * @var string m_sFunctionName The function that will be used in our request.
	 */
	private $m_sFunctionName;
	
	/**
	 * @var string m_sRequestUrl The complete URL request that will be sent to the Health Library.
	 */
	private $m_sRequestUrl;
	
	/**
	 * @var string m_sResponse The response that is returned by the request.
	 */
	private $m_sResponse = "";
	
	/**
	 * @var integer m_iResponseSize The size of the response it bytes.
	 */
	private $m_iResponseSize = 0;
	
	/**
	 * @var integer m_iTotalBytesDownloaded The total number of bytes that van been uploaded.
	 */
	private $m_iTotalBytesDownloaded = 0;
	
	/**
	 * @var integer m_iHttpResponseCode The HTTP Response code returned by the request
	 */
	private $m_iHttpResponseCode = 0;

	/**
	 * Default Constructor
	 *
	 * Create an instance of the HealthLibraryRequest object.
	 *
	 * @param string sBaseUrl The base URL used to query the library.
	 * @param string sSiteName The site name used to identify our particular license.
	 * @param string sServiceName The name of the Web Service class being accessed.  Should be "Content.svc" for most functions. 
	 * @param string sHttpMethod Specify if we use HTTP GET or POST to make the request.  Default to POST.
	 */
	function __construct( $sBaseUrl = "http://external.ws.staywell.com/", 
								 $sSiteName = "umiamikbws", 
								 $sServiceName = "Content.svc",
								 $sHttpMethod = "POST" )
	{
		log_debug("HealthLibraryRequest::__construct()");
		
		//* Initialize out member variables
		$this->m_sBaseUrl 				= $sBaseUrl;
		$this->m_sSiteName				= $sSiteName;
		$this->m_sServiceName			= $sServiceName;
		$this->m_sHttpMethod			= $sHttpMethod;
		
		$this->m_sParamArray			= array();
		
		log_debug("BaseURL = " . $this->m_sBaseUrl);
		log_debug("SiteName = " . $this->m_sSiteName);
		log_debug("ServiceName = " . $this->m_sServiceName);
		log_debug("HTTPMethod = " . $this->m_sHttpMethod);
	}
	
	/**
	 * Generate the Basic URL that will be used to send a request to the Health Library
	 * Knowledge Base.  This URL does not include any parameters yet.
	 * 
	 * @param string sFunctionName The name of the function we want to execute.
	 * 
	 * @return string Returns the URL that was created.
	 */
	private function createRequestUrl( $sFunctionName )
	{
		$this->m_sFunctionName = $sFunctionName;
		
		$sRequestUrl = $this->m_sBaseUrl . $this->m_sSiteName . "/" . $this->m_sServiceName . "/" . $this->m_sFunctionName;
			
		return $sRequestUrl;
	}
	
	/**
	 * Return the total number of bytes that were downloaded in the response.
	 * 
	 * @return integer The total number of bytes that were downloaded in the response. 
	 */
	public function getTotalBytesDownloaded()
	{
		return $this->m_iTotalBytesDownloaded;
	}
	
	/**
	 * Return the HTTP Response code for the last request.
	 * 
	 * @return integer The HTTP Response code for the last request.
	 */
	public function getHTTPResponseCode()
	{
		return $this->m_iHttpResponseCode;
	}
	
	/**
	 * Return the Request URL used
	 * 
	 * @return string The request URL that was sent int he request.
	 */
	public function getRequestUrl()
	{
		return $this->m_sRequestUrl;
	}
	
	/**
	 * Submits the request URL and retreives the response from the server.  Uses the curl library for this.
	
	 * @return string The response that was received from the Krames Library Server
	 */
	private function submitHttpRequest()
	{
		log_debug("HealthLibraryRequest::submitHttpRequest()");
		
		$sResponse = "";
		
		//* Collect and format the parameters for the request
		$sParamneters = $this->collectParameters();
		
		//* Initialize a cURL session
		$xCurl = curl_init();
		
		//* add the xmlRequest parameter
		$sParamString = "xmlRequest=" . urlencode($sParamneters);
		log_debug("Param String: $sParamString");
		
		log_debug("HTTP Request Method: " . $this->m_sHttpMethod);
		if ($this->m_sHttpMethod == "POST")
		{
			//* set the options needed for the POST request
			curl_setopt($xCurl, CURLOPT_URL, $this->m_sRequestUrl);				//* Set the URL
			curl_setopt($xCurl, CURLOPT_HEADER, FALSE);							//* Don't return the header
			curl_setopt($xCurl, CURLOPT_POST, 1);								//* We only have 1 paramenter xmlRequest
			curl_setopt($xCurl, CURLOPT_POSTFIELDS, $sParamString);				//* pass the paramneter string
			curl_setopt($xCurl, CURLOPT_RETURNTRANSFER, TRUE);					//* return web page		
		}
		else 
		{
			//* Set the options needed for a GET request
			curl_setopt($xCurl, CURLOPT_URL, $this->m_sRequestUrl . "?$sParamString");	//* Set the URL and add the query parameters
			curl_setopt($xCurl, CURLOPT_HEADER, FALSE);									//* Don't return the header
			curl_setopt($xCurl, CURLOPT_RETURNTRANSFER, TRUE);							//* return web page
			curl_setopt($xCurl, CURLOPT_CUSTOMREQUEST, 'GET');							//* Make this a GET request
		}

		//* Execute the request
		$sResponse = curl_exec($xCurl);
		
		//* Get some information 
		$this->m_iHttpResponseCode		= intval(curl_getInfo($xCurl, CURLINFO_HTTP_CODE));
		$this->m_iTotalBytesDownloaded	= intval(curl_getInfo($xCurl, CURLINFO_SIZE_DOWNLOAD));
		
		log_debug("HTTP Response Code: " . $this->m_iHttpResponseCode);
		log_debug("Total Bytes Downloaded: " . $this->m_iTotalBytesDownloaded);
		
		//* Lets see if there was an error
		$iErrorNo = curl_error($xCurl);
		if ($iErrorNo > 0)
		{
			//* there was an error
			log_error("Unable to get a response: " . $this->m_sRequestUrl);
			
			return null;
		}
		
		log_debug( "Response" );
		log_debug($sResponse);
		
		return $sResponse;
	}
	
	/**
	 * Add a request parameter to the parameter array.  If the paramenter doesn't already exist, 
	 * its gets added.  If it does already exist then it overwrites the value.
	 * 
	 * @param string $sParamName The name of the parameter we want to add.
	 * @param string $sParamValue The value of the paramenter.
	 * 
	 * @return boolean Return TRUE if the paramenter was added to the array successfully.
	 */
	public function addParameter( $sParamName, $sParamValue )
	{
		//* Make sure the paramenter name is not null or empty.
		if ($sParamName == null || $sParamName == "")
			return false;
	
		$this->m_sParamArray[$sParamName] = $sParamValue;
		
		return true;
	}
	
	/**
	 * Remove a request paramaneter from the paramenter array.  This will remove it from the request.
	 * 
	 * @param string $sParamName The name of the paramneter we want to remove.
	 * 
	 * @return boolean Return TRUE if the paramenter was removed successfully, FALSE if the paramenter does not exist
	 */
	public function deleteParameter( $sParamName )
	{
		//* Check to see if the key exists.
		$bExists = array_key_exists ( $sParamName , $this->m_sParamArray );
		if ($bExists)
		{
			//* The key exists, we need to remove it from the array
			unset( $this->m_sParamArray[$sParamName] );
			return true;
		}
		
		return false;
	}
	
	/**
	 * Collects all the parameters together and returns them as a string.
	 * 
	 * @param string $sMethod The HTTP method being used by the request.
	 * 
	 * @return string The urlencoded parameter string to be passed.
	 * 
	 */
	private function collectParameters()
	{
		$sTmpParameters = "";
		
		//* Get the list of paramneters in the array
		$sParamNameArray = array_keys( $this->m_sParamArray );
		
		if ($this->m_sHttpMethod == "POST" || $this->m_sHttpMethod == "GET")
		{
			/**
			 * Collect all the parameters together to be passed as one argument
			 * to the xmlRequest.
			 *
			 * ?xmlRequest=<GetContent ContentTypeId="22" ContentId="AsthmaCustom" IncludeBlocked="false" GetOriginal="false" />
			 */
			$sTmpParameters = "<" . $this->m_sFunctionName . " ";
			
			//* add all the parameter / values to the string
			foreach ($sParamNameArray as $sParamName)
			{
				$sTmpParameters .= "$sParamName=\"" . $this->m_sParamArray[$sParamName] . "\" ";
				
			}
			
			//* End the xmlRequest
			$sTmpParameters .= "/>";
		}
		
		log_debug("Parameters: " . $sTmpParameters);
			
		//* return the string of params
		return $sTmpParameters;
		
		
	}
	
	/**
	 * Query the Knowledge Base Library and retreive the response.
	 * 
	 * @param string sFunctionName An optional paramenter to specify the function name.  If not specified will default to GetContent
	 * 
	 * @return mixed Returns a String containing the Response, or NULL of there was an error
	 */
	public function getResponse($sFunctionName = "GetContent")
	{
		$this->m_sResponse = "";
		
		//* Create the request
		$this->m_sRequestUrl = $this->createRequestUrl($sFunctionName);
		
		log_debug("Functin Name: " . $this->m_sFunctionName);
		log_debug("RequestURL: " . htmlentities($this->m_sRequestUrl));
		
		//* now that we have our Requesr URL.  We can submit the request and get te response.
		$this->m_sResponse = $this->submitHttpRequest();
		
		return $this->m_sResponse;
	}
}
?>