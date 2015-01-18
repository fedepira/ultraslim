<?php
require_once 'whoops/vendor/autoload.php';
$whoops = new Whoops\Run();
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
$whoops->register(); 

require_once '../vendor/autoload.php';
require_once 'app.php';