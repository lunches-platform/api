<?php
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'config/bootstrap.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($app['doctrine.em']);
