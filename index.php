<?php
	error_reporting(-1);

	$m = new MongoClient();

	$db = $m->ist;

	$collection = $db->classrooms;

	$cursor = $collection->find();
	$cursor->sort(array('classroomName' => 1));

	foreach($cursor as $document) {
		echo $document["classroomName"]  . " ";
		if(!isset($document["picfile"]))
		{
			echo $document["_id"];
		}
		echo "<br/>";
	}
?>
