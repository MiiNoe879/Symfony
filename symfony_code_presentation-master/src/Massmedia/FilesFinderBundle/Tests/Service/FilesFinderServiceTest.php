<?php

namespace Massmedia\FilesFinderBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class FilesFinderServiceTest extends KernelTestCase
{
    /**
     * @var \Massmedia\FilesFinderBundle\Services\FilesFinder
     */
    private $filesFinder;

    /**
     * Dir to store temporary files
     *
     * @var
     */
    private $tmpDir;

    /**
     * Array of phrases to search in files
     *
     * @var array
     */
    private $phrasesToSearch = [];

    /**
     * Count of files to test search
     *
     * @var array
     */
    private $searchFilesCount = 5;

    /**
     * Count of files to test search
     *
     * @var string
     */
    private $testPhrase = 'test phrase';

    /**
     * Generate files to search
     */
    public function setUp()
    {
        self::bootKernel();

        $this->tmpDir = __DIR__ . '/../tmp';

        $container = self::$kernel->getContainer();
        $this->filesFinder = $container->get('massmedia.files_finder');

        // Generate test files. Each file contain unique text phrase
        for ($i = 1; $i <= $this->searchFilesCount; $i++) {
            $phrase = $this->getPhrase($i);
            $this->phrasesToSearch[] = $phrase;
            file_put_contents($this->tmpDir . '/' . uniqid() . '.txt', "Content $phrase another content");
        }
    }

    /**
     * Clear files to search
     */
    public function tearDown()
    {
        $files = glob($this->tmpDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testFindFiles()
    {
        // Count files contains test phrase
        $filesCount = count($this->filesFinder->search($this->tmpDir, $this->getPhrase()));
        $this->assertEquals($this->searchFilesCount, $filesCount);

        // Check each files
        for ($i = 1; $i <= $this->searchFilesCount; $i++) {
            $filesCount = count($this->filesFinder->search($this->tmpDir, $this->getPhrase($i)));
            $this->assertEquals(1, $filesCount);
        }

        // Check file without occurrences
        $filesCount = count($this->filesFinder->search($this->tmpDir, 'phrase which not in files'));
        $this->assertEquals(0, $filesCount);
    }

    public function testFindInNotExistsDir()
    {
        $this->setExpectedException('RuntimeException');

        $files = $this->filesFinder->search($this->tmpDir . '_wrong', $this->getPhrase());
    }

    /**
     * Get test phrase
     *
     * @param bool|false $number
     * @return string
     */
    private function getPhrase($number = false)
    {
        return $number ? "$this->testPhrase $number" : $this->testPhrase;
    }

}
