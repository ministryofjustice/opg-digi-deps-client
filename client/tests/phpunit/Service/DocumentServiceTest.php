<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\S3Storage;
use Mockery\Exception;
use Mockery as m;
use Psr\Log\LoggerInterface;

class DocumentServiceTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var DocumentService
     */
    protected $object;

    private $s3Storage;

    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->s3Storage = m::mock(S3Storage::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->object = new DocumentService($this->s3Storage, $this->restClient, $this->logger);
    }


    public static function cleanUpDataProvider()
    {
        return [
            [0], // s3 failures NOT ignored -> hard delete gets called
            [1], // s3 failures ignored -> hard delete gets called
        ];
    }

    public function testRemoveDocumentFromS3()
    {
        $docId = 1;
        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->shouldReceive('removeFromS3')->once()->with('r1')->andReturn([]);

        $this->restClient->shouldReceive('delete')
            ->once()
            ->with('document/' . $docId)
            ->andReturn(true);

        $this->object->removeDocumentFromS3($document);

    }

    public function testRemoveDocumentWithS3Failure()
    {
        $docId = 1;

        $document = new Document();
        $document->setId($docId);
        $document->setStorageReference('r1');

        $this->s3Storage
            ->shouldReceive('removeFromS3')->once()->with('r1')->andThrow(Exception::class);

        $this->restClient->shouldReceive('apiCall')->never()->with('DELETE', 'document/1', null, 'array', [], false);

        $this->setExpectedException('Exception');

        $this->object->removeDocumentFromS3($document);

    }

    public function tearDown()
    {
        m::close();
    }
}
