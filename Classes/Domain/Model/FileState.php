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
 * @ORM\Table(
 *    indexes={
 * 		@ORM\Index(name="identifierindex",columns={"sourceFileIdentifierHash"}),
 *    }
 * )
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
     * @param $sourceIdentifier
     * @param $sourceFileIdentifier
     * @param $sourceFileIdentifierHash
     * @param \DateTime $sourceFileTime
     */
    public function __construct(PersistentResource $resource, $sourceIdentifier, $sourceFileIdentifier, $sourceFileIdentifierHash, \DateTime $sourceFileTime)
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
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param PersistentResource $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getSourceIdentifier()
    {
        return $this->sourceIdentifier;
    }

    /**
     * @param string $sourceIdentifier
     */
    public function setSourceIdentifier($sourceIdentifier)
    {
        $this->sourceIdentifier = $sourceIdentifier;
    }

    /**
     * @return string
     */
    public function getSourceFileIdentifier()
    {
        return $this->sourceFileIdentifier;
    }

    /**
     * @param string $sourceFileIdentifier
     */
    public function setSourceFileIdentifier($sourceFileIdentifier)
    {
        $this->sourceFileIdentifier = $sourceFileIdentifier;
    }

    /**
     * @return string
     */
    public function getSourceFileIdentifierHash()
    {
        return $this->sourceFileIdentifierHash;
    }

    /**
     * @param string $sourceFileIdentifierHash
     */
    public function setSourceFileIdentifierHash($sourceFileIdentifierHash)
    {
        $this->sourceFileIdentifierHash = $sourceFileIdentifierHash;
    }

    /**
     * @return \DateTime
     */
    public function getSourceFileTime()
    {
        return $this->sourceFileTime;
    }

    /**
     * @param \DateTime $sourceFileTime
     */
    public function setSourceFileTime($sourceFileTime)
    {
        $this->sourceFileTime = $sourceFileTime;
    }

    /**
     * @return \DateTime
     */
    public function getLastSynced()
    {
        return $this->lastSynced;
    }

    /**
     * @param \DateTime $lastSynced
     */
    public function setLastSynced($lastSynced)
    {
        $this->lastSynced = $lastSynced;
    }
}
