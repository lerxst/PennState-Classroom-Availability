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
        $sql = 'SELECT * FROM classroom_associations';
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

}
