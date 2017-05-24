<?php

/*
 * This file is part of Contao Manager.
 *
 * Copyright (c) 2016-2017 Contao Association
 *
 * @license LGPL-3.0+
 */

namespace Contao\ManagerApi\Controller;

use Contao\ManagerApi\ApiKernel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller to handle log files.
 */
class LogController extends Controller
{
    /**
     * @var ApiKernel
     */
    private $kernel;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor.
     *
     * @param ApiKernel       $kernel
     * @param Filesystem|null $filesystem
     */
    public function __construct(ApiKernel $kernel, Filesystem $filesystem = null)
    {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Returns a list of users in the configuration file.
     *
     * @return Response
     */
    public function listFiles()
    {
        /** @var Finder $finder */
        $finder = Finder::create()
            ->depth(0)
            ->files()
            ->ignoreDotFiles(true)
            ->name('*.log')
            ->in($this->kernel->getContaoDir().'/var/logs')
        ;

        $files = [];

        foreach ($finder as $file) {
            $files[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'mtime' => \DateTime::createFromFormat('U', $file->getMTime())->format(\DateTime::ATOM),
            ];
        }

        return new JsonResponse($files);
    }

    /**
     * Returns data of a log file.
     *
     * @param string $filename
     *
     * @return Response
     */
    public function retrieveFile($filename)
    {
        $file = $this->getFile($filename);

        return new JsonResponse(
            [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'mtime' => \DateTime::createFromFormat('U', $file->getMTime())->format(\DateTime::ATOM),
                'content' => $file->getContents(),
            ]
        );
    }

    /**
     * Deletes a log file.
     *
     * @param string $filename
     *
     * @return Response
     */
    public function deleteFile($filename)
    {
        $file = $this->getFile($filename);

        $this->filesystem->remove($file->getPathname());

        return new JsonResponse(
            [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'mtime' => \DateTime::createFromFormat('U', $file->getMTime())->format(\DateTime::ATOM),
            ]
        );
    }

    /**
     * Gets absolute path for filename and checks for security and if file exists.
     *
     * @param string $filename
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return SplFileInfo
     */
    private function getFile($filename)
    {
        if (false !== strpos($filename, '/')) {
            throw new BadRequestHttpException(sprintf('"%s" is not a valid file name.', $filename));
        }

        $path = $this->kernel->getContaoDir().'/var/logs/'.$filename;

        if (!is_file($path)) {
            throw new NotFoundHttpException(sprintf('Log file "%s" does not exist.', $filename));
        }

        return new SplFileInfo($path, '', $filename);
    }
}
