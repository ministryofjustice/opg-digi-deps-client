<?php

namespace AppBundle\Service\File\Types;

use AppBundle\Service\File\Checker\FileCheckerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadableFile implements UploadableFileInterface
{
    /**
     * @var FileCheckerInterface[]
     */
    protected $fileCheckers;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var UploadedFile $file
     */
    protected $uploadedFile;

    /**
     * FileUploader constructor.
     */
    public function __construct(
        FileCheckerInterface $virusChecker,
        FileCheckerInterface $fileChecker,
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
        $this->fileCheckers = [$virusChecker, $fileChecker];
    }

    /**
     * @return FileCheckerInterface[]
     */
    public function getFileCheckers()
    {
        return $this->fileCheckers;
    }

    /**
     * @param FileCheckerInterface[] $fileCheckers
     *
     * @return $this
     */
    public function setFileCheckers($fileCheckers)
    {
        $this->fileCheckers = $fileCheckers;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return $this
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * Checks a file by calling configured file checkers for that file type
     *
     * @throws \Exception
     */
    public function checkFile()
    {
        $fileBody = file_get_contents($this->getUploadedFile()->getPath());

        foreach ($this->getFileCheckers() as $fc)
        {
            $this->getLogger()->debug('Calling File checker: ' . get_class($fc) );

            $fc->checkFile($fileBody);
        }
    }
}
