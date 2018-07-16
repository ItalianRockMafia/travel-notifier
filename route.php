<?php
session_start();
$eventID = $_GET['event'];
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
require '../global/functions/irm.php';
$config = require "../config.php";

require 'functions/render.php';

require '../global/functions/header.php';
require '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | route";
$header = getHeader($options);
$footer = renderFooter();

echo $header;

?>

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
/*
echo '<iframe
width="600"
height="450"
frameborder="0" style="border:0"
src="https://www.google.com/maps/embed/v1/directions?key=' . $gmapkey  . '&origin=' . urlencode($_SESSION['station']) . '&destination=' . urlencode($event['station']) .  'allowfullscreen>
</iframe>';*/

} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
echo $footer;
?>

