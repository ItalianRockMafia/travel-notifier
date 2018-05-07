<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";

require 'functions/render.php';

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
				<li class="nav-item">
				<a class="nav-link" href="../emp">EMP</a>
			  </li>
				<li class="nav-item">
				<a class="nav-link" href="../vinyl">Vinyl</a>
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
$gmapkey = $config->google['map_api_key'];
$gmap_apiroot = "https://maps.googleapis.com/maps/api/directions/json?";
$event = json_decode(getCall($config->api_url . "events/" . $eventID), true);
$startdate = strtotime($event['startdate']);
$enddate = strtotime($event['enddate']);



$result = json_decode(getCall($gmap_apiroot . "origin=" . urlencode($_SESSION['station']) . "&destination=" . urlencode($event['station']) . "&key=" . $gmapkey . "&mode=driving&arrival_time=" . $startdate . "&units=metric&region=ch"), true);

$route = $result['routes'][0];

$leg = $route['legs'][0];
$leaveTime = $startdate - $leg['duration']['value'] - 10;
echo '<h1>Route to: ' . $leg['end_address'] . '</h1>';
echo '<p class="desc">Start from: ' . $leg['start_address'] . '</p>';
echo '<p class="desc">Distance: ' . $leg['distance']['text'] . '</p>';
echo '<p class="desc">Duration: ' . gmdate("H:i",$leg['duration']['value']) . ' h</p>';
echo '<p class="desc">Leave at: ' . date("l, d.m.Y H:i", $leaveTime) . '</p>';
/*
 echo '<iframe
width="600"
height="450"
frameborder="0" style="border:0"
src="https://www.google.com/maps/embed/v1/directions?key=' . $gmapkey  . '&origin=' . urlencode($_SESSION['station']) . '&destination=' . urlencode($event['station']) .  'allowfullscreen>
</iframe>';*/


?>
<div id="accordion">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#routeDetail" aria-expanded="true" aria-controls="collapseOne">
        Complete route
        </button>
      </h5>
    </div>
		<div id="routeDetail" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">
<?php

foreach($leg['steps'] as $step){
	echo $step['html_instructions'] .' ' . gmdate("i",$step['duration']['value']) . ' min (' . $step['distance']['text'] . ')<br>';

}?>
      </div>
    </div>
	</div>
	
<?php

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

	