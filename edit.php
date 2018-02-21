<?php
session_start();
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";
$now = new datetime();
$eventID = $_GET['event'];
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
 	   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="../global/main.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://use.fontawesome.com/c414fc2c21.js"></script>
		<title>IRM - Meetup planer</title>
	</head>
	<body>
<?php
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
	$irmID = $_SESSION['irmID'];

	$postfields = "{\n\t\"event_title\": \"$title\", \n\t\"startdate\": \"$startdate\",\n\t\"enddate\": \"$endate\",\n\t\"url\": \"$eventlink\",\n\t\"station\": \"$station\",\n\t\"description\": \"$desc\",\n\t\"userIDFK\": \"$irmID\"\n\t\n}";
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



?>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
	<a class="navbar-brand" href="#">ItalianRockMafia</a>
	  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	  </button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
		<li class="nav-item">
        				<a class="nav-link" href="../main.php">Home</a>
      				</li>
			  <li class="nav-item">
				<a class="nav-link" href="../settings.php">Settings</a>
			  </li>
			  <li class="nav-item active">
				<a class="nav-link" href="https://italianrockmafia.ch/meetup">Events<span class="sr-only">(current)</span></a>
			  </li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
				<li class="nav-item">
        			<a class="nav-link" href="https://italianrockmafia.ch/login.php?logout=1">Logout</a>
      			</li>
		</ul>
	</div>
</nav>
<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php

$tg_user = getTelegramUserData();

if ($tg_user !== false) {
	$event = json_decode(getCall($config->api_url . "events/" . $eventID), true);
	$startdate = strtotime($event['startdate']);
	$enddate = strtotime($event['enddate']);

?>
<h1>Edit Event "<?php echo $event['event_title'];?>"</h1>
<?php echo '<form action="?edit=1&event=' .  $eventID . '" method="POST">';?>
  <div class="form-group">
  <label for="title">Event title</label>
    <input type="text" class="form-control" name="title" id="title" value="<?php echo $event['event_title'];?>" required>
  </div>
  <div class="form-group">
  <label for="startdate">Event start</label>
    <input type="datetime-local" class="form-control" name="startdate" id="startdate" value="<?php echo date('Y-m-d\TH:i',$startdate);?>" placeholder="<?php echo date('Y-m-d H:i:s',$startdate);?>" required>
	</div>
  <div class="form-group">
  <label for="enddate">Event end</label>
    <input type="datetime-local" class="form-control" name="enddate" id="enddate" value="<?php echo date('Y-m-d\TH:i',$enddate);?>" placeholder="2018-27-42 00:00:00" required>
  </div>
  <div class="form-group">
  <label for="url">Event Link</label>
    <input type="url" class="form-control" name="url" id="url" value="<?php echo $event['url'];?>">
  </div>
  <div class="form-group">
  <label for="station">Event Location / Station</label>
    <input type="text" class="form-control" name="station" id="station" value="<?php echo $event['station'];?>" required>
	<small id="stationHelp" class="form-text text-muted">Please provide the name, as it is in the SBB mobile app.</small>
  </div>
  <div class="form-group">
  <label for="description">Event Description</label>
  <textarea class="form-control" name="description" id="description" rows="3" ><?php echo $event['description'];?></textarea>
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
?>
</div>
</main>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>