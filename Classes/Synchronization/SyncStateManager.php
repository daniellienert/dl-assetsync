<?php
declare(strict_types=1);

namespace DL\AssetSync\Synchronization;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2019
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Cache\Frontend\VariableFrontend;

/**
 * @Flow\Scope("singleton")
 */
class SyncStateManager
{
    /**
     * @var VariableFrontend
     */
    protected $syncStateCache;

    /**
     * @param string $sourceIdentifier
     * @return \DateTime|null
     */
    public function getLastSuccessfulSyncOfSource(string $sourceIdentifier): ?\DateTime
    {
        if ($this->syncStateCache->has($sourceIdentifier)) {
            $lastSyncState = $this->syncStateCache->get($sourceIdentifier);
            return $lastSyncState instanceof \DateTime ? $lastSyncState : null;
        }

        return null;
    }

    /**
     * @param string $sourceIdentifier
     * @param \DateTime $dateTime
     * @throws \Neos\Cache\Exception
     */
    public function setLastSuccessfulSyncStateOfSource(string $sourceIdentifier, \DateTime $dateTime): void
    {
        $this->syncStateCache->set($sourceIdentifier, $dateTime);
    }
}
