<?php
session_start();
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
				<a class="nav-link" href="#">Meetup<span class="sr-only">(current)</span></a>
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

require '../global/functions/apicalls.php';
require '../global/functions/telegram.php';
$config = require "../config.php";


$tg_user = getTelegramUserData();

if ($tg_user !== false) {
?>
<h1>New Event</h1>
<form action="?add=1" method="POST">
  <div class="form-group">
  <label for="title">Event title</label>
    <input type="text" class="form-control" name="title" id="title" placeholder="SAUFEN SAUFEN SAUFEN">
  </div>
  <div class="form-group">
  <label for="startdate">Event start</label>
    <input type="datetime-local" class="form-control" name="startdate" id="startdate" placeholder="2018-27-42 00:00:00">
  </div>
  <div class="form-group">
  <label for="enddate">Event end</label>
    <input type="datetime-local" class="form-control" name="enddate" id="enddate" placeholder="2018-27-42 00:00:00">
  </div>
  <div class="form-group">
  <label for="url">Event Link</label>
    <input type="url" class="form-control" name="url" id="url" placeholder="https://italianrockmafia.ch">
  </div>
  <div class="form-group">
  <label for="station">Event Location / Station</label>
    <input type="text" class="form-control" name="station" id="station" placeholder="Baden">
	<small id="stationHelp" class="form-text text-muted">Please provide the name, as it is in the SBB mobile app.</small>
  </div>
  <div class="form-group">
  <label for="description">Event Description</label>
  <textarea class="form-control" id="description" rows="3"></textarea>
  </div>

  <button type="submit" class="btn btn-success">Submit</button>

</form>
	</div><?php
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
