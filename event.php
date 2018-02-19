<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";

$event2del = $_GET['delete'];
if(isset($_GET['delete'])){
	deleteCall($config->api_url . "events/" . $event2del);
	header('Location: https://italianrockmafia.ch/meetup/index.html');
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
				<a class="nav-link" href="index.php">Meetup<span class="sr-only">(current)</span></a>
			  </li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
				<li class="nav-item">
        			<a class="nav-link" href="login.php?logout=1">Logout</a>
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
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);
	$creator = json_decode(getCall($config->api_url . "users/" . $event['userIDFK'] . "?transform=1"),true);

	echo '<h1>Event: ' . $event['event_title'] . '</h1>';
	echo '<p class="desc">' . $event['description'] . '</p>';
	echo '<div class="topspacer"></div>';
	echo '<p class="desc">Start: ' . $event['startdate'] . ' - ' . $event['enddate'] . '</p>';
	echo '<p class="desc">Location / Station: ' . $event['station'] . '</p>';
	
	echo '<p>More: <a href="' . $event['url'] . '" target="_blank">' . $event['url'] . '</a></p>';
	echo '<p>Creator: <a href="https://t.me/' . $creator['tgusername'] . '" target="_blank">' . $creator['firstname'] . ' ' . $creator['lastname'] . ' (' . $creator['tgusername'] .')</a></p>';
?>
<a href="index.php"><button type="button" class="btn btn-success">Back</button></a>
<button type="button" class="btn btn-success">Sign up</button>
<button type="button" class="btn btn-success"><i class="fa fa-telegram"></i> Send connection</button>
<?php 
if($creator['tgusername']  == $tg_user['username']){
	echo '<a href="?delete=' . $event['eventID'] . '"><button type="button" class="btn btn-danger">Delete Event</button></a>';
}

} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to login first
  </div>
';
}
?>
			</div>
		</main>
	</body>
</html>
