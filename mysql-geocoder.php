<?php

require(YOUR_CONFIG_FILE);

define("MAPS_HOST","maps.googleapis.com");
define("KEY",YOUR_GOOGLEMAPS_API_KEY);

$table = YOUR_TABLE_NAME;

//open mysql server

$connection = mysql_connect($host,$user,$password);
if (!$connection){
	die("Not connected:".mysql_error());
}

//set active mysql db
$database = YOUR_DB_NAME;
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected){
	die("Can't use db:".mysql_error());
}

$query = "SELECT * FROM $table ORDER BY id ASC";
$result = mysql_query($query);
if(!$result){
	die("Invalid query".mysql_error());
}

//initialize delay in geocode speed
$delay = 0;
$base_url = "http://".MAPS_HOST."/maps/api/geocode/json?address=";

while($row = @mysql_fetch_assoc($result)){
	$geocode_pending = true;

	while($geocode_pending){

		$address = $row["full_address"];
		$id = $row["id"];

		$request_url = $base_url."".urlencode($address)."&sensor=false";

		sleep(2);

	$json = file_get_contents($request_url);
	$json_decoded = json_decode($json);

	$status = $json_decoded->status;

	if(strcmp($json_decoded->status,"OK")==0){

		$geocode_pending = false;

			$lat = $json_decoded->results[0]->geometry->location->lat;
			$lng = $json_decoded->results[0]->geometry->location->lng;

		echo "here";

		$query = sprintf("UPDATE $table SET latitude='%s', longitude='%s' WHERE id ='%s' LIMIT 1;",
			mysql_real_escape_string($lat),
			mysql_real_escape_string($lng),
			mysql_real_escape_string($id));
		$update_result = mysql_query($query);


		echo $id;

		if(!$update_result){
			die("Invalid query:".mysql_error());
		}
	}

	else {
	//failure to geocode
		$geocode_pending = false;
		echo "Address".$fullAddress."failed to geocoded.";
		echo "Received status".$status."/n";
	}

	}
}

?>