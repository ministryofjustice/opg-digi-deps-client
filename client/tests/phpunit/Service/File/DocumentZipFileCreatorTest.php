<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\File\Storage\StorageInterface;
use Mockery as m;

class DocumentZipFileCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentsZipFileCreator
     */
    private $object;

    public function setUp()
    {
        $this->storage = m::mock(StorageInterface::class);
        $this->reportSubmission = m::mock(ReportSubmission::class, [
            'getDocuments' => [],
        ]);

        $this->object = new DocumentsZipFileCreator($this->reportSubmission, $this->storage);
    }

    public function testcreateZipFileNoDocuments()
    {
        $this->reportSubmission->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(true);
        $this->reportSubmission->shouldReceive('getDocuments')->andReturn([]);

        $this->setExpectedException('RuntimeException');
        $this->object->createZipFile();
    }

    public function testcreateZipFileNotDownloadable()
    {
        $this->reportSubmission->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(false);

        $this->setExpectedException('RuntimeException', DocumentsZipFileCreator::MSG_NOT_DOWNLOADABLE);
        $this->object->createZipFile();
    }

    public function testcreateZipFile()
    {
        $zipFileName = 'Report_12345678_2017_2018.zip';
        $doc1 = m::mock(Document::class, [
            'getId' => 1,
            'getFileName' => 'file1.pdf',
            'getStorageReference' => 'r1'
        ]);
        $doc2 = m::mock(Document::class, [
            'getId' => 2,
            'getFileName' => 'file2.pdf',
            'getStorageReference' => 'r2'
        ]);
        $this->storage->shouldReceive('retrieve')->with('r1')->andReturn('doc1-content');
        $this->storage->shouldReceive('retrieve')->with('r2')->andReturn('doc2-content');

        $this->reportSubmission
            ->shouldReceive('getZipName')->andReturn($zipFileName)
            ->shouldReceive('getDocuments')->andReturn([$doc1, $doc2])
            ->shouldReceive('isDownloadable')->once()->withNoArgs()->andReturn(true)
        ;

        $fileName = $this->object->createZipFile();

        $this->assertContains($zipFileName, $fileName);
        $this->assertEquals('doc1-content', exec("unzip -c $fileName file1.pdf"));
        $this->assertEquals('doc2-content', exec("unzip -c $fileName file2.pdf"));

        $this->object->cleanUp();

        $this->assertFalse(file_exists($fileName));
    }

    public function tearDown()
    {
        m::close();
    }
}
