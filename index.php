<?php
	$currentTime = time();	
	//echo date("D M d g:i A", $currentTime);

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
		echo "<table class=\"table classTable\">";
		echo "<tr><th>Event</th><th>Start</th><th>End</th></tr>";
		$eventCursor = $eventCollection->find(array("room"=>$document["classroomName"]));
		foreach($eventCursor as $event)
		{
			$inprogress = false;
			if($event["start"]->sec < time() && $event["end"]->sec > time())
			{
				$inprogress = true;
			}
			$startTime = date("D M d g:i A", $event["start"]->sec);
			$endTime = date("D M d g:i A", $event["end"]->sec);
			echo "<tr><td>" . $event["name"] . "</td><td>" . $startTime . "</td><td>" . $endTime . "</td>";
			//echo "<td>" . $event["start"]->sec . "</td><td>" . $event["end"]->sec  . "</td>";
			//echo "<td>" . $currentTime . "</td>";
			if($inprogress)
				//echo "<td><b>In progress</b></td>";
			echo "</tr>";

		}
		echo "</table>";
	}
	
	require_once('includes/footer.php');
?>
