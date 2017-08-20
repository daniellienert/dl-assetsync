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
     * @Flow\InjectConfiguration(path="sourceConfiguration")
     * @var array
     */
    protected $sourceConfiguration;

    /**
     * Synchronize a single defined source
     *
     * @param string $sourceIdentifier The identifier of the source to synchronize.
     */
    public function syncCommand($sourceIdentifier)
    {
        $this->synchronizeSource($sourceIdentifier);
    }

    /**
     * Synchronize all defined sources
     */
    public function syncAllCommand()
    {
        $this->outputLine('Syncing all available sources');
        foreach(array_keys($this->sourceConfiguration) as $sourceIdentifier) {
            $this->synchronizeSource($sourceIdentifier);
        }
    }

    /**
     * @param $sourceIdentifier
     */
    protected function synchronizeSource($sourceIdentifier) {
        $this->outputLine(sprintf('Syncing source <b>%s</b>', $sourceIdentifier));
        try {
            $this->synchronizer->syncAssetsBySourceIdentifier($sourceIdentifier);
        } catch (\Exception $exception) {
            $this->outputLine(sprintf("<error>Synchronization failed:\n%s (%s)</error>", $exception->getMessage(), $exception->getCode()));
        }
    }
}
