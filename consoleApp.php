<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], getenv('APP_ENV') ?: 'dev');

$app = new Silex\Application();

require_once __DIR__ . '/config/bootstrap.php';

ini_set('memory_limit', '2G');
ini_set('max_execution_time', '7200');
ini_set('display_errors', 1);
mb_internal_encoding('UTF-8');

/** @var Application $console */
$console = $app['console'];
$console->add(new \Lunches\Command\ChangeOrderStatusCommand());
$console->run();
