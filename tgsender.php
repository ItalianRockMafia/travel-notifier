<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";

require 'functions/render.php';



$tg_user = getTelegramUserData();
saveSessionArray($tg_user);
	if(isset($_GET['send'])){
		
			if($_GET['send'] == "group"){
				$eventArray = json_decode(getCall($config->api_url . "events/" . $eventID . "&transform=1"), true);
				$eventStation = $eventArray["station"];
				$startdate = date("Y-m-d", strtotime($eventArray['startdate']));
				$starttime = date("H:i", strtotime($eventArray['startdate']));
				$text ="";
				$userArray = json_decode(getCall($config->api_url . "userStation?transform=1"),true);
				foreach($userArray['userStation'] as $user){
						$station = $user["station"];
						$url = "http://transport.opendata.ch/v1/connections?from=" . urlencode($station) . "&to=" . urlencode($eventStation) . "&date=" .$startdate . "&time=". $starttime . "&isArrivalTime=1";
						$response = json_decode(file_get_contents($url));
						
						$text .= renderTgConnection($response, false, true, $user);
						$text .= chr(10) .chr(10);
				}
				$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=". $config->telegram['chatID'] . "&parse_mode=HTML&disable_web_page_preview=1&text=" . urlencode($text);
			
				  getCall($alertURL);
				
			} else {
				$userArray = json_decode(getCall($config->api_url . "userStation?transform=1&filter=userID,eq," . $_SESSION['irmID']),true);
				foreach($userArray['userStation'] as $user){
				$station = $user["station"];
				}
				
				$eventArray = json_decode(getCall($config->api_url . "events/" . $eventID . "&transform=1"), true);
				
				$eventStation = $eventArray["station"];
				$startdate = date("Y-m-d", strtotime($eventArray['startdate']));
				$starttime = date("H:i", strtotime($eventArray['startdate']));
				$url = "http://transport.opendata.ch/v1/connections?from=" . urlencode($station) . "&to=" . urlencode($eventStation) . "&date=" .$startdate . "&time=". $starttime . "&isArrivalTime=1";
				$response = json_decode(file_get_contents($url));
		
				$tgUrl = renderTgConnection($response, true, false, null);
				 getCall($tgUrl);
				
			}
		}
		header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&sent=1');
	