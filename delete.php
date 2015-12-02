<?php

require_once('config.php');
require_once('createDynamoDBClient.php');
require_once('deleteDynamoDBItem.php');
session_start();


if (!$_SESSION['me']){
	header('Location: '.SITE_URL);
}
if (count($_POST) == 0) {
	header('Location: '.SITE_URL);
}

$client = createDynamoDBClient();
$email = $_SESSION['email'];
$unixTime = $_POST['UnixTime'];
deleteDynamoDBItem($client,$email,$unixTime);

$_SESSION['date'] = $_POST['Date'];
header('Location: '.SITE_URL.'edit.php');

