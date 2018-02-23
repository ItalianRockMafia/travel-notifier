<?php

function renderTgConnection($response, $full, $group, $user){
	global $eventID, $config;
	if ($response->from) {
		$from = $response->from->name;
		$x = $response->from->coordinate->x;
		$y = $response->from->coordinate->y;
		$fromLink = '<a href="https://www.google.ch/maps/@' . $x .',' . $y . ',18z">';
	}
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
	if (isset($response->stations->to[0])) {
		if ($response->stations->to[0]->score < 101) {
			foreach (array_slice($response->stations->to, 1, 3) as $station) {
				if ($station->score > 97) {
					$stationsTo[] = $station->name;
				}
			}
		}
	}
	if($group){
		$sendText = chr(10) . chr(10) . '<a href="tg://user?id='. $user['telegramID'] . '">'. $user['tgusername'] . '</a>, your connection to: ' . $toLink . $to . '</a>' .chr(10) . 'from: ';
		
	} else {
		$sendText = 'Your connection from: ';
	}
	$sendText .= $fromLink . $from . '</a>' . chr(10).  'departure: ' . date('d.m.Y H:i', strtotime($response->connections[0]->from->departure)) . chr(10) . 
	'arrival: ' . date('d.m.Y H:i', strtotime($response->connections[0]->to->arrival)) . chr(10). 'duration: ';
	
	$duration =  (substr($response->connections[0]->duration, 0, 2) > 0) ? htmlentities(trim(substr($response->connections[0]->duration, 0, 2), '0')).'d ' : '';
	
	$duration .= htmlentities(trim(substr($response->connections[0]->duration, 3, 1), '0').substr($response->connections[0]->duration, 4, 4)) .'′' . chr(10);
	$sendText .= $duration . chr(10);
	if($full){ 
	$sendText .='journey:' . chr(10);
	
	foreach ($response->connections[0]->sections as $section){
		$fromx = $section->departure->station->coordinate->x;
		$fromy = $section->departure->station->coordinate->y;
		$fromLink = '<a href="https://www.google.ch/maps/@' . $fromx .',' . $fromy . ',18z">';
		$tox = $section->arrival->station->coordinate->x;
		$toy = $section->arrival->station->coordinate->y;
		$toLink = '<a href="https://www.google.ch/maps/@' . $tox .',' . $toy . ',18z">';
		$sendText .=  date('H:i', strtotime($section->departure->departure)) . ' from ' . $fromLink .  htmlentities($section->departure->station->name, ENT_QUOTES, 'UTF-8') . '</a>' . chr(10);
		$sendText .=  htmlentities($section->journey->name, ENT_QUOTES, 'UTF-8') . ' number: ' . htmlentities($section->journey->number, ENT_QUOTES, 'UTF-8') . chr(10);
		$sendText .=  date('H:i', strtotime($section->arrival->arrival)) . ' to ' . $toLink . htmlentities($section->arrival->station->name, ENT_QUOTES, 'UTF-8') . '</a>' ; 
		
	}
}
if($group){
	$sendText .=  '<a href="https://italianrockmafia.ch/meetup/connections.php?event=' . $eventID . '">View connections on web</a>';
	$result = $sendText;
}else {
	$sendText .=  chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/connections.php?event=' . $eventID . '">View connections on web</a>';
	$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=". $_SESSION['tgID']. "&parse_mode=HTML&disable_web_page_preview=1&text=" . urlencode($sendText);
	$result = $alertURL;
}	
	
	return $result;
}


function renderTgRoute($response, $starttime, $full, $group, $user){
	global $eventID, $config;
	$route = $response->routes[0];
	$leg = $route->legs[0];
	
	$leaveTime = $starttime - $leg->duration->value - 10;
	$sendText = '<a href="tg://user?id='. $user['telegramID'] . '">'. $user['tgusername'] . '</a>, your route to <a href="https://www.google.ch/maps/@' . $leg->end_address->lat . ',' . $leg->end_address->lng . '18z' . $leg->end_address . '</a>'. chr(10);
	$sendText .= 'Start from: <a href="https://www.google.ch/maps/@' . $leg->start_address->lat . ',' . $leg->start_address->lng . '18z' . $leg->start_address . '</a>' . chr(10);
	$sendText .=  'Distance: ' . $leg->distance->text . chr(10);
	$sendText .=  'Duration: ' . gmdate("H:i",$leg->duration->value) . ' h' . chr(10);
	$sendText .=  'Leave at: ' . date("l, d.m.Y H:i", $leaveTime)  . chr(10);
	if($full){ 
		$sendText .= '<strong>Navigation:</strong>' . chr(10);
		foreach($leg->steps as $step){
			$sendText .= strip_tags($step->html_instructions, '<b>') .' ' . gmdate("i",$step->duration->value) . ' min (' . $step->distance->text . ')' .chr(10);
		
		}
	}
	$sendText .=  '<a href="https://italianrockmafia.ch/meetup/route.php?event=' . $eventID . '">View route on web</a>';
	if($group){
		$sendText .= chr(10) . chr(10);
		$result = $sendText;

	} else {
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=". $_SESSION['tgID']. "&parse_mode=HTML&disable_web_page_preview=1&text=" . urlencode($sendText);
		$result = $alertURL;
	}
	return $result;
}