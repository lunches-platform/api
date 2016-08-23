<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Silex\Application();

require_once __DIR__.'/bootstrap.php';

ini_set('memory_limit', '2G');
ini_set('max_execution_time', '7200');
ini_set('display_errors', 1);
mb_internal_encoding('UTF-8');

/** @var Application $console */
$console = $app['console'];
$console->add(new \Lunches\Command\StartOrderProgressCommand());
$console->run();
