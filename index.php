<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

define('inc',true);
define('BASE_PATH', getcwd());
define('DS', DIRECTORY_SEPARATOR);

require_once BASE_PATH . DS . 'core' . DS . 'app.php';

$app = core\app::instance();
$app->run();
