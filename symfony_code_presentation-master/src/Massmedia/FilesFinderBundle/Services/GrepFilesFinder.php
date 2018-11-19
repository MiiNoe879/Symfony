<?php

namespace Massmedia\FilesFinderBundle\Services;

/**
 * Find files using Linux and OSX grep
 * OSX - http://www.gnu.org/software/grep
 * Linux - https://developer.apple.com/library/mac/documentation/Darwin/Reference/ManPages/man1/grep.1.html
 *
 * Class GrepFilesFinder
 * @package Massmedia\FilesFinderBundle\Services
 */
class GrepFilesFinder implements FilesFinderInterface
{
    /**
     * @inheritdoc
     */
    public function search($dir, $text)
    {
        $text = addcslashes($text, '\'');
        $dir = addcslashes($dir, '\'');

        // Execute "grep" command
        exec("grep -rl '$text' '$dir'", $results, $returnVar);

        if ($returnVar > 1) {
            throw new \RuntimeException("Error find files in directory $dir using grep");
        }

        // Prepare results
        $results = array_filter($results);
        foreach ($results as $key => $line) {
            $results[$key] = realpath($line);
        }

        return $results;
    }

}
