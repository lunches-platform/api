<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$kernel = new AppKernel('prod', $request->query->has('debug_s'));
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
