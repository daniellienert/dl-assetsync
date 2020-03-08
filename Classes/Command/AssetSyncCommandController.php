<?php
declare(strict_types=1);

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
use Neos\Flow\Cli\Exception\StopCommandException;
use Neos\Flow\Log\ThrowableStorageInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Psr\Log\LoggerInterface;

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
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\InjectConfiguration(path="sourceConfiguration")
     * @var string[]
     */
    protected $sourceConfiguration;

    /**
     * @Flow\Inject
     * @var ThrowableStorageInterface
     */
    protected $throwableStorage;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Synchronize a single defined source
     *
     * @param string $sourceIdentifier The identifier of the source to synchronize.
     * @throws StopActionException
     * @throws StopCommandException
     */
    public function syncCommand(string $sourceIdentifier): void
    {
        $this->synchronizeSource($sourceIdentifier);
    }

    /**
     * Synchronize all defined sources
     * @throws StopActionException
     * @throws StopCommandException
     */
    public function syncAllCommand(): void
    {
        $this->outputLine('Syncing all available sources');
        foreach (array_keys($this->sourceConfiguration) as $sourceIdentifier) {
            $this->synchronizeSource($sourceIdentifier);
        }
    }

    /**
     * @param $sourceIdentifier
     * @throws StopActionException
     * @throws StopCommandException
     */
    protected function synchronizeSource(string $sourceIdentifier): void
    {
        if(!isset($this->sourceConfiguration[$sourceIdentifier]) || !is_array($this->sourceConfiguration[$sourceIdentifier])) {
            $this->outputLine('SourceIdentifier "%s" is not configured', [$sourceIdentifier]);
            $this->quit(1);
        }

        $this->outputLine(sprintf('Syncing source <b>%s</b>', $sourceIdentifier));

        try {
            $this->synchronizer->syncAssetsBySourceIdentifier($sourceIdentifier);
            $this->persistenceManager->persistAll();
        } catch (\Exception $exception) {
            $message = $this->throwableStorage->logThrowable($exception);
            $this->logger->error($message, LogEnvironment::fromMethodName(__METHOD__));
            $this->outputLine(sprintf("<error>Synchronization failed:\n%s (%s)</error>", $message, $exception->getCode()));
        }
    }
}
