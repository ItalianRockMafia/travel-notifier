<?php
session_start();
$eventID = $_GET['event'];
require_once '../global/functions/apicalls.php';
require_once '../global/functions/telegram.php';
require_once '../global/functions/irm.php';
$config = require_once "../config.php";

require_once '../global/functions/header.php';
require_once '../global/functions/footer.php';

$menu = renderMenu();
$options['nav'] = $menu;
$options['title'] = "IRM | event detail";
$header = getHeader($options);
$footer = renderFooter();


$tg_user = getTelegramUserData();
saveSessionArray($tg_user);
$event2del = $_GET['delete'];
if(isset($_GET['delete'])){
	deleteCall($config->api_url . "events/" . $event2del);
	header('Location: https://italianrockmafia.ch/meetup/index.php');
}

if(isset($_GET['signup'])){
	if ($tg_user !== false) {
	$user = $_SESSION['irmID'];
	$postfields = "{\n \t \"userIDFK\": \"$user\", \n \t \"eventIDFK\": \"$eventID\" \n }";
	$result = postCall($config->api_url . "attendes", $postfields);
	$event = json_decode(getCall($config->api_url . "events/" . $eventID), true);
	$text = urlencode('<a href="tg://user?id=' . $_SESSION['tgID'] . '">' . $_SESSION['firstname'] . ' ' . $_SESSION['lastname'] . ' (' . $tg_user['username'] . ')</a> signed up for event <a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '">' . $event['event_title'] . '</a>.');
	$msg = getCall("https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $text);
	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
	} else {
		echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
	}
}

if(isset($_GET['cancel'])){
		$list = json_decode(getCall($config->api_url . 'attendes?transform=1&filter[]=userIDFK,eq,' . $_SESSION['irmID'] . '&filter[]=eventIDFK,eq,' . $eventID . "satisfy=all"), true);
		foreach($list['attendes'] as $user){
			$attende2del = $user['attendeID'];
		}
	
		$result = deleteCall($config->api_url . "attendes/" . $attende2del);
		header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
	}

if(isset($_GET['addcar'])){
	$car2add = $_GET['addcar'];
	$driver = $_SESSION['irmID'];
	$postfields = "{\n \t \"userIDFK\": \"$driver\", \n \t \"eventIDFK\": \"$eventID\", \n \t \"carIDFK\": \"$car2add\" \n }";
	$result = postCall($config->api_url . "eventCarUsers", $postfields);
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);	
	$text = urlencode('<a href="tg://user?id=' . $_SESSION['tgID'] . '">' . $_SESSION['username'] . '</a> added a car to ' . $event['event_title'] . chr(10) . 
							'If you want to join as a passenger, <a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&add2car=' . $car2add . '">click here</a>.');
	$msg = getCall("https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $text);
							
	if(is_numeric($result)){
		header('Location: https://italianrockmafia.ch/meetup/event.php?caradd=success&event=' . $eventID);
	} else {
		header('Location: https://italianrockmafia.ch/meetup/event.php?caradd=fail&event=' . $eventID);
	}
}
if(isset($_GET['deleteCar'])){
	$car2del = $_GET['deleteCar'];
	$records = json_decode(getCall($config->api_url . "eventCarUsers?transform=1&filter[]=eventIDFK,eq," . $eventID . "&filter[]=carIDFK,eq," . $car2del),true);
	$recIDs = array();
	foreach($records['eventCarUsers'] as $record){
		$recIDs[] = $record['comboID'];
	}
	foreach($recIDs as $id){
		$result = deleteCall($config->api_url . "eventCarUsers/" . $id);
	}
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);

	$text = urlencode('<a href="tg://user?id=' . $_SESSION['tgID'] . '">' . $_SESSION['username'] . '</a> removed his car from the event "' . $event['event_title'] . '".');
	$msg = getCall("https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $text);
	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
	
}

if(isset($_GET['add2car'])){
	$car2add = $_GET['add2car'];
	$passenger = $_SESSION['irmID'];
	$postfields = "{\n \t \"userIDFK\": \"$passenger\", \n \t \"eventIDFK\": \"$eventID\", \n \t \"carIDFK\": \"$car2add\" \n }";
	$result = postCall($config->api_url . "eventCarUsers", $postfields);
	$ownerarr = json_decode(getCall($config->api_url . "carUsers?transform=1&filter=carID,eq," . $car2add) , true);
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);
	foreach($ownerarr['carUsers'] as $owner){
		$tgID = $owner['telegramID'];
		$tgName = $owner['tgusername'];
	}
	$text = urlencode("Hi, " . $tgName . chr(10) . '<a href="tg://user?id=' . $_SESSION['tgID'] . '">' . $_SESSION['username'] . '</a> signed up to drive with you to ' . $event['event_title'] .
										"." .	chr(10) . 'If it\'s ok, ignore this message. Else, you can <a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '&delpassenger=' . 
										$passenger . 'car=' . $car2add . '">remove the person from your car</a>.');
	$msg = getCall("https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $tgID . "&parse_mode=HTML&text=" . $text);

	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
	
}

if(isset($_GET['delpassenger'])){
	$leaver = $_GET['delpassenger'];
	$car2free = $_GET['car'];
	$records = json_decode(getCall($config->api_url . "eventCarUsers?transform=1&filter[]=userIDFK,eq," . $leaver . "&filter[]=carIDFK,eq," . $car2free . "&filter[]=eventIDFK,eq," . $eventID . "&satisfy=all"),true);
	foreach($records['eventCarUsers'] as $record){
		$id2kill = $record['comboID'];
	}
	$result = deleteCall($config->api_url . "eventCarUsers/" . $id2kill);
	$user = json_decode(getCall($config->api_url . "users/" . $leaver . "?transform=1"), true);
	$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);
	$text = urlencode('Hi <a href="tg://user?id=' . $user['telegramID'] . '">'. $user['tgusername'] . '</a>'.  chr(10) . 'You have been removed from a car driving to <a href="https://italianrockmafia.ch/meetup/event.php?event=' . $eventID . '">' . $event['event_title'] . '</a>');
	$msg = getCall("https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $user['telegramID'] . "&parse_mode=HTML&text=" . $text);	
	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
}

if(isset($_GET['addcomment'])){
	$comment = $_POST['Newcomment'];
	$irmID = $_SESSION['irmID'];
	$commentPost = "{\n \t \"comment\": \"$comment\", \n \t \"authorIDFK\": \"$irmID\" \n }";
	$newComID = postCall($config->api_url . "comments", $commentPost);
	if(is_numeric($newComID)){
		$eventPost = "{\n \t \"eventIDFK\": \"$eventID\", \n \t \"commentIDFK\": \"$newComID\" \n }";
		postCall($config->api_url . "eventComments", $eventPost);
		$event = json_decode(getCall($config->api_url . "events/" . $eventID . "?transform=1"),true);		
		$alertText = urlencode( "@" . $tg_user['username'] . ' made a new comment on event <a href="' . $config->app_url . "meetup/event.php?event=" . $eventID . '">'. $event['event_title'] . '</a>:' . chr(10) . $comment);
		$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=" .  $config->telegram['chatID'] . "&parse_mode=HTML&text=" . $alertText;		
		getCall($alertURL);
	}
	header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
}

echo $header;
?>

<div class="topspacer"></div>
<main role="main">
	<div class="container">

<?php

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
	if ($_GET['caradd'] == "success"){
		echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
		<strong>Success!</strong> Your car has been added.
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>';
	}

	if ($_GET['caradd'] == "fail"){
		echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong>Error!</strong> There was an error adding you car. Are you already signed up with another car on this event?
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>';
	}

	$mycars = json_decode(getCall($config->api_url . "carUsers?transform=1&filter=telegramID,eq," . $tg_user['id']), true);

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
	?>

<form method="post" action="add2cal.php">
  <input type="hidden" name="date_start" value="<?php echo date("Y-m-d g:iA", $startdate); ?>">
  <input type="hidden" name="date_end" value="<?php echo date("Y-m-d g:iA", $enddate); ?>">
  <input type="hidden" name="location" value="<?php echo $event['station'];?>">
  <input type="hidden" name="description" value="<?php echo $event['description'];?>">
  <input type="hidden" name="summary" value="<?php echo $event['event_title'];?>">
  <input type="hidden" name="url" value="<?php echo $event['url'];?>">
  <button type="submit" class="btn btn-success" value="Add to Calendar">Add to Calendar</button>
</form>

<?php
	
	echo '<p>More: <a href="' . $event['url'] . '" target="_blank">' . $event['url'] . '</a></p>';
	echo '<p>Creator: <a href="https://t.me/' . $creator['tgusername'] . '" target="_blank">' . $creator['firstname'] . ' ' . $creator['lastname'] . ' (' . $creator['tgusername'] .')</a></p>';
?>
<a href="index.php"><button type="button" class="btn btn-success">Back</button></a>
<?php
$url = $config->api_url . 'attendes?transform=1&filter[]=userIDFK,eq,' . $_SESSION['irmID'] . '&filter[]=eventIDFK,eq,' . $eventID . "&satisfy=all";
$StatusChecker = json_decode(getCall($url), true);

foreach($StatusChecker['attendes'] as $attende){

	if($eventID == $attende['eventIDFK'] && $_SESSION['irmID'] == $attende['userIDFK']){
		echo '<a href="?event=' . $eventID . '&cancel=1" class="btn btn-success">Cancel</a>';
	}

}
if(empty($StatusChecker['attendes'])){
	echo '<a href="?event=' . $eventID . '&signup=1" class="btn btn-success">Sign up</a>';
} 

?>

<?php echo '<a href="tgsender.php?event=' . $eventID . '&send=1" class="btn btn-success"><i class="fab fa-telegram"></i> Send connection</a>';

if($creator['tgusername']  == $tg_user['username']){
	echo '<a href="edit.php?event=' . $eventID . '" class="btn btn-success" >Edit Event</a>';	
	echo '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteEvent">Delete Event</button>';
}

?>
<div class="topspacer"></div>
<?php
if($event['guestOK'] == '1'){
	echo '<p class="big">This is a public event. Guests are allowed.</p>
	';
} ?>
<h2>Attendes:</h2>
<ol>
<?php
$attendes = json_decode(getCall($config->api_url . 'eventAttendes?transform=1&filter=eventIDFK,eq,' . $eventID), true);
foreach($attendes['eventAttendes'] as $attende){
	
echo '<li><a href="https://t.me/' . $attende["tgusername"] . '" target="_blank">' . $attende["firstname"] . ' ' . $attende["lastname"] . ' (' . $attende["tgusername"] . ')</a></li>';
}
echo '</ol>';
?>
<h2>Cars</h2>
<button class="btn btn-success" data-toggle="modal" data-target="#addCar">Add my car</button>
<?php
$eventCars = json_decode(getCall($config->api_url . "eventCarUsers?filter=eventIDFK,eq," . $eventID . "&transform=1"), true);
echo '<div id="accordion">';

$carsprinted = array();
foreach($eventCars["eventCarUsers"] as $carBin){
	if(!in_array($carBin['carIDFK'], $carsprinted)){
		//Get details of car
		$cardetails = json_decode(getCall($config->api_url . "carUsers?transform=1&filter=carID,eq," . $carBin['carIDFK'] ), true);
		$car = $cardetails['carUsers'][0];
		//Get list of passengers
		$passengers = json_decode(getCall($config->api_url . "eventCarUsers?transform=1&filter[]=carIDFK,eq," . $carBin['carIDFK'] . "&filter[]=eventIDFK,eq," . $eventID . "satisfy=all"), true);
		//calculate free space
		$noOfPassangers = count($passengers['eventCarUsers']);
		$freeSpace = $car['places'] - $noOfPassangers;
		//check owner
		if($_SESSION['tgID'] === $car['telegramID']){
			$owner = true;
		} 
		//echo list
		echo '<div class="card">
    <div class="card-header" id="heading-'. $carBin['carIDFK'].'">
      <h5 class="mb-0">
        <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#members-'. $carBin['carIDFK'].'" aria-expanded="false" aria-controls="members-'. $car['carIDFK'].'">
				' . $car['brand'] . ' ' . $car['model'] . ' (' . $car['color'] . ') ' . 'Owner: ' . $car['tgusername'];
				if($freeSpace > 0){
					echo ' <span class="badge badge-success badge-pill">'.$freeSpace.'</span> ';
				} else {
					echo ' <span class="badge badge-danger badge-pill">full</span> ';
				} 
				
				echo '</button>
      </h5>
    </div>

    <div id="members-'. $carBin['carIDFK'].'" class="collapse" aria-labelledby="heading-'. $carBin['carIDFK'].'" data-parent="#accordion">
			<div class="card-body"><ul>';
			foreach($passengers['eventCarUsers'] as $passenger){
				$details = json_decode(getCall($config->api_url. "users/". $passenger['userIDFK'] . "?transform=1"), true);
				echo '<li>' . $details['tgusername'];
				if($tg_user['username'] == $details['tgusername'] && !$owner){
					echo ' <a href="?delpassenger=' . $passenger['userIDFK']  . '&car='. $passenger['carIDFK'] .  '&event=' . $eventID . '"><i class="fa fa-times"></i></a>';
				} 
				if($tg_user['username'] != $details['tgusername'] && $owner){
					echo ' <a href="?delpassenger=' . $passenger['userIDFK'] . '&car='. $passenger['carIDFK'] .  '&event=' . $eventID . '"><i class="fa fa-trash"></i></a>';
				}
				
				echo '</li>';
			}
			echo '</ul>';
			if($owner){
				echo ' <a href="?event=' . $eventID . '&deleteCar='. $car['carID'] . '" class="btn btn-danger">Remove car from event</a>';
			}
			if(!$passengerCheck){
				' <a href="?event=' . $eventID . '&addPassenger='. $_SESSION['irmID'] . '" class="btn btn-success">Add me to this car</a>';
			}
			if(!in_array($_SESSION['irmID'], $carBin)){
				echo ' <a href="?event=' . $eventID . '&add2car='. $car['carID'] . '" class="btn btn-success ';if($freeSpace < 1){ echo 'disabled';} echo'">Add me to this car</a>';
								
			}

			echo '</div>
    </div>
	</div>
';
$carsprinted[] = $carBin['carIDFK'];
	}
}
echo '</div>';

echo '<h2>Comments</h2>';
$eventCommentsID = json_decode(getCall($config->api_url . "eventComments?transform=1&filter=eventIDFK,eq," . $eventID), true);
foreach($eventCommentsID['eventComments'] as $commentIDs){
	$commRecs[] = $commentIDs['commentIDFK'];
}
if(!empty($commRecs)){
	$qrystr = "";
	foreach($commRecs as $commID){
		$qrystr .= $commID . ",";
	}
	$qrystr = rtrim($qrystr,",");
	$comments = json_decode(getCall($config->api_url . "comments/" . $qrystr . "?transform=1&order=commentID,asc"), true);
	if (!isset($comments[0])) $comments=[$comments];
		foreach($comments as $comment){
			$author = json_decode(getCall($config->api_url . "users/" . $comment['authorIDFK'] . "?transform=1"), true);
			echo '<div class="card">
				<div class="card-body">
	 			'. $comment['comment'] .'
	 			<footer class="blockquote-footer">'. $author['tgusername'].'</footer>			 
				</div>
				</div>';
		}
	} else {
			echo '<div class="alert alert-warning" role="alert">
				No comments.
				</div>';
	}
?> <h3>New comment</h3>
<form action="?addcomment=1&event=<?php echo $eventID;?>" method="POST">
	<div class="form-group">
			<label for="Newcomment">Your comment</label>
			<textarea class="form-control" id="Newcomment" name="Newcomment" rows="3"></textarea>
		</div>
		<button type="submit" class="btn btn-success">Submit</button>

</form>
  
<?php

} else {
	echo '
	<div class="alert alert-danger" role="alert">
	<strong>Error.</strong> You need to <a href="https://italianrockmafia.ch/login.php">login</a> first.
  </div>
';
}
?>

<!-- Modal coming soon -->
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


<!-- Modal delete event-->
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


<!-- Modal add car-->
<div class="modal fade" id="addCar" tabindex="-1" role="dialog" aria-labelledby="addCarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCarLabel">Add a car</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <strong>Select one of your cars</strong>
				<div class="list-group">
				<?php

					foreach($mycars['carUsers'] as $car){
						echo '<a href="?addcar=' .  $car['carID'] . '&event=' . $eventID .'" class="list-group-item list-group-item-action">' . $car["brand"] . ' '. $car['model'] . '</a>';
					}
				?>
				</div>
      </div>
      <div class="modal-footer">
			<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
			
      </div>
    </div>
  </div>
</div>


<?php
echo $footer;