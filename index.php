<?php
	$pageTitle = "IST Classroom Availability";
	require_once('includes/header.php');

	echo "<h1>" . $pageTitle . "<font color='#00ADEF'> pre-alpha</font></h1>";

	$m = new MongoClient();

	$db = $m->ist;

	$collection = $db->classrooms;
	$eventCollection = $db->events;

	$cursor = $collection->find();
	$cursor->sort(array('classroomName' => 1));

	foreach($cursor as $document) {
		echo '<h2>' . $document["classroomName"] . '</h2>';
		echo "<table class=\"table\">";
		echo "<tr><th>Event</th><th>Start</th><th>End</th></tr>";
		$eventCursor = $eventCollection->find(array("room"=>$document["classroomName"]));
		foreach($eventCursor as $event)
		{
			$startTime = date("D M d g:i A", $event["start"]->sec);
			$endTime = date("D M d g:i A", $event["end"]->sec);
			echo "<tr><td>" . $event["name"] . "</td><td>" . $startTime . "</td><td>" . $endTime . "</td></tr>";
		}
		echo "</table>";
	}
	
	require_once('includes/footer.php');
?>
