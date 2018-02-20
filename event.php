<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";

$event2del = $_GET['delete'];
if(isset($_GET['delete'])){
	deleteCall($config->api_url . "events/" . $event2del);
	header('Location: https://italianrockmafia.ch/meetup/index.php');
}

if(isset($_GET['signup'])){

	$user = $_SESSION['irmID'];
	$postfields = "{\n \t \"userIDFK\": \"$user\", \n \t \"eventIDFK\": \"$eventID\" \n }";
	$result = postCall($config->api_url . "attendes", $postfields);
	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
}

if(isset($_GET['cancel'])){
		$list = json_decode(getCall($config->api_url . 'attendes?transform=1&filter[]=userIDFK,eq,' . $_SESSION['irmID'] . '&filter[]=eventIDFK,eq,' . $eventID . "satisfy=all"), true);
		foreach($list['attendes'] as $user){
			$attende2del = $user['attendeID'];
		}
	
		$result = deleteCall($config->api_url . "attendes/" . $attende2del);
		header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
	}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
 	   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<link rel="stylesheet" href="../global/main.css">
			<link rel="stylesheet" href="travel.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script src="https://use.fontawesome.com/c414fc2c21.js"></script>
		<title>IRM - Meetup planer</title>
	</head>
	<body>


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
				<a class="nav-link" href="index.php">Events<span class="sr-only">(current)</span></a>
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
saveSessionArray($tg_user);
if ($tg_user !== false) {

	if (isset($_GET['sent'])){
		echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
		Connection sent.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>';
	}
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);
	$creator = json_decode(getCall($config->api_url . "users/" . $event['userIDFK'] . "?transform=1"),true);

	$startdate =  $event['startdate'];
	$enddate = $event['enddate'];
$startdate = strtotime($startdate);
$enddate = strtotime($enddate);

	echo '<h1>Event: ' . $event['event_title'] . '</h1>';
	echo '<p class="desc">' . $event['description'] . '</p>';
	echo '<div class="topspacer"></div>';
	echo '<p class="desc">Start: ' . date("l, d.m.Y H:i", $startdate) . ' - ' . date("d.m.Y H:i", $enddate) . '</p>';
	echo '<p class="desc">Location / Station: ' . $event['station'] . '</p>';
	
	echo '<p>More: <a href="' . $event['url'] . '" target="_blank">' . $event['url'] . '</a></p>';
	echo '<p>Creator: <a href="https://t.me/' . $creator['tgusername'] . '" target="_blank">' . $creator['firstname'] . ' ' . $creator['lastname'] . ' (' . $creator['tgusername'] .')</a></p>';
?>
<a href="index.php"><button type="button" class="btn btn-success">Back</button></a>
<?php
$url = $config->api_url . 'attendes?transform=1&filter[]=userIDFK,eq,' . $_SESSION['irmID'] . '&filter[]=eventIDFK,eq,' . $eventID . "&satisfy=all";
$StatusChecker = json_decode(getCall($url), true);

foreach($StatusChecker['attendes'] as $attende){

	if($eventID == $attende['eventIDFK'] && $_SESSION['irmID'] == $attende['userIDFK']){
		echo '<a href="?event=' . $eventID . '&cancel=1"><button type="button" class="btn btn-success">Cancel</button></a>';
	}

}
if(empty($StatusChecker['attendes'])){
	echo '<a href="?event=' . $eventID . '&signup=1"><button type="button" class="btn btn-success">Sign up</button></a>';
} 

?>

<?php echo '<a href="tgsender.php?event=' . $eventID . '&send=1"><button type="button" class="btn btn-success"><i class="fa fa-telegram"></i> Send connection</button></a>';

if($creator['tgusername']  == $tg_user['username']){
	echo '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteEvent">Delete Event</button>';
}

?>
<div class="topspacer"></div>
<h2>Attendes:</h2>
<ol>
<?php
$attendes = json_decode(getCall($config->api_url . 'eventAttendes?transform=1&filter=eventIDFK,eq,' . $eventID), true);
foreach($attendes['eventAttendes'] as $attende){
	
echo '<li><a href="https://t.me/' . $attende["tgusername"] . '" target="_blank">' . $attende["firstname"] . ' ' . $attende["lastname"] . ' (' . $attende["tgusername"] . ')</a></li>';
}
echo '</ol>';

} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
?>

<!-- Modal -->
<div class="modal fade" id="comingSoon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Coming Soon</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        This feature is still in development.
      </div>
      <div class="modal-footer">
			<button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="deleteEvent" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Are you sure?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this event?
      </div>
      <div class="modal-footer">
			<button type="button" class="btn btn-success" data-dismiss="modal">No</button>
			<?php echo '<a href="?delete=' . $event['eventID'] . '"><button type="button" class="btn btn-danger">Yes</button></a>'; ?>
      </div>
    </div>
  </div>
</div>

</div>
</main>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>