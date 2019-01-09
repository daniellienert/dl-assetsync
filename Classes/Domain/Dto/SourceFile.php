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
     * @param string $fileIdentifier
     * @param \DateTime $fileTime
     * @param int $fileSize
     */
    public function __construct(string $fileIdentifier, \DateTime $fileTime, int $fileSize)
    {
        $this->fileIdentifier = $fileIdentifier;
        $this->fileTime = $fileTime;
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getFileIdentifier(): string
    {
        return $this->fileIdentifier;
    }

    /**
     * @return string
     */
    public function getFileIdentifierHash(): string
    {
        return sha1($this->fileIdentifier);
    }

    /**
     * @return \DateTime
     */
    public function getFileTime(): \DateTime
    {
        return $this->fileTime;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }
}
