<?php

// Set timezone
date_default_timezone_set('US/Eastern');

// Load config
require_once('../private/config.php');

// Attempt to set database handle
$db = new mysqli(DatabaseConfig::DB_HOST, DatabaseConfig::DB_USERNAME, DatabaseConfig::DB_PASSWORD, DatabaseConfig::DB_NAME);

// Check for error
if(mysqli_connect_errno())
{
    throw new Exception('Failed to initialize database: ' . mysqli_connect_error());
    exit;
}

// Initialize template class
class View {

    protected $templateDirectory = '../templates/';
    protected $variables = array();

    public function render($templateFile) {
        if (file_exists($this->templateDirectory.$templateFile)) {
            include $this->templateDirectory . $templateFile;
        } else {
            throw new Exception('Cannot find template file ' . $templateFile . ' in directory ' . $this->templateDirectory);
        }
    }

    public function __set($name, $value) {
        $this->variables[$name] = $value;
    }

    public function __get($name) {
        return $this->variables[$name];
    }
}
