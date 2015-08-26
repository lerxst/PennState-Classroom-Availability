<?php

// Load init
require_once('../includes/init.php');

// Get controller
$c = isset($_GET['controller']) ? strtolower($_GET['controller']) : null;

switch($c)
{
	case 'classroom':
	default:
		require_once('../includes/controllers/ClassroomController.class.php');
		$controller = new ClassroomController($db);
		break;
}
