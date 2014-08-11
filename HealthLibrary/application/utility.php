<?php
/**
 Utility functions for all PHP applications
 * 
 * @package HealthLibrary
 * @author Emil Diego <emildiego@gmail.com>
 * @copyright Copyright (c) 2014
 * @version 1.0.0
 */
 
/**
 * Convert a string date into a mysql friendly version.  The formatiing of the 
 * source string needs to be recognized by strtotime to work.
 * 
 * @param string sStringDate The date string we want to convert.
 * 
 * @return string A string with the date formatted for mysql.
 */
function convertStringToDate( $sStringDate )
{
	log_debug("Date to convert: $sStringDate");
	
	//* Check to see if we have a date otherwise return emtpy string
	if ($sStringDate == "")
	{
		log_debug("Empty Date");
		return "";
	}
	
	//* Convert the string date into a unix date
	$xDate = strtotime($sStringDate);
	if ($xDate == FALSE)
	{
		log_error("Unable to convert string to date: $sStringDate");
		return "";
	}
	
	$sTmpDate = date("Y-m-d", $xDate);
	log_debug("Successfuly converted date: $sTmpDate");
	
	return $sTmpDate;
	
}

/**
 * The cascade server uses a date picker that formats the date as MM-DD-YYYY.
 * for some reason strtotime does not recognize that format.  So we need a 
 * seperate function to handle the date conversions here.
 * 
 * @param string sStringDate The Cascade Date string we want to convert.
 * 
 * @return string A string with the data formatted using the mysql date format.
 */
function convertCascadeDateToMysqlDate( $sStringDate )
{
	log_debug("Date to convert: $sStringDate");
	
	//* Check to see if we have a date otherwise return emtpy string
	if ($sStringDate == "")
	{
		log_debug("Empty Date");
		return "";
	}
	
	//* Lets see how long it is.  Should have 10 characters.
	$iLength = strlen($sStringDate);
	if ($iLength > 10)
	{
		//* There is something weird in this date
		log_error("Invalid String: $sStringDate");
		return false;
	}
	
	//* Lets reformat the date/time from MM-DD-YYYY to Y-m-d
	$xDateTime = substr($sStringDate, 6, 4) . "-" . substr($sStringDate, 0, 2) . "-" . substr($sStringDate, 3, 2);
		   	
	log_debug("Date successfully converted: " . $xDateTime);
	
	//* We successfully created the date
	//* Now lets return it using the specified format
	return $xDateTime;
	
}

/**
 * Retreive a HTTP POST/GET Parameter
 * 
 * @param string $sParameterName The name of the POST/GET parameter we want to retreive.
 * 
 * @return string A string with the value of the POST/GET parameter.
 */
function getParameter( $sParameterName )
{

	$sRequestMethod = $_SERVER['REQUEST_METHOD'];

	//* Find out what request method we are using
	if ( $sRequestMethod == "POST" )
	{
		//* We are using POST
		log_debug("getParameter( $sParameterName ) = $_POST[$sParameterName]");
		return $_POST[$sParameterName];
	}
	else
	{
		//* We are using GET
		log_debug("getParameter( $sParameterName ) = $_GET[$sParameterName]");
		return $_GET[$sParameterName];
	}

}

/**
 * Write the string to the HTML output.
 * 
 * @param string $sStr The string we want to write to the HTML output.
 */
function write($sStr)
{

	print($sStr);

}

/**
 * Write the string to the HTML output and append a carriage return to the end of it.
 * 
 * @param  string $sStr The string we want to write to the HTML output.
 */
function writeline($sStr)
{
	print($sStr . CFG_CR);
}

/**
 * Add a <br/> tag to the output for html formatting purposes.
 * 
 * @param string $sStr The string we want to write to the HTML output.
 */
function writelineBR($sStr)
{
	writeline($sStr . "<br/>");
}
/**
 * use the cURL library to get the contents of the URL.
 * 
 * @param string $sURL a string containing a properly formated URL.
 */
function getUrl($sUrl)
{
	//* First we want to check to see if curl is installed
	$bIsInstalled =  function_exists('curl_version');
	if (!$bIsInstalled)
	{
		//* The curl library is not installed.
		log_error("curl library is not installed.");
		return "";
	}
	
	//* Initilize the curl library.
	$ch = curl_init();
	
	//* Setup the parametes for the request
	curl_setopt($ch, CURLOPT_URL, $sUrl);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	//* get the contents
	$sData = curl_exec($ch);
	
	//* close everything down
	curl_close($ch);
	
	return $sData;
}

/**
 * Write a message to the log file.  This function should not be used directly.
 * Use the log_error, log_debug, log_sql functions instead.
 * 
 * @param string $sMsg A string with the message we want to write to the log.
 * @param integer $iLogLevel The severity of the message we want to write.
 */
function writelog($sMsg, $iLogLevel)
{
	//* Get the address of the computer executign the script
	$ipSrc = getenv('REMOTE_ADDR');
	//$ipSrc = $_SERVER['REMOTE_ADDR'];
	$sTmpMsg = "";
	
	//* Check the log level to see if we want to log this message
	if ($iLogLevel < CFG_LOG_LEVEL)
	{
		//* We Don't
		return;
	}
	
	//* construct the message string
	if ($iLogLevel == CFG_LOG_ERROR)
	{
		$sTmpMsg = "[$ipSrc] ERR:";
	}
	else if ($iLogLevel == CFG_LOG_SQL)
	{
		$sTmpMsg = "[$ipSrc] SQL:";
	}
	else if ($iLogLevel == CFG_LOG_ERROR)
	{
		$sTmpMsg = "[$ipSrc] WAR:";
	}
	else if ($iLogLevel == CFG_LOG_DEBUG)
	{
		$sTmpMsg = "[$ipSrc] DEB:";
	}
	
	//* add the name of the script file
	if (function_exists('date_default_timezone_set'))
	{
		date_default_timezone_set ( "America/New_York" );
	}
	$sTmpMsg .= "" . date("m/d/Y - H:i:s | ") . $sMsg . CFG_CR;
	
	//* Log the message-
	if (CFG_LOG_TO == "3")
	{
		//* Log the message to a user defined file
		error_log( $sTmpMsg, 3, CFG_LOG_FILE);
	}
	else if (CFG_LOG_TO == "1")
	{
		//* THe message goes to the php system logger
		error_log( $sTmpMsg , 0);
	}

} 


/**
 * Log a error message to the log file.
 * 
 * @param string $sMsg A string with the message we want to write to the log.
 */
function log_error($sMsg) { writelog($sMsg, CFG_LOG_ERROR); }

/**
 * Log a SQL statement to the log file.
 * 
 * @param string $sMsg A string with thre SQL statement we want to write to the log file.
 */
function log_sql($sMsg) { writelog($sMsg, CFG_LOG_SQL); }

/**
 * Log a debug message to the log file.
 * 
 * @param string $sMsg A string with the DEBUG message we want to write to the log file.
 */
function log_debug($sMsg) { writelog($sMsg, CFG_LOG_DEBUG); }



/**
 * Uses a regular expression to check to see if the string 
 * provided is a valid email address.  Note: this does not check
 * for a valid email, just a valid formatted address.
 * (Wicked Cool PHP  pg56)
 * 
 * @param string $sEmail The email address we want to check.
 * @return boolean True if its a valid formatted email, false if it isn't.
 */
function isValidEmailFormat($sEmail)
{
	if (! preg_match( '/^[A-Za-z0-9!#$%&\'*+-/=?^_`{|}~]+@[A-Za-z0-9-]+(\.[A-Za-z0-9]+)+[A-Za-z]$/', $sEmail))
	{
		return false;
	}
	
	return true;
}


/**
 * mkdir recursive.  Makes a directory in a recursive way.  Will builld the whole path if it doesn't already exist.
 * 
 * @param string $dirName The directory we want to create
 * @param integer $rights The permissions we want to set on the directories
 * @return boolean True if the directory was created successfully.
 */
function mkdir_r($dirName, $rights=0777){
    $dirs = explode('/', $dirName);
    $dir='';
    foreach ($dirs as $part) {
        $dir.=$part.'/';
        if (!is_dir($dir) && strlen($dir)>0)
        {
					log_debug("Mkdir: $dir ($rights)");
          mkdir($dir, $rights);
        }
    }    
    return true;
}

/**
 * Connect to a database using the system defined values if none are specified.
 * 
 * @param string $sUserId The user id we want to use to login to the database.
 * @param string $sPassword The password for the user id.
 * @param string $sServer The mysql database server hostname.
 * @param string $sSchema The name of the database we want to use.
 * @return db a Database object that we can use to make queries.
 */
function connectToDatabase($sUserId, $sPassword, $sServer, $sSchema)
{
	//* Check the parameters
	if (is_set($sUserId))
	{
		$sLclUserId = $sUserId;
	}
	else
	{
		$sLclUserId = CFG_DB_USER;
	}
	
	if (is_set($sPassword))
	{
		$sLclPassword = $sPassword;
	}
	else
	{
		$sLclPassword = CFG_DB_PASSWD;
	}
	
	if (is_set($sServer))
	{
		$sLclServer = $sServer;
	}
	else
	{
		$sLclServer = CFG_DB_SERVER;
	}
	
	$myConn = new db($sLclServer, $sLclSchema, $sLclUserId, $sLclPassword);
	
	return $myConn;

}


/**
 * Send an email using the PHPMAILER application.  
 * 
 * @param string $sAddress
 * @param string $sBody
 * @param string $sSubjectLine
 * @param string $sAttachment
 */
function sendEmail($sAddress, $sBody, $sSubjectLine, $sAttachment)
{
	log_debug("sendEmail()");
	
	

	//* Crreate our instance of the phpmailer and set the default language
	$mail = new PHPMailer();
	$mail->SetLanguage("en", CFG_EMAIL_PHPMAILER_HOME . "/language/");

	//* Setup the mail host to use
	if (CFG_EMAIL_METHOD == "SENDMAIL")
	{
		//* We want to send the emails using sendmail
		log_debug("Sending email via sendmail");
		
		$mail->IsSendmail();
	}
	else if (CFG_EMAIL_METHOD == "SMTP")
	{
		//* We want to use SMTP to send them
		log_debug("Sending email via SMTP: " . CFG_EMAIL_SERVER_SMTP);
		$mail->IsSMTP();
		$mail->Host = CFG_EMAIL_SERVER_SMTP;
	}

	$mail->IsHTML(false);
	$mail->From = CFG_EMAIL_FROM_ADDRESS;
	$mail->FromName = CFG_EMAIL_FROM_NAME;
	
	//* Not really needed, but lets do it anyway
	$mail->ClearAddresses();	
	$mail->Subject = $sSubjectLine;

	//* Let's see if we have on attachment or multiple
	if (is_array($sAttachment))
	{
		//* we do have multiple attachments
		$iLength = count($sAttachment);
		for ($i = 0; $i < $iLength; $i++)
		{
			$mail->addAttachment($sAttachment[$i]);
			log_debug("Adding attachment: " . $sAttachment[$i]);
		}
	}
	else
	{
		//* we just have the one.
		$mail->addAttachment($sAttachment);
		log_debug("Adding attachment: " . $sAttachment);
	}

	
	//* Add the content to the body of the email
	$mail->Body		= $sBody;
	$mail->WordWrap	= 80;	

	//* Add the destination address
	$mail->addAddress( $sAddress );
	
	//* send the mail
	if(!$mail->Send()) {	
		//* An error occured while trying to send the email	
		$sMsg = "An error occured while trying to send submission email: " . $mail->ErrorInfo;
		log_error($sMsg);
		writeToLog($sMsg);
		die($sMsg);
	}
	log_debug("Email notification sent to $sAddress");
}

?>
