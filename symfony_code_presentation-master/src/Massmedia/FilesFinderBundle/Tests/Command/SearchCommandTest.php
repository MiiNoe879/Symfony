<?php

namespace Massmedia\FilesFinderBundle\Tests\Controller;

use Massmedia\FilesFinderBundle\Command\SearchCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Container;

class SearchCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var SearchCommand
     */
    private $command;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesFinder;


    /**
     * Generate files to search
     */
    public function setUp()
    {
        $this->container = new Container();

        $this->command = new SearchCommand();

        $this->filesFinder = $this->getMockBuilder('Massmedia\FilesFinderBundle\Services\FilesFinder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSearchResultFounded()
    {
        $foundedFileName = 'founded file name';
        $this->filesFinder->expects($this->once())->method('search')
            ->with($this->isType('string'), $this->equalTo($foundedFileName))
            ->will($this->returnValue([$foundedFileName]));

        $this->container->set('massmedia.files_finder', $this->filesFinder);

        $this->command->setContainer($this->container);

        $output = new BufferedOutput();
        $this->command->run(
            new ArrayInput(['dir' => 'test', 'text' => $foundedFileName]),
            $output
        );

        $this->assertContains($foundedFileName, $output->fetch());
    }

    public function testSearchResultNoFounded()
    {
        $foundedFileName = 'founded file name';
        $this->filesFinder->expects($this->once())->method('search')
            ->with($this->isType('string'), $this->equalTo($foundedFileName))
            ->will($this->returnValue([]));

        $this->container->set('massmedia.files_finder', $this->filesFinder);

        $this->command->setContainer($this->container);

        $output = new BufferedOutput();
        $this->command->run(
            new ArrayInput(['dir' => 'test', 'text' => $foundedFileName]),
            $output
        );

        $this->assertContains('No files founded', $output->fetch());
    }
}
