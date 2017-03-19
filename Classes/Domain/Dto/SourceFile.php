<?php
namespace DL\AssetSync\Domain\Dto;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2017
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class SourceFile
{
    /**
     * @var string
     */
    protected $fileIdentifier;

    /**
     * @var \DateTime
     */
    protected $fileTime;

    /**
     * @var integer
     */
    protected $fileSize;

    /**
     * SourceFile constructor.
     * @param string $fileIdentifier
     * @param \DateTime $fileTime
     * @param integer $fileSize
     */
    public function __construct($fileIdentifier, $fileTime, $fileSize)
    {
        $this->fileIdentifier = $fileIdentifier;
        $this->fileTime = $fileTime;
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getFileIdentifier()
    {
        return $this->fileIdentifier;
    }

    /**
     * @return string
     */
    public function getFileIdentifierHash() {
        return sha1($this->fileIdentifier);
    }

    /**
     * @return \DateTime
     */
    public function getFileTime()
    {
        return $this->fileTime;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }
}