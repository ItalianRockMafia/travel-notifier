<?php
session_start();
$date = new DateTime();
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
$config = require_once "../config.php";

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

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
		$irm_user['access'] = $user['accessIDFK'];
	}

	$_SESSION['access'] = $irm_user['access'];
	$_SESSION['irmID'] = $irm_user['id'];

	if($irm_user['access'] == "1"){
		$access = "banned";
	} elseif($irm_user['access'] == "2"){
		$access = "guest";
	} elseif($irm_user['access'] >= "3"){
		$access = "irm";
	}
$events = json_decode(getCall($config->api_url . "events?transform=1"), true);
?>
<h1>Events <a href="new.php"><i class="fa fa-plus-circle righticon" aria-hidden="true"></i></a></h1>
<div class="list-group">
<?php
foreach($events['events'] as $event){
	$startdate = new DateTime($event['startdate']);
	$enddate = new DateTime($event['enddate']);
	if($startdate > $date && $enddate > $date){
		
		if($access == "irm" || $event['guestOK'] == "1" && $access == "guest"){

			echo '<a href="event.php?event=' . $event['eventID'] . '" class="list-group-item list-group-item-action">' . $startdate->format("d.n.Y"). " - " . $event["event_title"] .'</a>';
		}
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
		