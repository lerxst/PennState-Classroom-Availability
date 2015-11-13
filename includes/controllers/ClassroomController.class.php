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
        foreach($classrooms as &$classroom)
        {
            $classroom['schedules'] = array();
        }
        unset($classroom);
        
        // Assign schedules to classrooms
        $schedule = null;
        foreach($schedules as &$schedule)
        {
            // Check that event hasn't occurred and is today
            if(time() < strtotime($schedule['end_time']) && date('Y-m-d', time()) === date('Y-m-d', strtotime($schedule['start_time'])))
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
        error_reporting(E_ERROR | E_PARSE);
        $classrooms = $this->getClassrooms();

        foreach($classrooms as $classroom) {            
            $page = file('https://clc.its.psu.edu/labhours/RoomPrintout.aspx?&room=' . $classroom['psu_room_id'] . '&days=150&date=' . date('m/d/y', time()));
            
            $explode = explode('Name">', $page[17]);
            
            $roomNumber = $explode[1];
            $roomNumber = current(explode("<", $roomNumber));
            $startDate  = substr($page[19], 47, 6);
            $endDate    = substr($page[20], 45, 5);
            
            $updateQuery = "";
            
            $numLines = count($page) - 1;
            
            $newDayIndex      = array();
            $newDayIndexIndex = 0;
            
            $dateArray = array();
            
            for ($i = 41; $i < $numLines; $i++) {
                if (preg_match('/cellspacing/', $page[$i])) {
                    $newDayIndex[$newDayIndexIndex] = $i;
                    $dateArray[$newDayIndexIndex]   = current(explode("<", $page[$i + 4]));
                    $newDayIndexIndex++;
                }
            }
            
            $counter        = 1;
            $tableArray     = array();
            $newDayIndex[7] = 999;
            for ($i = 0; $i < count($newDayIndex); $i++) {
                $counter = $newDayIndex[$i];
                $j       = 0;
                while ($counter < $newDayIndex[$i + 1]) {
                    if (preg_match('/table border/', $page[$counter])) {
                        $tableArray[$i][$j] = $counter;
                        $j++;
                    }
                    $counter++;
                }
            }
            
            for ($i = 0; $i < count($dateArray); $i++) {
                foreach ($tableArray[$i] as &$test) {
                    $start = current(explode("<", $page[$test + 3]));
                    $end   = current(explode("<", $page[$test + 5]));
                    $start = strtotime($dateArray[$i] . ' ' . $start);
                    $end   = strtotime($dateArray[$i] . ' ' . $end);
                    $event = trim(current(explode("<", $page[$test + 7])));

					// Add to database
					$this->addClass($classroom['psu_room_id'], date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end), $event);
                }
            }
            sleep(2);
        }

		die('Website successfully updated.');
    }
    
    /** Get classroom schedules */
    public function getClassroomSchedules()
    {
        $sql = 'SELECT * FROM classroom_schedules WHERE start_time >= CURDATE()';
        if($stmt = $this->dbHandle->prepare($sql))
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
        if($stmt = $this->dbHandle->prepare($sql))
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
    
}
