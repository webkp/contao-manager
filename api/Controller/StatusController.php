<?php

/*
 * This file is part of Contao Manager.
 *
 * Copyright (c) 2016-2017 Contao Association
 *
 * @license LGPL-3.0+
 */

namespace Contao\ManagerApi\Controller;

use Contao\ManagerApi\Tenside\InstallationStatusDeterminator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Tenside\Core\Config\TensideJsonConfig;
use Tenside\Core\SelfTest\SelfTest;
use Tenside\Core\SelfTest\SelfTestResult;

class StatusController extends Controller
{
    const STATUS_NEW = 'new'; // Manager not installed
    const STATUS_AUTHENTICATE = 'auth'; // Manager installed, requires authentication
    const STATUS_CONFLICT = 'conflict'; // Manager has conflict
    const STATUS_EMPTY = 'empty'; // Contao not installed
    const STATUS_OK = 'ok'; // Contao is ready
    const STATUS_BROKEN = 'broken'; // Contao is broken

    /**
     * @var InstallationStatusDeterminator
     */
    private $status;

    /**
     * @var SelfTest
     */
    private $selfTest;

    /**
     * Constructor.
     *
     * @param InstallationStatusDeterminator $status
     * @param SelfTest                       $selfTest
     */
    public function __construct(InstallationStatusDeterminator $status, SelfTest $selfTest)
    {
        $this->status = $status;
        $this->selfTest = $selfTest;
    }

    /**
     * @return JsonResponse
     */
    public function statusAction()
    {
        if (!$this->status->isTensideConfigured()) {
            $results = $this->selfTest->perform();

            return $this->getResponse(self::STATUS_NEW, $results);
        }

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse('', 401);
        }

        $results = $this->selfTest->perform();

        return $this->getResponse($this->status->isComplete() ? self::STATUS_OK : self::STATUS_EMPTY, $results);
    }

    /**
     * @param string $status
     * @param array  $results
     *
     * @return JsonResponse
     */
    private function getResponse($status, array $results)
    {
        return $this->getErrorResponse($results) ?: new JsonResponse(
            [
                'status' => $status,
                'selftest' => $this->prepareResults($results),
                'autoconfig' => $this->prepareAutoConfig($this->selfTest->getAutoConfig()),
            ]
        );
    }

    /**
     * @param SelfTestResult[] $results
     *
     * @return bool
     */
    private function hasError(array $results)
    {
        foreach ($results as $result) {
            if (SelfTestResult::STATE_FAIL === $result->getState()) {
                return true;
            }
        }

        return false;
    }

    private function getErrorResponse(array $results)
    {
        if (!$this->hasError($results)) {
            return null;
        }

        return new JsonResponse(
            [
                'status' => self::STATUS_CONFLICT,
                'selftest' => $this->prepareResults($results),
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * @param SelfTestResult[] $results
     *
     * @return array
     */
    private function prepareResults(array $results)
    {
        $data = [];

        foreach ($results as $result) {
            $data[] = [
                'name'    => $result->getTestClass(),
                'state'   => $result->getState(),
                'message' => $result->getMessage(),
                'explain' => $result->getExplain(),
            ];
        }

        return $data;
    }

    /**
     * @param TensideJsonConfig $config
     *
     * @return array
     */
    private function prepareAutoConfig(TensideJsonConfig $config)
    {
        $result = [];

        if ($phpCli = $config->getPhpCliBinary()) {
            $result['php_cli'] = $phpCli;
        }

        if ($phpArguments = $config->getPhpCliArguments()) {
            $result['php_cli_arguments'] = $phpArguments;
        }

        if ($phpEnvironment = $config->getPhpCliEnvironment()) {
            $result['php_cli_environment'] = $phpEnvironment;
        }

        if ($phpEnvironment = $config->isForceToBackgroundEnabled()) {
            $result['php_force_background'] = true;
        }

        $result['php_can_fork'] = $config->isForkingAvailable();

        return $result;
    }
}