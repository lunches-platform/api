<?php

namespace Lunches\Silex;

use Knp\Console\Application as BaseApplication;
use Lunches\Silex\Application as LunchesApplication;
use Symfony\Component\Console\Input\InputOption;

class ConsoleApplication extends BaseApplication
{
    public function __construct(LunchesApplication $application, $projectDirectory, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($application, $projectDirectory, $name, $version);
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The environment name'));
    }
}
