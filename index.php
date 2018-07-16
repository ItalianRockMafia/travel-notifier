<?php
session_start();
$date = new DateTime();
require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";

require '../global/functions/header.php';
require '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | events";
$header = getHeader($options);
$footer = renderFooter();

echo $header;
?>

<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php



$tg_user = getTelegramUserData();

if ($tg_user !== false) {
	$_SESSION['tgID'] = $tg_user['id'];
	$irm_users = json_decode(getCall($config->api_url . "users?transform=1&filter=telegramID,eq," . $tg_user['id']), true);
	foreach($irm_users['users'] as $user){
		$irm_user['id'] = $user['userID'];
	}
	
	$_SESSION['irmID'] = $irm_user['id'];

$events = json_decode(getCall($config->api_url . "eventUsers?transform=1"), true);
?>
<h1>Events <a href="new.php"><i class="fa fa-plus-circle righticon" aria-hidden="true"></i></a></h1>
<div class="list-group">
<?php
foreach($events['eventUsers'] as $event){
	$startdate = new DateTime($event['startdate']);
	$enddate = new DateTime($event['enddate']);
	if($startdate > $date && $enddate > $date){
	echo '<a href="event.php?event=' . $event['eventID'] . '" class="list-group-item list-group-item-action">' . $startdate->format("d.n.Y"). " - " . $event["event_title"] . '</a>';
	}
}
?></div><?php
} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
echo $footer;
?>
		