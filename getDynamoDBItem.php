<?php

require_once('config.php');

function getDynamoDBItem($client,$email,$unixTimeFrom,$unixTimeTo) {
	try {
		$result = $client->getIterator('Query', array(
		    'TableName' => DynamoDB_WORKTIME_TABLE,
		    'KeyConditions' => array(
		        'Email' => array(
		            'AttributeValueList' => array(
		                array('S' => $email)
		            ),
		            'ComparisonOperator' => 'EQ'
		        ),
		        'UnixTime' => array(
		            'AttributeValueList' => array(
		                array('N' => $unixTimeFrom),
		                array('N' => $unixTimeTo)
		            ),
		            'ComparisonOperator' => 'BETWEEN'
		        ),
		    )
		));
	} catch (exception $e) {
		echo 'DynamoDBアイテム取得の例外：', $e->getMessage(), "\n";
		exit;
	}
	return $result;
}
