<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use AppBundle\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"isFileNameUnique"}, groups={"document"})
 */
class Document
{
    use CreationAudit;
    use HasReportTrait;

    /**
     * @param ExecutionContextInterface $context
     */
    public function isFileNameUnique(ExecutionContextInterface $context)
    {
        if (!$this->getFile()) {
            return;
        }

        $fileNames = [];
        foreach ($this->getReport()->getDocuments() as $document) {
            $fileNames[] = $document->getFileName();
        }
        
        if (in_array($this->getFile()->getClientOriginalName(), $fileNames)) {
            $context->addViolationAt('file', 'document.file.errors.alreadyPresent');
        }
    }

    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * // add more validators here if needed
     * http://symfony.com/doc/current/reference/constraints/File.html
     *
     * @Assert\NotBlank(message="Please choose a file", groups={"document"})
     * @Assert\File(
     *     maxSize = "15M",
     *     maxSizeMessage = "document.file.errors.maxSizeMessage",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "document.file.errors.mimeTypesMessage",
     *     groups={"document"}
     * )
     *
     * @var UploadedFile
     */
    private $file;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $fileName;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"document"})
     *
     * @var string
     */
    private $storageReference;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return Document
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStorageReference()
    {
        return $this->storageReference;
    }

    /**
     * @param string $storageReference
     * @return Document
     */
    public function setStorageReference($storageReference)
    {
        $this->storageReference = $storageReference;

        return $this;
    }


    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

}
