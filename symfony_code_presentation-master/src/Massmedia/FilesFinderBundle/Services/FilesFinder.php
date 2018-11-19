<?php

namespace Massmedia\FilesFinderBundle\Services;

/**
 * Find file by content
 *
 * Class FilesFinder
 * @package Massmedia\FilesFinderBundle\Services
 */
class FilesFinder
{

    /**
     * @var FilesFinderInterface
     */
    protected $finder;

    /**
     * @inheritdoc
     */
    public function search($dir, $text)
    {
        return $this->finder->search($dir, $text);
    }

    /**
     * @param FilesFinderInterface $finder
     */
    public function setFinder(FilesFinderInterface $finder)
    {
        $this->finder = $finder;
    }

}
