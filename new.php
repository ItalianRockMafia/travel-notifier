<?php
session_start();
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
$config = require_once "../config.php";
$now = new datetime();

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | new event";
$header = getHeader($options);
$footer = renderFooter();

echo $header;
?>

<?php
// FORM HANDLER
if(isset($_GET['add'])){
	$title = htmlspecialchars($_POST['title'], ENT_QUOTES);
	$startdate= strtotime($_POST['startdate']);
	$startdate = date("Y-m-d H:i:s",$startdate);
	$endate = strtotime($_POST['enddate']);
	$endate = date("Y-m-d H:i:s",$endate);
	$eventlink = htmlspecialchars($_POST['url'], ENT_QUOTES);
	$station = htmlspecialchars($_POST['station'], ENT_QUOTES);
	$desc = htmlspecialchars($_POST['description'], ENT_QUOTES);
	$irmID = $_SESSION['irmID'];

	$postfields = "{\n\t\"event_title\": \"$title\", \n\t\"startdate\": \"$startdate\",\n\t\"enddate\": \"$endate\",\n\t\"url\": \"$eventlink\",\n\t\"station\": \"$station\",\n\t\"description\": \"$desc\",\n\t\"userIDFK\": \"$irmID\"\n\t\n}";
	$eventID = postCall($config->api_url . "events", $postfields);

	$startdate = strtotime($startdate);
	$endate = strtotime($endate);

	if(is_numeric($eventID)){
		$alertText = urlencode('<strong>New Event: </strong>' . $title   .chr(10). 'Start: ' . date("l, d.m.Y H:i", $startdate) . chr(10) . 'End: ' . date("d.m.Y H:i", $endate) . chr(10) . 'Where: ' . $station . chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '">View on web</a>' .chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&signup=1">I\'m coming, sign me up!</a>');
	
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $alertText;
		getCall($alertURL);
		header('Location: https://italianrockmafia.ch/meetup/tgsender.php?event=' . $eventID . '&send=group');
		
} else {
	$alertText = "Error saving event.";
	$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $_SESSION['tgID'] . "&parse_mode=HTML&text=" . $alertText;

	getCall($alertURL);
	header('Location: https://italianrockmafia.ch/meetup/');
}


}



?>

<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php


$tg_user = getTelegramUserData();

if ($tg_user !== false) {
?>
<h1>New Event</h1>
<form action="?add=1" method="POST">
  <div class="form-group">
  <label for="title">Event title</label>
    <input type="text" class="form-control" name="title" id="title" placeholder="SAUFEN SAUFEN SAUFEN" require_onced>
  </div>
  <div class="form-group">
  <label for="startdate">Event start</label>
    <input type="datetime-local" class="form-control" name="startdate" id="startdate" value="<?php echo $now->format('Y-m-d\TH:i');?>" placeholder="<?php echo $now->format('Y-m-d H:i:s');?>" require_onced>
	</div>
  <div class="form-group">
  <label for="enddate">Event end</label>
    <input type="datetime-local" class="form-control" name="enddate" id="enddate" value="<?php echo $now->format('Y-m-d\TH:i');?>" placeholder="2018-27-42 00:00:00" require_onced>
  </div>
  <div class="form-group">
  <label for="url">Event Link</label>
    <input type="url" class="form-control" name="url" id="url" placeholder="https://italianrockmafia.ch">
  </div>
  <div class="form-group">
  <label for="station">Event Location / Station</label>
    <input type="text" class="form-control" name="station" id="station" placeholder="Baden" require_onced>
	<small id="stationHelp" class="form-text text-muted">Please provide the name, as it is in the SBB mobile app.</small>
  </div>
  <div class="form-group">
  <label for="description">Event Description</label>
  <textarea class="form-control" name="description" id="description" rows="3"></textarea>
  </div>

  <button type="submit" class="btn btn-success">Submit</button>

</form>
	</div><?php
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
	  </div>
';
}

echo $footer;
?>
