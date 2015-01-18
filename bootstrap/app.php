<?php
$app = new Slim\Slim(array('debug' => true));

require_once 'functions.php';
require_once 'protos.php';
require_once '../app/routes.php';
require_once '../app/filters.php';