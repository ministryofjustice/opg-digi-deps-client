<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\StorageInterface;
use Mockery as m;
use Psr\Log\LoggerInterface;

class FileUploaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileUploader
     */
    private $object;

    public function setUp()
    {
        $this->storage = m::mock(StorageInterface::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $this->object = new FileUploader($this->storage, $this->restClient, $this->logger);
    }

    public function testuploadFile()
    {
        $fileName = 'dd_fileuploadertest.pdf';
        $fileContent = 'testcontent';

        $this->storage->shouldReceive('store')->once()->with(matchesPattern('/^dd_doc_1_\d+$/'), $fileContent);
        $this->restClient->shouldReceive('post')->once()->with('/document/report/1', anInstanceOf(Document::class), ['document']);

        $report = m::mock(Report::class, ['getId'=>1]);
        $doc = $this->object->uploadFile($report, $fileContent, $fileName, false); /* @var $document Document */

        $this->assertStringMatchesFormat('dd_doc_1_%d', $doc->getStorageReference());
        $this->assertEquals($fileName, $doc->getFileName());
        $this->assertEquals(false, $doc->isReportPdf());
    }

    public function tearDown()
    {
        m::close();
    }
}
