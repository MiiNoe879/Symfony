<?php

namespace Massmedia\FilesFinderBundle\Tests\Controller\Api;

use Bazinga\Bundle\RestExtraBundle\Test\WebTestCase;

class SearchControllerTest extends WebTestCase
{

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesFinder;


    /**
     * Generate files to search
     */
    public function setUp()
    {

        $this->client = static::createClient();
        $this->filesFinder = $this->getMockBuilder('Massmedia\FilesFinderBundle\Services\FilesFinder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSearchFounded()
    {

        $foundedFileName = 'founded file name';
        $this->filesFinder->expects($this->once())->method('search')
            ->with($this->isType('string'), $this->equalTo($foundedFileName))
            ->will($this->returnValue([$foundedFileName]));

        $this->client->getContainer()->set('massmedia.files_finder', $this->filesFinder);

        $this->client->request('GET', '/api/search.json', ['search' => $foundedFileName]);

        $this->assertJsonResponse($this->client->getResponse());

        $response = $this->parseClientJson();

        $this->assertValidResponse($response);

        $this->assertEquals(1, count($response['files']));
    }

    public function testSearchNoFounded()
    {

        $foundedFileName = 'founded file name';
        $this->filesFinder->expects($this->once())->method('search')
            ->with($this->isType('string'), $this->equalTo($foundedFileName))
            ->will($this->returnValue([]));

        $this->client->getContainer()->set('massmedia.files_finder', $this->filesFinder);

        $this->client->request('GET', '/api/search.json', ['search' => $foundedFileName]);

        $this->assertJsonResponse($this->client->getResponse());

        $response = $this->parseClientJson();

        $this->assertValidResponse($response);

        $this->assertEquals(0, count($response['files']));
    }

    protected function assertValidResponse($response) {
        $this->assertArrayHasKey('files', $response);
    }

    protected function parseClientJson() {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}