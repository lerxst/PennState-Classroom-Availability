<?php

/** Classroom controller */
class ClassroomController
{
    
    /** Database handle */
    protected $dbHandle = null;
    
    /** Constructor */
    public function __construct($db)
    {
        // Assign database handle
        $this->dbHandle = $db;
        
        // Check for page
        $page = isset($_GET['page']) ? strtolower($_GET['page']) : null;

        switch($page)
        {
            case 'update':
                $this->handleUpdate();
                break;
            case 'view':
            default:
                $this->handleViewClassrooms();
                break;
        }
    }
    
    /** View classroom schedules */
    public function handleViewClassrooms()
    {
        // Get classrooms
        $classrooms = $this->getClassrooms();
        
        // Get schedules
        $schedules = $this->getClassroomSchedules();
        
        // Assign schedule arrays to classrooms
        $classroom = null;
        foreach ($classrooms as &$classroom)
        {
            $classroom['schedules'] = array();
        }
        unset($classroom);

		// Date
		$date = date('Y-m-d', time());
		if (isset($_GET['date']))
			$date = date('Y-m-d', strtotime($_GET['date']));
        
        // Assign schedules to classrooms
        $schedule = null;
        foreach ($schedules as &$schedule)
        {
            // Check that event hasn't occurred and is today
            if (time() < strtotime($schedule['end_time']) && $date === date('Y-m-d', strtotime($schedule['start_time'])))
            {
                // Check for in progress
                $inProgress = (time() > strtotime($schedule['start_time']) && time() < strtotime($schedule['end_time'])) ? true : false;
                
                // Assign in progress status
                $schedule['in_progress'] = $inProgress;
                
                // Assign schedule to classroom
                $classrooms[$schedule['psu_room_id']]['schedules'][] = $schedule;
            }
        }
        unset($schedule);
        
        // Initialize template 
        $tpl = new View();
        
        // Assign classrooms with schedules
        $tpl->classrooms = $classrooms;
        
        // Render template
        $tpl->render('view_classrooms.html');
    }
    
    /** Handle update */
    public function handleUpdate()
    {
		if ($this->getNumberOfUpdates(date('Y-m-d')) > 0)
			die('The website has already been updated today.');

        error_reporting(E_ERROR | E_PARSE);
        $classrooms = $this->getClassrooms();

        foreach ($classrooms as $classroom) {

			// Get data
			$data = json_encode(array(
				'url' => 'https://clc.its.psu.edu/labhours/RoomPrintout.aspx?&room=' . $classroom['psu_room_id'] . '&days=150&date=' . date('m/d/y', time())
			));

			// Make request
			$ch = curl_init('https://iecvames6d.execute-api.us-east-1.amazonaws.com/prod/classroom');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($data))
			);

			// Get result
			$result = curl_exec($ch);

			// Decode JSON
			if ($classroomData = json_decode($result, true))
			{
				// Process dates
				foreach ($classroomData as $day => $classes)
				{
					foreach ($classes as $class)
					{
						// Clean up start time
						$class['start'] = date('Y-m-d H:i:s', strtotime($day . ' ' . $class['start']));

						// Clean up end time
						$class['end'] = date('Y-m-d H:i:s', strtotime($day . ' ' . $class['end']));

						// Clean up name
						$class['name'] = preg_replace('/\s+/', ' ', $class['name']);

						// Add to database
						$this->addClass($classroom['psu_room_id'], $class['start'], $class['end'], $event);
					}
				}
			}

			// Sleep for 1 second
			sleep(1);
        }

		// Mark as updated
		$this->addUpdate(date('Y-m-d'));

		// Close request
		die('Website successfully updated.');
    }
    
    /** Get classroom schedules */
    public function getClassroomSchedules()
    {
        $sql = 'SELECT * FROM classroom_schedules WHERE start_time >= CURDATE()';
        if ($stmt = $this->dbHandle->prepare($sql))
        {
            $stmt->execute();
            $result = $stmt->get_result();
            
            $schedules = array();
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }
            return $schedules;
        } else {
            throw new Exception('Failed to retrieve classroom schedules');
        }
        return null;
    }
    
    /** Get classrooms */
    public function getClassrooms()
    {
        $sql = 'SELECT * FROM classroom_associations ORDER BY classroom_name ASC';
        if ($stmt = $this->dbHandle->prepare($sql))
        {
            $stmt->execute();
            $result = $stmt->get_result();
            $classrooms = array();
            
            while ($row = $result->fetch_assoc()) {
                $classrooms[$row['psu_room_id']] = $row;
            }
            
            return $classrooms;
        } else {
            throw new Exception('Failed to retrieve classroom associations');
        }
        return null;
    }

	/** Add class */
	protected function addClass($psuRoomId, $startTime, $endTime, $className)
	{
		$sql = 'REPLACE INTO classroom_schedules(psu_room_id, start_time, end_time, class_name) VALUES(?,?,?,?)';
		if ($stmt = $this->dbHandle->prepare($sql))
		{
			$stmt->bind_param('isss', $psuRoomId, $startTime, $endTime, $className);
			$stmt->execute();
		} else {
			throw new Exception('Failed to add class.');
		}
	}

	/** Add update */
	protected function addUpdate($date)
	{
		$sql = 'INSERT INTO website_updates(update_date) VALUES (?)';
		if ($stmt = $this->dbHandle->prepare($sql))
		{
			$stmt->bind_param('s', $date);
			$stmt->execute();
		} else {
			throw new Exception('Failed to add update.');
		}
	}

	/** Get number of updates for date */
	protected function getNumberOfUpdates($date)
	{
		$sql = 'SELECT COUNT(*) FROM website_updates WHERE update_date = ?';
		if ($stmt = $this->dbHandle->prepare($sql))
		{
			$stmt->bind_param('s', $date);
			$stmt->bind_result($cnt);
			$stmt->execute();
			$stmt->fetch();
			return $cnt;
		}

		return 0;
	}
    
}
