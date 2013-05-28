<?php

//require './kookoolib-KooKoo-PHP-a8fe864/kookoophp/response.php';

function connectToMySQL(){
  $db_user = "tictoc";
  $db_pass = "toctic";
  $db_db = "CarPooling";
  mysql_connect("localhost", $db_user, $db_pass) or die("ERROR: MySQL cannot connect.");
  mysql_select_db($db_db) or die("ERROR: MySQL cannot select database.");
}

connectToMySQL();

$api_key = 'KKfc0789e81353f5544e8f50f1d0d4f2af';
$key = urlencode($apiKey);

$event=$_REQUEST['event'];
$number=$_REQUEST['cid'];
$from = urlencode($number);
$message=$_REQUEST['message'];
$datetime=$_REQUEST['time'];

$sms = explode(" ",$message);
if(strtolower($sms[0]) == 'register')
{
	if(strtolower($sms[1]) == 'agent')
	{
		mysql_query("INSERT INTO `User` (`first_name`,`last_name`,`mobile_number`,`type`) VALUES ('".$sms[2]."','".$sms[3]."','".$number."','AGENT')");
		
		$userIDRow = mysql_query("SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."'");
		$userID = mysql_fetch_array($userIDRow);
		mysql_query("INSERT INTO `Agent` (`user_id`) VALUES ('".$userID[0]."')");
		
		$newRegistrationSMS = "Hello ".$sms[2].", please provide info about your company. Send 'CARPOOL info <company name>' to 09227507512.";
		$reply = urlencode($newRegistrationSMS);

		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	else
	{
		mysql_query("INSERT INTO `User` (`first_name`,`last_name`,`mobile_number`,`type`) VALUES ('".$sms[1]."','".$sms[2]."','".$number."','RIDER')");

		$userIDRow = mysql_query("SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."'");
		$userID = mysql_fetch_array($userIDRow);
		mysql_query("INSERT INTO `Rider` (`user_id`) VALUES ('".$userID[0]."')");

		$newRegistrationSMS = "Hello ".$sms[1].", please provide info about your gender and if you are a smoker. Send 'CARPOOL info <M/F> <S/NS>' to 09227507512.";
		$reply = urlencode($newRegistrationSMS);

		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
}

if(strtolower($sms[0]) == 'info')
{
	$typeRow = mysql_query("SELECT `type` FROM `User` WHERE `mobile_number` = '".$number."'");
	$type = mysql_fetch_array($typeRow);
	
	if($type[0] == 'RIDER')
	{	
		if(strtolower($sms[1]) == 'm')	
		$gender = 'MALE';
		elseif(strtolower($sms[1]) == 'f')
		$gender = 'FEMALE';

		if(strtolower($sms[2]) == 's')
		$smoker = 'YES';
		elseif(strtolower($sms[2]) == 'ns')
		$smoker = 'NO';	

		mysql_query("UPDATE `Rider` SET `gender` = '".$gender."', `smoker` = '".$smoker."' WHERE `user_id` = (SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."')");

		$newInfoSMS = "Thank you. Please share your preference for a ride in future. Send 'CARPOOL preference <M/F> <S/NS>' to 09227507512.";
		$reply = urlencode($newInfoSMS);

		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	
	elseif($type[0] == 'AGENT')
	{
		mysql_query("UPDATE `Agent` SET `company` = '".$sms[1]."' WHERE `user_id` = (SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."')");

		$newInfoSMS = "You have successfully registered as an agent with shareSMSride.";
		$reply = urlencode($newInfoSMS);

		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}	

}

if(strtolower($sms[0]) == 'preference')
{
	$userIDRow = "(SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."')";
	$userID = mysql_fetch_array($userIDRow);
	
	$registeredRiderRow = mysql_query("SELECT `id` FROM `Rider` WHERE `user_id` = '".$userID[0]."'");
	$riderID = mysql_fetch_array($registeredRiderRow);
	
	if(!empty($registeredRider))
	{
		if(strtolower($sms[1]) == 'm')	
		$gender = 'MALE';
		elseif(strtolower($sms[1]) == 'f')
		$gender = 'FEMALE';

		if(strtolower($sms[2]) == 's')
		$smoker = 'YES';
		elseif(strtolower($sms[2]) == 'ns')
		$smoker = 'NO';
		
		$existingPreferenceRow = mysql_query("SELECT `id` FROM `Preference` WHERE `rider_id` = '".$riderID[0]."'");
		$existingPreferenceID = mysql_fetch_array($existingPreferenceRow);
		
		if(empty($existingPreferenceID))
		{
			mysql_query("INSERT INTO `Preference` (`gender`,`smoker`,`rider_id`) VALUES ('".$gender."','".$smoker."','".$riderID[0]."')");
		
			$newPreferenceSMS = "Thank you. Your registration was successful. To share a ride in future, send 'CARPOOL trip <start city> <end city> <start time>' to 09227507512.";
			$reply = urlencode($newPreferenceSMS);

			$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
			$response = new SimpleXMLElement($response);	
		}
		else
		{
			mysql_query("UPDATE `Preference` SET `gender` = '".$gender."', `smoker` = '".$smoker."' WHERE `rider_id` = '".$riderID[0]."'");
			$newPreferenceSMS = "Your preference has been updated. To share a ride in future, send 'CARPOOL trip <start point> <end point> <start time>' to 09227507512.";
			$reply = urlencode($newPreferenceSMS);

			$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
			$response = new SimpleXMLElement($response);
		}
	}
	else
	{
		$newPreferenceSMS = "You are not registered with shareSMSride. Please send CARPOOL register <first name> <last name> to 09227507512 first.";
		$reply = urlencode($newPreferenceSMS);

		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}	
}

if(strtolower($sms[0]) == 'trip')
{
	$userIDRow = "(SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."')";
	$userID = mysql_fetch_array($userIDRow);
	
	$registeredRiderRow = mysql_query("SELECT `id` FROM `Rider` WHERE `user_id` = '".$userID[0]."'");
	$riderID = mysql_fetch_array($registeredRiderRow);
	
	if(!empty($registeredRider))
	{
		$preferenceIDRow = mysql_query("SELECT `id` FROM `Preference` WHERE `rider_id` = '".$riderID."'");
		$preferenceID = mysql_fetch_array($preferenceIDRow);
		
		mysql_query("INSERT INTO `Trip Request` (`start_location`,`end_location`,`start_time`,`rider_id`,`preference_id`) VALUES ('".$sms[1]."','".$sms[2]."','".$sms[3]."','".$riderID[0]."','".$preferenceID[0]."')");
		
		$newTripRequestSMS = "Your trip request has been received. You'll be notified when we find someone who suits your preferences.";
		$reply = urlencode($newTripRequestSMS);
		
		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response); 
		
	}	
	else
	{
		$newTripRequestSMS = "Sorry, you are not registered to use shareSMSride service. To do so, first send 'CARPOOL register <first name> <last name>' to 09227507512.";
		$reply = urlencode($newTripRequestSMS);
		
		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	
}

if(strtolower($sms[0]) == 'notify')
{
	$userIDRow = mysql_query("SELECT `id` FROM `User` WHERE (`mobile_number` = '".$number."' AND `type` = 'AGENT')");
	$userID = mysql_fetch_array($userIDRow);
	
	$agentIDRow = mysql_query("SELECT `id` FROM `Agent` WHERE `user_id` = '".$userID[0]."'");
	$agentID = mysql_fetch_array($agentIDRow);
	
	$tripIDRow = mysql_query("SELECT * FROM `Trip` WHERE (`id` = '".$sms[1]."' AND `agent_id` = '".$agentID[0]."'");
	$trip = mysql_fetch_array($tripIDRow);
	
	if(empty($userID) || empty($agentID))
	{
		$newNotifySMS = "Sorry, you are not a registered agent at shareSMSride. Contact support@shareSMSride.in to complete registration.";
		$reply = urlencode($newNotifySMS);
		
		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	
	if(empty($trip))
	{
		$newNotifySMS = "Sorry, the specified trip has not been approved yet.";
		$reply = urlencode($newNotifySMS);
		
		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	else
	{
		$startLocation = $trip[3];
		$endLocation = $trip[4];
		$startTime = $trip[2];
		$comments = $trip[6];
		
		$tripRiderRow = mysql_query("SELECT `rider_id` FROM `Trip User` WHERE `trip_id` = '".$tripID[0]."'");
		$tripRiderID = mysql_fetch_array($tripRiderRow);
		
		$noOfRiders = count($tripRiderID);
		
		for($i=0;$i<=$noOfRiders;$i++)
		{
			$userIDRow = mysql_query("SELECT `user_id` FROM `Rider` WHERE `id` = '".$tripRiderID[$i]."'");
			$userID = mysql_fetch_array($userIDRow);
		
			$userNumberRow = mysql_query("SELECT `mobile_number` FROM `User` WHERE `id` = '".$userID[0]."'");
			$userNumber = mysql_fetch_array($userNumberRow);
			$mobileNumber = $userNumber[0];
			
			$newNotifySMS = "Your trip request from $startLocation to $endLocation at $startTime has been approved. The trip ID is $trip[0] and further details go as follows: $comments";
			$reply = urlencode($newNotifySMS);
		
			$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$mobileNumber."&api_key=".$api_key."&message=".$reply);
			$response = new SimpleXMLElement($response);
		}
	}
}

if(strtolower($sms[0]) == 'chat')
{
	$userIDRow = "(SELECT `id` FROM `User` WHERE `mobile_number` = '".$number."')";
	$userID = mysql_fetch_array($userIDRow);
	
	$riderDetailsRow = mysql_query("SELECT * FROM `User` WHERE `id` = '".$userID[0]."'");
	$riderDetails = mysql_fetch_array($riderDetailsRow);
	
	$registeredRiderRow = mysql_query("SELECT `id` FROM `Rider` WHERE `user_id` = '".$userID[0]."'");
	$riderID = mysql_fetch_array($registeredRiderRow);
	
	$tripExistsRow = mysql_query("SELECT `id` FROM `Trip User` WHERE (`trip_id` = '".$sms[1]."' AND `rider_id` = '".$riderID[0]."')");
	$tripExists = mysql_fetch_array($tripExistsRow);
	
	if(!empty($tripExists))
	{
		$tripRiderRow = mysql_query("SELECT `rider_id` FROM `Trip User` WHERE (`trip_id` = '".$tripExists[0]."' AND `rider_id` NOT IN ('".$riderID[0]."')");
		$tripRiderID = mysql_fetch_array($tripRiderRow);
		
		$noOfRiders = count($tripRiderID);
		
		for($i=0;$i<=$noOfRiders;$i++)
		{
			$userIDRow = mysql_query("SELECT `user_id` FROM `Rider` WHERE `id` = '".$tripRiderID[$i]."'");
			$userID = mysql_fetch_array($userIDRow);
		
			$userNumberRow = mysql_query("SELECT `mobile_number` FROM `User` WHERE `id` = '".$userID[0]."'");
			$userNumber = mysql_fetch_array($userNumberRow);
			$mobileNumber = $userNumber[0];
			
			$newChatSMS = "New chat SMS from your fellow rider $riderDetalils[1] $riderDetalils[2]: $sms[2]";
			$reply = urlencode($newChatSMS);
		
			$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$mobileNumber."&api_key=".$api_key."&message=".$reply);
			$response = new SimpleXMLElement($response);
		}
	}
	else
	{
		$newChatSMS = "Trip ID $sms[1] has not been approved till now. Please contact support@shareSMSride.in for further assistance.";
		$reply = urlencode($newChatSMS);
		
		$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
		$response = new SimpleXMLElement($response);
	}
	
	
}

if(strtolower($sms[0]) == 'help')
{
	$helpSMS = "shareSMSride HELP:\n1.CARPOOL register <first name> <last name>\n2.CARPOOL info <M/F> <S/NS>\n3.CARPOOL preference <M/F> <S/NS>\n4.CARPOOL trip <start city> <end city> <start time>\n5.CARPOOL chat <trip ID> <message>";
	$reply = urlencode($helpSMS);
		
	$response = file_get_contents("http://www.kookoo.in/outbound/outbound_sms.php?phone_no=".$number."&api_key=".$api_key."&message=".$reply);
	$response = new SimpleXMLElement($response);
}
mysql_close();
?>
