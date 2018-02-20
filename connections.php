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
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		

		<script>
        $(function () {
         
            function reset() {
                $('table.connections tr.connection').show();
                $('table.connections tr.section').hide();
            }
            $('table.connections tr.connection').bind('click', function (e) {
                reset();
                var $this = $(this);
                $this.hide();
                $this.nextAll('tr.section').show();
                if ('replaceState' in window.history) {
                    history.replaceState({}, '', '?' + $('.pager').serialize() + '&c=' + $this.data('c'));
                }
            });
            $('.station input').bind('focus', function () {
                var that = this;
                setTimeout(function () {
                    that.setSelectionRange(0, 9999);
                }, 10);
            });
        });
    </script>

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

$userArray = json_decode(getCall($config->api_url . "userStation?transform=1&filter=userID,eq," . $_SESSION['irmID']),true);
foreach($userArray['userStation'] as $user){
$station = $user["station"];
}

$eventArray = json_decode(getCall($config->api_url . "events/" . $eventID . "&transform=1"), true);

$eventStation = $eventArray["station"];
$startdate = date("Y-m-d", strtotime($eventArray['startdate']));
$starttime = date("H:i", strtotime($eventArray['startdate']));

$url = "http://transport.opendata.ch/v1/connections?from=" . urlencode($station) . "&to=" . urlencode($eventStation) . "&date=" .$startdate . "&time=". $starttime . "&isArrivalTime=1";
$response = json_decode(file_get_contents($url));


if ($response->from) {
	$from = $response->from->name;
	$x = $response->from->coordinate->x;
	$y = $response->from->coordinate->y;
	$fromLink = '<a href="https://www.google.ch/maps/@' . $x .',' . $y . ',18z">';
}
if ($response->to) {
	$to = $response->to->name;
	$x = $response->to->coordinate->x;
	$y = $response->to->coordinate->y;
	$toLink = '<a href="https://www.google.ch/maps/@' . $x .',' . $y . ',18z">';
}
if (isset($response->stations->from[0])) {
	if ($response->stations->from[0]->score < 101) {
		foreach (array_slice($response->stations->from, 1, 3) as $station) {
			if ($station->score > 97) {
				$stationsFrom[] = $station->name;
			}
		}
	}
}
if (isset($response->stations->to[0])) {
	if ($response->stations->to[0]->score < 101) {
		foreach (array_slice($response->stations->to, 1, 3) as $station) {
			if ($station->score > 97) {
				$stationsTo[] = $station->name;
			}
		}
	}
}

if(isset($_GET['send'])){
$sendText = 'Your connection from: ' .$fromLink . $from . '</a>' . chr(10). 'to: ' . $toLink . $to . '</a>' . chr(10) . 'departure: ' . date('d.m.Y H:i', strtotime($response->connections[0]->from->departure)) . chr(10) . 
'arrival: ' . date('d.m.Y H:i', strtotime($response->connections[0]->to->arrival)) . chr(10). 'duration: ';

$duration =  (substr($response->connections[0]->duration, 0, 2) > 0) ? htmlentities(trim(substr($response->connections[0]->duration, 0, 2), '0')).'d ' : '';

$duration .= htmlentities(trim(substr($response->connections[0]->duration, 3, 1), '0').substr($response->connections[0]->duration, 4, 4)) .'′' . chr(10);
$sendText .= $duration . chr(10) . 'journey:' . chr(10);

foreach ($response->connections[0]->sections as $section){
	$fromx = $section->departure->station->coordinate->x;
	$fromy = $section->departure->station->coordinate->y;
	$fromLink = '<a href="https://www.google.ch/maps/@' . $fromx .',' . $fromy . ',18z">';
	$tox = $section->arrival->station->coordinate->x;
	$toy = $section->arrival->station->coordinate->y;
	$toLink = '<a href="https://www.google.ch/maps/@' . $tox .',' . $toy . ',18z">';
	$sendText .=  date('H:i', strtotime($section->departure->departure)) . ' from ' . $fromLink .  htmlentities($section->departure->station->name, ENT_QUOTES, 'UTF-8') . '</a>' . chr(10);
	$sendText .=  htmlentities($section->journey->name, ENT_QUOTES, 'UTF-8') . ' number: ' . htmlentities($section->journey->number, ENT_QUOTES, 'UTF-8') . chr(10);
	$sendText .=  date('H:i', strtotime($section->arrival->arrival)) . ' to ' . $toLink . htmlentities($section->arrival->station->name, ENT_QUOTES, 'UTF-8') . '</a>' .chr(10); 
	
}
$sendText .=  chr(10) . chr(10) . '<a href="https://italianrockmafia.ch/meetup/connections.php?event=' . $eventID . '">View connections on web</a>';

$alertURL = "https://api.telegram.org/bot" . $config->telegram['token'] . "/sendMessage?chat_id=". $_SESSION['tgID']. "&parse_mode=HTML&text=" . urlencode($sendText);
getCall($alertURL);
header('Location: https://italianrockmafia.ch/meetup/event.php?event=' . $eventID);
}
?>
<div class="alert alert-primary alert-dismissible fade show" role="alert">
  Click on a connection to enlarge.
 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>


<table class="table connections">
<colgroup>
	<col width="20%">
	<col width="57%">
	<col width="23%">
</colgroup>
<thead>
	<tr>
		<th>Time</th>
		<th>Journey</th>
		<th>
			<span class="visible-xs-inline">Pl.</span>
			<span class="hidden-xs">Platform</span>
		</th>
	</tr>
</thead>
<?php $j = 0; ?>
<?php foreach ($response->connections as $connection): ?>
	<?php $j++; ?>
	<tbody>
		<tr class="connection"<?php if ($j == $c): ?> style="display: none;"<?php endif; ?> data-c="<?php echo $j; ?>">
			<td>
				<?php echo date('H:i', strtotime($connection->from->departure)); ?>
				<?php if ($connection->from->delay): ?>
					<span style="color: #a20d0d;"><?php echo '+'.$connection->from->delay; ?></span>
				<?php endif; ?>
				<br/>
				<?php echo date('H:i', strtotime($connection->to->arrival)); ?>
				<?php if ($connection->to->delay): ?>
					<span style="color: #a20d0d;"><?php echo '+'.$connection->to->delay; ?></span>
				<?php endif; ?>
			</td>
			<td>
				<?php echo (substr($connection->duration, 0, 2) > 0) ? htmlentities(trim(substr($connection->duration, 0, 2), '0')).'d ' : ''; ?>
				<?php echo htmlentities(trim(substr($connection->duration, 3, 1), '0').substr($connection->duration, 4, 4)); ?>′<br/>
				<span class="muted">
				<?php echo htmlentities(implode(', ', $connection->products)); ?>
				</span>
			</td>
			<td>
				<?php if ($connection->from->prognosis->platform): ?>
					<span style="color: #a20d0d;"><?php echo htmlentities($connection->from->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
				<?php else: ?>
					<?php echo htmlentities($connection->from->platform, ENT_QUOTES, 'UTF-8'); ?>
				<?php endif; ?>
				<br/>
				<?php if ($connection->capacity2nd > 0): ?>
					<small title="Expected occupancy 2nd class">
						<?php for ($i = 0; $i < 3; $i++): ?>
							<?php if ($i < $connection->capacity2nd): ?>
								<span class="glyphicon glyphicon-user text-muted"></span>
							<?php else: ?>
								<span class="glyphicon glyphicon-user text-disabled"></span>
							<?php endif; ?>
						<?php endfor; ?>
					</small>
				<?php endif; ?>
			</td>
		</tr>
		<?php $i = 0; foreach ($connection->sections as $section): ?>
			<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
				<td rowspan="2">
					<?php echo date('H:i', strtotime($section->departure->departure)); ?>
					<?php if ($section->departure->delay): ?>
						<span style="color: #a20d0d;"><?php echo '+'.$section->departure->delay; ?></span>
					<?php endif; ?>
				</td>
				<td>
					<?php echo htmlentities($section->departure->station->name, ENT_QUOTES, 'UTF-8'); ?>
				</td>
				<td>
					<?php if ($section->departure->prognosis->platform): ?>
						<span style="color: #a20d0d;"><?php echo htmlentities($section->departure->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php else: ?>
						<?php echo htmlentities($section->departure->platform, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
				<td style="border-top: 0; padding: 4px 8px;">
					<span class="muted">
					<?php if ($section->journey): ?>
						<?php echo htmlentities($section->journey->name, ENT_QUOTES, 'UTF-8'); ?>
					<?php else: ?>
						Walk
					<?php endif; ?>
					</span>
				</td>
				<td style="border-top: 0; padding: 4px 8px;">
					<small title="Expected occupancy 2nd class">
						<?php if ($section->journey && $section->journey->capacity2nd > 0): ?>
							<?php for ($i = 0; $i < 3; $i++): ?>
								<?php if ($i < $section->journey->capacity2nd): ?>
									<span class="glyphicon glyphicon-user text-muted"></span>
								<?php else: ?>
									<span class="glyphicon glyphicon-user text-disabled"></span>
								<?php endif; ?>
							<?php endfor; ?>
						<?php endif; ?>
					</small>
				</td>
			</tr>
			<tr class="section"<?php if ($j != $c): ?> style="display: none;"<?php endif; ?>>
				<td style="border-top: 0;">
					<?php echo date('H:i', strtotime($section->arrival->arrival)); ?>
					<?php if ($section->arrival->delay): ?>
						<span style="color: #a20d0d;"><?php echo '+'.$section->arrival->delay; ?></span>
					<?php endif; ?>
				</td>
				<td style="border-top: 0;">
					<?php echo htmlentities($section->arrival->station->name, ENT_QUOTES, 'UTF-8'); ?>
				</td>
				<td style="border-top: 0;">
					<?php if ($section->arrival->prognosis->platform): ?>
						<span style="color: #a20d0d;"><?php echo htmlentities($section->arrival->prognosis->platform, ENT_QUOTES, 'UTF-8'); ?></span>
					<?php else: ?>
						<?php echo htmlentities($section->arrival->platform, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
<?php endforeach; ?>
</table>

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