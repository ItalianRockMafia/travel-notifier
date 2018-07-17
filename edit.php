<?php
session_start();
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
require_once '../global/functions/irm.php';

$config = require_once "../config.php";
$now = new datetime();
$eventID = $_GET['event'];

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | Edit event";
$header = getHeader($options);
$footer = renderFooter();

// FORM HANDLER
if(isset($_GET['edit'])){
	
	$title = htmlspecialchars($_POST['title'], ENT_QUOTES);
	$startdate= strtotime($_POST['startdate']);
	$startdate = date("Y-m-d H:i:s",$startdate);
	$endate = strtotime($_POST['enddate']);
	$endate = date("Y-m-d H:i:s",$endate);
	$eventlink = htmlspecialchars($_POST['url'], ENT_QUOTES);
	$station = htmlspecialchars($_POST['station'], ENT_QUOTES);
	$desc = htmlspecialchars($_POST['description'], ENT_QUOTES);
	$guestok = $_POST['guestcheck'];
	$irmID = $_SESSION['irmID'];

	$postfields = "{\n\t\"event_title\": \"$title\", \n\t\"startdate\": \"$startdate\",\n\t\"enddate\": \"$endate\",\n\t\"url\": \"$eventlink\",\n\t\"station\": \"$station\",\n\t\"description\": \"$desc\",\n\t\"userIDFK\": \"$irmID\",\n\t\"guestOK\":\"$guestok\"\n\t\n}";
	$affRows = putCall($config->api_url . "events/" . $eventID, $postfields);
	$startdate = strtotime($startdate);
	$endate = strtotime($endate);
	if(is_numeric($affRows)){
		$alertText = urlencode('<strong>Event updated: </strong>' . $title   .chr(10). 'Start: ' . date("l, d.m.Y H:i", $startdate) . chr(10) . 'End: ' . date("d.m.Y H:i", $endate) . chr(10) . 'Where: ' . $station . chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '">View on web</a>');
	
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $alertText;
		
		getCall($alertURL);
		header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
		
} else {
	$alertText = "Error saving event.";
	$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $_SESSION['tgID'] . "&parse_mode=HTML&text=" . $alertText;

	getCall($alertURL);
	header('Location: https://italianrockmafia.ch/meetup/');
}


}


echo $header;
?>


<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php

$tg_user = getTelegramUserData();
saveSessionArray($tg_user);

if ($tg_user !== false) {
	if($_SESSION['access'] > 2){

	
	$event = json_decode(getCall($config->api_url . "events/" . $eventID), true);
	$startdate = strtotime($event['startdate']);
	$enddate = strtotime($event['enddate']);

?>
<h1>Edit Event "<?php echo $event['event_title'];?>"</h1>
<?php echo '<form action="?edit=1&event=' .  $eventID . '" method="POST">';?>
  <div class="form-group">
  <label for="title">Event title</label>
    <input type="text" class="form-control" name="title" id="title" value="<?php echo $event['event_title'];?>" require_onced>
  </div>
  <div class="form-group">
  <label for="startdate">Event start</label>
    <input type="datetime-local" class="form-control" name="startdate" id="startdate" value="<?php echo date('Y-m-d\TH:i',$startdate);?>" placeholder="<?php echo date('Y-m-d H:i:s',$startdate);?>" require_onced>
	</div>
  <div class="form-group">
  <label for="enddate">Event end</label>
    <input type="datetime-local" class="form-control" name="enddate" id="enddate" value="<?php echo date('Y-m-d\TH:i',$enddate);?>" placeholder="2018-27-42 00:00:00" require_onced>
  </div>
  <div class="form-group">
  <label for="url">Event Link</label>
    <input type="url" class="form-control" name="url" id="url" value="<?php echo $event['url'];?>">
  </div>
  <div class="form-group">
  <label for="station">Event Location / Station</label>
    <input type="text" class="form-control" name="station" id="station" value="<?php echo $event['station'];?>" require_onced>
	<small id="stationHelp" class="form-text text-muted">Please provide the name, as it is in the SBB mobile app.</small>
  </div>
  <div class="form-group">
  <label for="description">Event Description</label>
  <textarea class="form-control" name="description" id="description" rows="3" ><?php echo $event['description'];?></textarea>
  </div>
	<div class="form-group form-check">
		<input type="checkbox" name="guestcheck" value="1" class="form-check-input" id="guestcheck" <?php if($event['guestOK'] == '1'){echo "checked";} ?>>
		<label for="guestcheck">Guests are allowed</label>
	</div>

  <button type="submit" class="btn btn-success">Submit</button>

</form>
	</div><?php
	}
	else {
		echo '
		<div class="alert alert-warning" role="alert">
		<strong>Warning.</strong> Access denied.
			</div>
	';
	}
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
	  </div>
';
}

echo $footer;
?>
