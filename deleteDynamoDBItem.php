<?php

require_once('config.php');

function deleteDynamoDBItem($client,$email,$unixTime) {
	try {
		$result = $client->deleteItem(array(
		    'TableName' => DynamoDB_WORKTIME_TABLE,
		    'Key' => array(
		        'Email'      => array('S' => $email),
		        'UnixTime'    => array('N' => $unixTime)
		    )
		));
	} catch (exception $e) {
		echo 'DynamoDB削除の例外：', $e->getMessage(), "\n";
		exit;
	}
}
