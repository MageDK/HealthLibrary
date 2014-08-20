<?php
/**
 * This object takes the response that was returned by the Health Library and 
 * parses out the information we need from the XML data.
 * 
 * The Schema that defines the XML structure of the Content.svc responses can be found at 
 * http://external.ws.StayWell.com/DocumentationV2/Content.xsd.
 * 
 * The Schemas which define the XML structure of the old document requests and responses 
 * can be found at http://external.ws.StayWell.com/DocumentationV2/Types.xsd and 
 * http://external.ws.StayWell.com/DocumentationV2/Markup.xsd
 * 
 * A variety of the important data we want to grab is part of the ContentObject
 * The following are all usually found as children of the ContentObject:
 * Language, RegularTitle, InvertedTitle, Blurb, Keywords, GenderCode
 * Gender, Rating, AgeGroups, CopyrightStatement, PostingDate, Content
 * Disclaimer, ReadingLevel, Authors, OnlineEditors, OnlineMedicalReviewers
 * PrintSources, AdditionalTitles, OnlineSources, RecommendedSites, Indexing
 * OtherLanguages, Servicelines
 * 
 * @package HealthLibrary
 * @author Emil Diego
 * @copyright 2014
 * emildiego@gmail.com
 * 
 */
class HealthLibraryResponse
{
	/**
	 * @var SimpleXMLElement m_xXmlDoc The SimpleXMLElement object that contains the XML returned by the request.
	 */
	private $m_xXmlDoc;
	
	/**
	 * @var boolean m_bValidXML Is set to true if the response was a valid XML documnet.
	 */
	private $m_bValidXML;
	
	/**
	 * @var string m_sLanguage The language of the content.
	 */
	private $m_sLanguage;
	
	/**
	 * @var string m_sRegularTitle The Regular Title contained int he response.
	 */
	private $m_sRegularTitle;
	
	/**
	 * @var string m_sKeywords Keywords that are associated with the content.
	 */
	private $m_sKeywords;
	
	/**
	 * @var string m_sGender The gender of the person the content relates to.
	 */
	private $m_sGender;
	
	/**
	 * @var string m_sCopyright The copyright statement that is associated with this content.
	 */
	private $m_sCopyrightStatement;
	
	/**
	 * @var string m_sPostingDate The date the content was updated.
	 */
	private $m_sPostingDate;
	
	/**
	 * @var string m_sBody The body of the content.
	 */
	private $m_sBody;
	
	
	
	/**
	 * Default Constructor
	 *
	 * Create an instance of the HealthLibraryResponse object.
	 *
	 * @param string sXmlContent A String containing the XML content returned by the request.
	 */
	function __construct( $sXmlContent = null )
	{
		log_debug("HealthLibraryResponse::__construct()");
		
		//* Make sure we have some contents in the string
		if ($sXmlContent == null || $sXmlContent == "")
		{
			log_debug("XML content is null or empty");
			$this->m_bValidXML = FALSE;
		}
		else
		{
			//* We have some data in the string.  lets try and convert it.
			log_debug("We have some data.  Let's try and convert it");
			$this->m_xXmlDoc = simplexml_load_string($sXmlContent);
			if ($this->m_xXmlDoc === FALSE)
			{
				//* we were not able to load the XML content
				log_debug("Unable to parse the XML");
				log_debug($sXmlContent);
				
				$this->m_bValidXML = FALSE;
			}
			else
			{
				//* We were able to load the XML.
				$this->m_bValidXML = TRUE;
			}
		}
	}
	
	/**
	 * isValidXMLDocument
	 * 
	 * Checks to see if the XML Document for this object is valid and has been successfully parsed.
	 * 
	 * @return boolean Returns true if we currently have a valid XML Document to query.
	 */
	public function isValidXMLDocument()
	{
		log_debug("HealthLibraryResponse::isValidXMLDocument()");
		log_debug("is Valid: " .  htmlentities(var_export($this->m_bValidXML, true)));
		
		return $this->m_bValidXML;
	}
	
	/**
	 * getRegularTitle
	 *
	 * Retrieve the value of the RegularTitle node of the XML document
	 *
	 * @return string a String containing the Body content from the XML Document.  Returns FALSE if the XML document is invalid.
	 */
	public function getRegularTitle()
	{
		//* Let's check to see if we have a valid XML Document
		if (!$this->isValidXMLDocument())
			return FALSE;
	
		return $this->m_sRegularTitle;
	}
	
	/**
	 * getBody
	 * 
	 * Retrieve the value of the Body node of the XML Document
	 * 
	 * @return string a String containing the Body content from the XML Document.  Returns FALSE if the XML document is invalid.
	 */
	public function getBody()
	{
		log_debug("HealthLibraryResponse::getBody()");
		
		//* Let's check to see if we have a valid XML Document
		if (!$this->isValidXMLDocument())
			return FALSE;
		
		return $this->m_sBody;
	}
	
	/**
	 * parseContent
	 *
	 * Pull out all the values of the XML content we are looking for by default.
	 *
	 * @return boolean Will return FALSE if there is no valid XML Document.  Otherwise returns TRUE if the content was successfully parsed.
	 */
	public function parseContent()
	{
		//* Let's check to see if we have a valid XML Document
		if (!$this->isValidXMLDocument())
			return FALSE;
		
		//print_r($this->m_xXmlDoc);
		
		//* Let's start querying the XML document to retrieve the data we want.
		//* Get the language
		$this->m_sLanguage = $this->m_xXmlDoc->ContentObject->Language;
		
		//* Get the RegularTitle
		$this->m_sRegularTitle = $this->m_xXmlDoc->ContentObject->RegularTitle;
		
		//* Get the Keywords
		$this->m_sKeywords = $this->m_xXmlDoc->ContentObject->Keywords;
		
		//* Get the Gender
		$this->m_sGender = $this->m_xXmlDoc->ContentObject->Gender;
		
		//* Get the Copyright Statement
		$this->m_sCopyrightStatement = $this->m_xXmlDoc->ContentObject->CopyrightStatement;
		
		//* Get the posting date
		$this->m_sPostingDate = $this->m_xXmlDoc->ContentObject->PostingDate;
		
		//* Let's get the body
		$this->m_sBody = $this->m_xXmlDoc->ContentObject->Content->asXML();
		
		print_r($this->m_xXmlDoc->ContentObject->Content);
		
		
		foreach ($this->m_xXmlDoc->ContentObject->Content->children() as $second_gen)
		{
			writelineBR("Child: " . $second_gen->getName());	
		}
		
		return TRUE;
	}
}
?>