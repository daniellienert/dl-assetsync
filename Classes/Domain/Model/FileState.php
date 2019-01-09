<?php
namespace DL\AssetSync\Domain\Model;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2017
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * @Flow\Entity
 */
class FileState
{
    /**
     * @var PersistentResource
     * @ORM\OneToOne(orphanRemoval=true, cascade={"all"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $resource;

    /**
     * @var string
     */
    protected $sourceIdentifier;

    /**
     * @var string
     */
    protected $sourceFileIdentifier;

    /**
     * @var string
     * @ORM\Column(length=40)
     */
    protected $sourceFileIdentifierHash;

    /**
     * @var \DateTime
     */
    protected $sourceFileTime;

    /**
     * @var \DateTime
     */
    protected $lastSynced;

    /**
     * FileState constructor.
     * @param PersistentResource $resource
     * @param string $sourceIdentifier
     * @param string $sourceFileIdentifier
     * @param string $sourceFileIdentifierHash
     * @param \DateTime $sourceFileTime
     * @throws \Exception
     */
    public function __construct(PersistentResource $resource, string $sourceIdentifier, string $sourceFileIdentifier, string $sourceFileIdentifierHash, \DateTime $sourceFileTime)
    {
        $this->resource = $resource;
        $this->sourceIdentifier = $sourceIdentifier;
        $this->sourceFileIdentifier = $sourceFileIdentifier;
        $this->sourceFileIdentifierHash = $sourceFileIdentifierHash;
        $this->sourceFileTime = $sourceFileTime;
        $this->lastSynced = new \DateTime();
    }

    /**
     * @return PersistentResource
     */
    public function getResource(): PersistentResource
    {
        return $this->resource;
    }

    /**
     * @param PersistentResource $resource
     */
    public function setResource(PersistentResource $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getSourceIdentifier(): string
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param string $sourceIdentifier
     */
    public function setSourceIdentifier(string $sourceIdentifier): void
    {
        $this->sourceIdentifier = $sourceIdentifier;
    }

    /**
     * @return string
     */
    public function getSourceFileIdentifier(): string
    {
        return $this->sourceFileIdentifier;
    }

    /**
     * @param string $sourceFileIdentifier
     */
    public function setSourceFileIdentifier(string $sourceFileIdentifier): void
    {
        $this->sourceFileIdentifier = $sourceFileIdentifier;
    }

    /**
     * @return string
     */
    public function getSourceFileIdentifierHash(): string
    {
        return $this->sourceFileIdentifierHash;
    }

    /**
     * @param string $sourceFileIdentifierHash
     */
    public function setSourceFileIdentifierHash(string $sourceFileIdentifierHash): void
    {
        $this->sourceFileIdentifierHash = $sourceFileIdentifierHash;
    }

    /**
     * @return \DateTime
     */
    public function getSourceFileTime(): \DateTime
    {
        return $this->sourceFileTime;
    }

    /**
     * @param \DateTime $sourceFileTime
     */
    public function setSourceFileTime(\DateTime $sourceFileTime): void
    {
        $this->sourceFileTime = $sourceFileTime;
    }

    /**
     * @return \DateTime
     */
    public function getLastSynced(): \DateTime
    {
        return $this->lastSynced;
    }

    /**
     * @param \DateTime $lastSynced
     */
    public function setLastSynced(\DateTime $lastSynced): void
    {
        $this->lastSynced = $lastSynced;
    }
}
