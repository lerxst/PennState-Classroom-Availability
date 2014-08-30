<?php
	$currentTime = time();	

	$pageTitle = 'IST Classroom Availability';
	require_once('includes/header.php');

	echo '<h1>' . $pageTitle . '<font color="#00ADEF"> pre-alpha</font></h1>';

	$m = new MongoClient();

	$db = $m->ist;

	$collection = $db->classrooms;
	$eventCollection = $db->events;

	$cursor = $collection->find();
	$cursor->sort(array('classroomName' => 1));

	foreach($cursor as $document) {
		echo '<h2>' . $document['classroomName'] . '</h2>';
		echo '<table class="table classTable">';
		echo '<tr><th>Event</th><th>Start</th><th>End</th></tr>';
		$eventCursor = $eventCollection->find(array('room'=>$document['classroomName']));
		foreach($eventCursor as $event)
		{
			$startTs = $event['start']->sec;
			$endTs = $event['end']->sec;
			$timeTs = strtotime('-4 hours');
			$start = date('D M d g:i A', $startTs);
			$end = date('D M d g:i A', $endTs);
			$time = date('D M d g:i A', $timeTs);
			
			if($endTs >= $timeTs && date('Y-m-d', $timeTs) == date('Y-m-d', $startTs))
			{
				$inProgress = false;
				if($startTs <= $timeTs && $endTs >= $timeTs)
				{
					$inProgress = true;
				}
				echo '<tr><td>' . $event['name'];
				if($inProgress)
				{
					echo '<font color="#00ADEF">&nbsp;&nbsp;<b>In Progress</b></font>';
				}
				echo '</td><td>' . $start . '</td><td>' . $end . '</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
	}
	
	require_once('includes/footer.php');
?>
