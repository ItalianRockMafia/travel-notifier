<?php
session_start();
require_once '/home/husserjo/www/italianrockmafia.ch/global/functions/apicalls.php';
require_once '/home/husserjo/www/italianrockmafia.ch/global/functions/telegram.php';
$config = require_once "/home/husserjo/www/italianrockmafia.ch/config.php";
$now = new datetime();


$events = json_decode(getCall($config->api_url . "eventUsers?transform=1"), true);

foreach($events['eventUsers'] as $event){
	$startdate = new DateTime($event['startdate']);
	$enddate = new DateTime($event['enddate']);
	$datediff = date_diff($now, $startdate);
	$daysdiff = $datediff->format('%R%a');
	if($daysdiff == "+1" ){
		
	$alertText = urlencode('<strong>Tomorrow: </strong>' . $event['event_title']   .chr(10). 'Start: ' . $startdate->format("l, d.m.Y H:i") . chr(10) . 'Where: ' . $event['station'] . chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $event['eventID'] . '">View on web</a>' .chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID  . '&signup=1">I\'m coming, sign me up!</a>');
	
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $alertText;
		echo $alertURL;
		getCall($alertURL);
	} else {
	}
}