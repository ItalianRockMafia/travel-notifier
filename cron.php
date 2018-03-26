<?php

$date = new DateTime();
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";

$events = json_decode(getCall($config->api_url . "eventUsers?transform=1"), true);

foreach($events['eventUsers'] as $event){
	$startdate = new DateTime($event['startdate']);
	$enddate = new DateTime($event['enddate']);
	
	if($startdate == $date ){
	echo $event['event_title'] . ' is today.<br>';
	$alertText = urlencode('<strong>Today: /strong>' . $event['event_title']   .chr(10). 'Start: ' . date("l, d.m.Y H:i", $event['startdate'])  . chr(10) . 'Where: ' . $event['station'] . chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $event['eventID'] . '">View on web</a>' .chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&signup=1">I\'m coming, sign me up!</a>');
	
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $alertText;
		getCall($alertText);
	}
}