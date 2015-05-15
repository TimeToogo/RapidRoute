<?php

namespace RapidRoute\Tests;

date_default_timezone_set('UTC');
error_reporting(-1);
set_time_limit(1000);
ini_set('display_errors', 'On');
$projectAsProjectAutoLoaderPath = __DIR__ . '/../vendor/autoload.php';
$projectAsDependencyAutoLoaderPath = __DIR__ . '/../../../../autoload.php';

if (file_exists($projectAsProjectAutoLoaderPath)) {
    $composerAutoLoader = require $projectAsProjectAutoLoaderPath;
} elseif (file_exists($projectAsDependencyAutoLoaderPath)) {
    $composerAutoLoader = require $projectAsDependencyAutoLoaderPath;
} else {
    throw new \Exception('Cannot load project tests: project cannot be loaded, please load project via composer');
}

$composerAutoLoader->addPsr4(__NAMESPACE__ . '\\', __DIR__);
