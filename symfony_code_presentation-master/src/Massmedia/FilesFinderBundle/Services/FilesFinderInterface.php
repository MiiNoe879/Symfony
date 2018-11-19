<?php

namespace Massmedia\FilesFinderBundle\Services;

/**
 * Class FilesFinderInterface
 * @package Massmedia\FilesFinderBundle\Services
 */
interface FilesFinderInterface
{
    /**
     * Recursively search files in given directory by content
     *
     * @param $dir
     * @param $text
     * @return array
     */
    public function search($dir, $text);

}
