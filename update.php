<?php

require_once('config.php');
require_once('createDynamoDBClient.php');
require_once('addDynamoDBItem.php');
session_start();


if (!$_SESSION['me']){
	header('Location: '.SITE_URL);
}
if (count($_POST) == 0) {
	header('Location: '.SITE_URL);
}

$myHour   = sprintf("%'.02d", $_POST['Hour']);
$myMinute = sprintf("%'.02d", $_POST['Minute']);
$mySecond = '00';

$client = createDynamoDBClient();
$email = $_SESSION['email'];
$unixTime = strtotime($_POST['Date'] . ' ' . $myHour . ':' . $myMinute . ':' . $mySecond);
$attendance = $_POST['Attendance'];
$description = $_POST['Description'];
addDynamoDBItem($client,$email,$unixTime,$attendance,$description);

$_SESSION['date'] = $_POST['Date'];
header('Location: '.SITE_URL.'edit.php');

