<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";

require 'functions/render.php';

$gmapkey = $config->google['map_api_key'];
$gmap_apiroot = "https://maps.googleapis.com/maps/api/directions/json?";
$startimestamp = strtotime($eventArray['startdate']);


$tg_user = getTelegramUserData();
saveSessionArray($tg_user);
	if(isset($_GET['send'])){
		
			if($_GET['send'] == "group"){
				
				$eventArray = json_decode(getCall($config->api_url . "events/" . $eventID . "&transform=1"), true);
				$eventStation = $eventArray["station"];
				$startdate = date("Y-m-d", strtotime($eventArray['startdate']));
				$starttime = date("H:i", strtotime($eventArray['startdate']));
				$startimestamp = strtotime($eventArray['startdate']);
				$text ="<strong>Connections / Routes to " . $eventArray['event_title'] .'</strong>';
				$userArray = json_decode(getCall($config->api_url . "userStation?transform=1&filter=userID,neq,null"),true);
				foreach($userArray['userStation'] as $user){
						$station = $user["station"];
						if($user['public_transport']){
							$url = "http://transport.opendata.ch/v1/connections?from=" . urlencode($station) . "&to=" . urlencode($eventStation) . "&date=" .$startdate . "&time=". $starttime . "&isArrivalTime=1";
							$response = json_decode(file_get_contents($url));
							if ($response->to) {
								$to = $response->to->name;
								$x = $response->to->coordinate->x;
								$y = $response->to->coordinate->y;
								$toLink = '<a href="https://www.google.ch/maps/@' . $x .',' . $y . ',18z">';
							}
							if (isset($response->stations->from[0])) {
								if ($response->stations->from[0]->score < 101) {
									foreach (array_slice($response->stations->from, 1, 3) as $station) {
										if ($station->score > 97) {
											$stationsFrom[] = $station->name;
										}
									}
								}
							}
							$text .= renderTgConnection($response, false, true, $user);
						}else{
							$url = $gmap_apiroot . "origin=" . urlencode($station) . "&destination=" . urlencode($eventStation) . "&key=" . $gmapkey . "&mode=driving&arrival_time=" . $startimestamp . "&units=metric&region=ch";
							$response = json_decode(file_get_contents($url));
							$text .= renderTgRoute($response, $startimestamp, false, true, $user);
						}
						$text .= chr(10) .chr(10);
				}


				$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=". $config->telegram['chatID'] . "&parse_mode=HTML&disable_web_page_preview=1&text=" . urlencode($text);
		
		//	getCall($alertURL);
			
				
			} else {
				$userArray = json_decode(getCall($config->api_url . "userStation?transform=1&filter=userID,eq," . $_SESSION['irmID']),true);
				foreach($userArray['userStation'] as $user){
				$station = $user["station"];
				}
				
				$eventArray = json_decode(getCall($config->api_url . "events/" . $eventID . "&transform=1"), true);
				
				$eventStation = $eventArray["station"];
				$startdate = date("Y-m-d", strtotime($eventArray['startdate']));
				$starttime = date("H:i", strtotime($eventArray['startdate']));
				$startimestamp = strtotime($eventArray['startdate']);
				if($user['public_transport']){
					$url = "http://transport.opendata.ch/v1/connections?from=" . urlencode($station) . "&to=" . urlencode($eventStation) . "&date=" .$startdate . "&time=". $starttime . "&isArrivalTime=1";
					$response = json_decode(file_get_contents($url));
					$tgUrl = renderTgConnection($response, true, false, null);
				} else{
					$url = $gmap_apiroot . "origin=" . urlencode($station) . "&destination=" . urlencode($eventStation) . "&key=" . $gmapkey . "&mode=driving&arrival_time=" . $startimestamp . "&units=metric&region=ch";
					$response = json_decode(file_get_contents($url));
					$tgUrl = renderTgRoute($response, $startimestamp, true, false, $user);
				}
				 getCall($tgUrl);
				
			}
		}
		header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&sent=1');
	