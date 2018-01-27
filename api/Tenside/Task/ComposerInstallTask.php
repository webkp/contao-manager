<?php

/*
 * This file is part of Contao Manager.
 *
 * Copyright (c) 2016-2017 Contao Association
 *
 * @license LGPL-3.0+
 */

namespace Contao\ManagerApi\Tenside\Task;

use Contao\ManagerApi\ApiKernel;
use Contao\ManagerApi\Process\ConsoleProcessFactory;
use Tenside\Core\Task\AbstractCliSpawningTask;
use Tenside\Core\Util\JsonArray;

/**
 * This class runs the dump-autoload command.
 */
class ComposerInstallTask extends AbstractCliSpawningTask
{
    /**
     * @var ApiKernel
     */
    private $kernel;

    /**
     * @var ConsoleProcessFactory
     */
    private $processFactory;

    /**
     * {@inheritdoc}
     */
    public function __construct(ApiKernel $kernel, ConsoleProcessFactory $processFactory, JsonArray $file)
    {
        parent::__construct($file);

        $this->kernel = $kernel;
        $this->processFactory = $processFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function doPerform()
    {
        $process = $this->processFactory->createManagerConsoleProcess(
            [
                'composer',
                'install',
                '--optimize-autoloader',
                '--no-dev',
                '--no-progress',
                '--no-interaction',
            ]
        );

        $this->runProcess($process);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'composer-install';
    }
}