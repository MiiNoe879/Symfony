<?php

namespace Massmedia\FilesFinderBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class FilesFinderConfigurator
 * @package Massmedia\FilesFinderBundle\Services
 */
class FilesFinderConfigurator
{
    /**
     * Configure files finder service
     *
     * @param FilesFinder $filesFinder
     */
    public function configure(FilesFinder $filesFinder)
    {
        switch (true) {
            // Case windows
            case stristr(PHP_OS, 'WIN'):
                $finder = new FindstrFilesFinder();
                break;
            // Case Linux or OSX
            case stristr(PHP_OS, 'DAR'):
            case stristr(PHP_OS, 'LINUX'):
                $finder = new GrepFilesFinder();
                break;
            default:
                throw new \RuntimeException('Unknown operating system');
        }

        $filesFinder->setFinder($finder);
    }

}
