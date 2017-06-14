<?php
namespace DL\AssetSync\Command;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2017
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSync\Synchronization\Synchronizer;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class AssetSyncCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * @param string $sourceIdentifier The identifier of the source to synchronize.
     */
    public function syncCommand($sourceIdentifier)
    {
        $this->outputLine(sprintf('Syncing source <b>%s</b>', $sourceIdentifier));
        try {
            $this->synchronizer->syncAssetsBySourceIdentifier($sourceIdentifier);
        } catch (\Exception $exception) {
            $this->outputLine(sprintf("<error>Synchronization failed:\n%s (%s)</error>", $exception->getMessage(), $exception->getCode()));
        }
    }
}
