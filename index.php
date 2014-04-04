<?php
	$pageTitle = "IST Classrooms";
	require_once('includes/header.php');

	echo "<h1>" . $pageTitle . "</h1>";

	$m = new MongoClient();

	$db = $m->ist;

	$collection = $db->classrooms;
	$eventCollection = $db->events;

	$cursor = $collection->find();
	$cursor->sort(array('classroomName' => 1));

	foreach($cursor as $document) {
		echo '<h2>' . $document["classroomName"] . '</h2>';
		echo "<table class=\"table\">";
		$eventCursor = $eventCollection->find(array("room"=>$document["classroomName"]));
		foreach($eventCursor as $event)
		{
			$startTime = date(DATE_ATOM, $event["start"]->sec);
			$endTime = date(DATE_ATOM, $event["end"]->sec);
			echo "<tr><td>" . $event["name"] . "</td><td>" . $startTime . "</td><td>" . $endTime . "</td></tr>";
		}
		echo "</table>";
	}
	
	require_once('includes/footer.php');
?>
