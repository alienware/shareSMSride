<?php
require_once("./kookoolib-KooKoo-PHP-a8fe864/kookoophp/response.php");
session_start();

function connectToMySQL(){
  $db_user = "tictoc";
  $db_pass = "toctic";
  $db_db = "CarPooling";
  mysql_connect("localhost", $db_user, $db_pass) or die("ERROR: MySQL cannot connect.");
  mysql_select_db($db_db) or die("ERROR: MySQL cannot select database.");
}

connectToMySQL();

$KooKoo = new Response();
$_SESSION['cid'] = $_REQUEST['cid'];

if($_REQUEST['event'] == "NewCall") 
{
	$userDetailsRow = mysql_query("SELECT * FROM `User` WHERE `mobile_number` = '".$_SESSION['cid']."'");
	$userDetails = mysql_fetch_array($userDetailsRow);
	
	$registeredRiderRow = mysql_query("SELECT `id` FROM `Rider` WHERE `user_id` = '".$userDetails[0]."'");
	$riderID = mysql_fetch_array($registeredRiderRow);
	
	if(empty($userDetails) || empty($riderID))
	{
		$KooKoo->addPlayText("Welcome to share SMS ride. We are sorry but you are not a registered user. Have a good day.");
		$KooKoo->addHangup();
		$KooKoo->send();
	}
	else
	{	
		$_SESSION['userID'] = $riderID[0];
	
		$genderRow = mysql_query("SELECT `gender` FROM `Rider` WHERE `user_id` = '".$userDetails[0]."'");
		$gender = mysql_fetch_array($genderRow);
	
		if($gender[0] == 'MALE')
			$greetings = 'Mister';
		elseif($gender[0] == 'FEMALE')
			$greetings = 'Miss';
	
		$cd = new CollectDtmf();
		$cd->setTermChar("#");
		$cd->setTimeOut("2000");
		$cd->addPlayText("Hello $greetings $userDetails[1]. Welcome to share SMS ride. To provide your valuable review, please press 1.");
	
		$_SESSION['state'] = "category";
		$KooKoo->addCollectDtmf($cd);
		$KooKoo->send();
	}

} 
else if ($_REQUEST['event'] == "GotDTMF" && $_SESSION['state'] == "category")
{
	$category = $_REQUEST['data'];
	
	if($category == '1')
	{
		$cd = new CollectDtmf();
		$cd->setTermChar("#");
		$cd->setTimeOut("2000");
		$cd->addPlayText("Welcome to the review portal. Please provide us with yout trip id. Press hash to terminate.");
	
		$_SESSION['state'] = "tripID";
		$KooKoo->addCollectDtmf($cd);
		$KooKoo->send();
	}
} 
else if ($_REQUEST['event'] == "GotDTMF" && $_SESSION['state'] == "tripID") 
{
	$tripID = $_REQUEST['data'];
	$_SESSION['tripID'] = $tripID;
	
	$agentNumberRow = mysql_query("SELECT `mobile_number` FROM `User` WHERE `id` = (SELECT `agent_id` FROM `Trip` WHERE `id` = '".$tripID."')");
	$agentNumber = mysql_fetch_array($agentNumberRow);
	$_SESSION['agent'] = $agentNumber[0];
	
	$KooKoo->addPlayText("Please provide your review for trip ID $tripID. The trip service agent will respond to it shortly. Thank you for using share SMS ride services.");
	$location='review_'.time().'_'.$tripID.'_'.$_REQUEST['userID'];
        $KooKoo->addRecord($location);
        $KooKoo->send(); 
}
else if($_REQUEST['event'] == "Hangup" && $_SESSION['state'] == "tripID")
{
      $url=$_REQUEST['data'];
      $KooKoo->sendSms('shareSMSride received a review from '.$_SESSION['cid'].' for trip ID '.$_SESSION['tripID'].'. You can listen to it at '.$url,$_SESSION['agent']);
      $KooKoo->send();
      
}
mysql_close();
?>
