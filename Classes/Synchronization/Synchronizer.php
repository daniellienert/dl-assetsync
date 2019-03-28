<?php
namespace DL\AssetSync\Synchronization;

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
use DL\AssetSync\Domain\Model\FileState;
use DL\AssetSync\Domain\Dto\SourceFile;
use DL\AssetSync\Domain\Repository\FileStateRepository;
use DL\AssetSync\Source\SourceConfigurationException;
use DL\AssetSync\Source\SourceInterface;
use DL\AssetSync\Source\SourceFactory;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\Tag;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\TagRepository;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Strategy\AssetModelMappingStrategyInterface;

class Synchronizer
{

    const BATCH_SIZE = 1000;

    /**
     * @Flow\Inject
     * @var SourceFactory
     */
    protected $sourceFactory;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var FileStateRepository
     */
    protected $fileStateRepository;

    /**
     * @Flow\Inject
     * @var AssetModelMappingStrategyInterface
     */
    protected $assetModelMappingStrategy;

    /**
     * @Flow\Inject
     * @var AssetService
     */
    protected $assetService;

    /**
     * @Flow\Inject
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @Flow\Inject
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var Tag[]
     */
    protected $tagFirstLevelCache = [];

    /**
     * @var int[]
     */
    protected $syncCounter = [];

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $logger;

    /**
     * @Flow\Inject
     * @var AssetCollectionRepository
     */
    protected $assetCollectionRepository;

    /**
     * @var AssetCollection[]
     */
    protected $assetCollectionFirstLevelCache = [];

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @param string $sourceIdentifier
     * @throws SourceConfigurationException
     * @throws IllegalObjectTypeException
     */
    public function syncAssetsBySourceIdentifier(string $sourceIdentifier): void
    {
        $syncedFileCount = 0;
        $batchTimeStart = microtime(true);

        $this->reset();
        $this->source = $this->sourceFactory->createSource($sourceIdentifier);

        $this->logger->log('Generating file list for source ' . $sourceIdentifier);
        $sourceFileCollection = $this->source->generateSourceFileCollection();
        $this->logger->log(sprintf('Found %s files to consider.', $sourceFileCollection->count()));

        $persistAndLog = function () use (&$batchTimeStart) {
            $timeUsedInMilliSeconds = (microtime(true) - $batchTimeStart);
            $this->logger->log(sprintf('Imported %s assets in %s seconds (%s assets per second)', self::BATCH_SIZE, number_format($timeUsedInMilliSeconds * 1000, 2), number_format(self::BATCH_SIZE / ($timeUsedInMilliSeconds * 1000), 2)), LOG_INFO);

            $this->persistenceManager->persistAll();
            $this->persistenceManager->clearState();
            $this->tagFirstLevelCache = [];
            $this->assetCollectionFirstLevelCache = [];
        };

        foreach ($sourceFileCollection as $sourceFile) {

            try {
                $this->syncAsset($sourceFile);
            } catch (\Exception $exception) {
                $this->logger->log(sprintf('Exception %s (%s) while trying to import asset %s', $exception->getMessage(), $exception->getCode(), $sourceFile->getFileIdentifier()), LOG_WARNING);
            }

            $syncedFileCount++;

            if ($syncedFileCount % self::BATCH_SIZE === 0) {
                $persistAndLog();
            }

            $batchTimeStart = microtime(true);
        }

        $persistAndLog();

        if ($this->source->isRemoveAssetsNotInSource() === true) {
            $this->removeDeletedInSource($sourceIdentifier, $sourceFileCollection);
        }

        $this->logger->log(sprintf('Synchronization of %s finished. Added %s new assets, updated %s assets, removed %s assets, skipped %s assets.', $sourceIdentifier, $this->syncCounter['new'], $this->syncCounter['update'], $this->syncCounter['removed'], $this->syncCounter['skip']));
        $this->source->shutdown();
    }

    /**
     * @param SourceFile $sourceFile
     * @return FileState|null
     * @throws IllegalObjectTypeException
     */
    protected function syncAsset(SourceFile $sourceFile): ?FileState
    {
        $fileState = $this->fileStateRepository->findOneBySourceFileIdentifierHash($sourceFile->getFileIdentifierHash());
        $this->logger->log(sprintf('Synchronizing file with identifier "%s".', $sourceFile->getFileIdentifier()), LOG_DEBUG);

        if (!$fileState) {
            $this->syncNew($sourceFile);
            $this->syncCounter['new']++;
            return $fileState;
        }

        if ($fileState && !$this->source->isSyncNecessary($sourceFile, $fileState)) {
            $this->syncCounter['skip']++;
            return $fileState;
        }

        $this->syncUpdate($sourceFile, $fileState);
        $this->syncCounter['update']++;

        return $fileState;
    }

    /**
     * @param SourceFile $sourceFile
     * @return FileState
     * @throws IllegalObjectTypeException
     * @throws \Exception
     */
    protected function syncNew(SourceFile $sourceFile): ?FileState
    {
        $this->logger->log(sprintf('Adding new file %s from source %s', $sourceFile->getFileIdentifier(), $this->source->getIdentifier()));

        try {

            $persistentResource = $this->resourceManager->importResource($this->source->getPathToLocalFile($sourceFile));

            $targetType = $this->assetModelMappingStrategy->map($persistentResource);
            $asset = new $targetType($persistentResource);
            $this->assetService->getRepository($asset)->add($asset);

        } catch (\Exception $exception) {
            $this->logger->log(sprintf('Import of file %s was NOT successful. Exception: %s (%s).', $sourceFile->getFileIdentifier(), $exception->getMessage(), $exception->getCode()), LOG_ERR);
            return null;
        }

        $this->addTags($asset);
        $this->addAssetToAssetCollections($asset);

        $fileState = new FileState(
            $persistentResource,
            $this->source->getIdentifier(),
            $sourceFile->getFileIdentifier(),
            $sourceFile->getFileIdentifierHash(),
            $sourceFile->getFileTime()
        );

        $this->fileStateRepository->add($fileState);

        return $fileState;
    }

    /**
     * @param SourceFile $sourceFile
     * @param FileState $fileState
     * @return FileState
     * @throws IllegalObjectTypeException
     */
    protected function syncUpdate(SourceFile $sourceFile, FileState $fileState): ?FileState
    {
        $this->logger->log(sprintf('Updating existing file %s from source %s', $sourceFile->getFileIdentifier(), $this->source->getIdentifier()));
        $resourceToBeReplaced = $fileState->getResource();

        /** @var Asset $asset */
        $asset = $this->assetRepository->findOneByResourceSha1($resourceToBeReplaced->getSha1());

        try {
            $newPersistentResource = $this->resourceManager->importResource($this->source->getPathToLocalFile($sourceFile));
            $this->assetService->replaceAssetResource($asset, $newPersistentResource);
            $this->addTags($asset);
            $this->addAssetToAssetCollections($asset);
        } catch (\Exception $exception) {
            $this->logger->log(sprintf('Import of replacement file %s was NOT successful. Exception: %s (%s).', $sourceFile->getFileIdentifier(), $exception->getMessage(), $exception->getCode()), LOG_ERR);
            return null;
        }

        $this->resourceManager->deleteResource($resourceToBeReplaced);
        $fileState->setResource($newPersistentResource);

        $this->fileStateRepository->update($fileState);
        return $fileState;
    }

    /**
     * @param Asset $asset
     * @throws IllegalObjectTypeException
     */
    protected function addTags(Asset $asset): void
    {
        foreach ($this->source->getAssetTags() as $tagLabel) {
            if (trim($tagLabel) === '') {
                continue;
            }

            $tag = $this->getOrCreateTag($tagLabel);

            if ($asset->getTags()->contains($tag)) {
                continue;
            }

            $asset->addTag($tag);
        }
    }

    /**
     * @param string $label
     *
     * @return Tag
     * @throws IllegalObjectTypeException
     */
    protected function getOrCreateTag(string $label): Tag
    {
        $label = trim($label);

        if (isset($this->tagFirstLevelCache[$label])) {
            return $this->tagFirstLevelCache[$label];
        }

        $tag = $this->tagRepository->findOneByLabel($label);

        if ($tag === null) {
            $tag = new Tag($label);
            $this->tagRepository->add($tag);
        }

        $this->tagFirstLevelCache[$label] = $tag;

        return $tag;
    }

    /**
     * @param string $sourceIdentifier
     * @param SourceFileCollection $sourceFileCollection
     * @throws IllegalObjectTypeException
     */
    protected function removeDeletedInSource(string $sourceIdentifier, SourceFileCollection $sourceFileCollection): void
    {
        $this->logger->log('Removing previously synced files, which are not in the source anymore.');
        $previouslySyncedFiles = $this->fileStateRepository->findBySourceIdentifier($sourceIdentifier);

        /** @var FileState $fileState */
        foreach ($previouslySyncedFiles as $fileState) {
            if ($sourceFileCollection->getSourceFileByFileIdentifierHash($fileState->getSourceFileIdentifierHash()) === null) {
                /** @var Asset $asset */
                $asset = $this->assetRepository->findOneByResource($fileState->getResource());

                if ($asset->isInUse() === true) {
                    $this->logger->log(sprintf('Cannot remove asset %s which was previously imported from %s, because it is still used %s %s.', $asset->getIdentifier(), $fileState->getSourceFileIdentifier(), $asset->getUsageCount(), ($asset->getUsageCount() === 1 ? 'time' : 'times')));
                    continue;
                }

                try {
                    $this->assetRepository->remove($asset);
                } catch (\Exception $exception) {
                    $this->logger->log(sprintf('Unable to remove asset %s Exception: %s (%s).', $asset->getIdentifier(), $exception->getMessage(), $exception->getCode()), LOG_ERR);
                    continue;
                }

                $this->fileStateRepository->remove($fileState);
                $this->logger->log(sprintf('Removing asset %s which doesn\'t exist in the source anymore.', $asset->getIdentifier()));
                $this->syncCounter['removed']++;
            }
        }
    }

    protected function reset(): void
    {
        $this->syncCounter = [
            'skip' => 0,
            'new' => 0,
            'update' => 0,
            'removed' => 0,
        ];
    }

    /**
     * @param Asset $asset
     * @throws IllegalObjectTypeException
     */
    protected function addAssetToAssetCollections(Asset $asset): void
    {
        foreach ($this->source->getAssetCollections() as $assetCollectionName) {
            if (trim($assetCollectionName) === '') {
                continue;
            }

            $assetCollection = $this->getOrCreateAssetCollection($assetCollectionName);

            if ($asset->getAssetCollections()->contains($assetCollection)) {
                continue;
            }

            $assetCollection->addAsset($asset);
            $this->assetCollectionRepository->update($assetCollection);
        }
    }

    /**
     * @param string $assetCollectionName
     * @return AssetCollection
     * @throws IllegalObjectTypeException
     */
    protected function getOrCreateAssetCollection(string $assetCollectionName): AssetCollection
    {


        $assetCollectionName = trim($assetCollectionName);
        if (isset($this->assetCollectionFirstLevelCache[$assetCollectionName])) {
            return $this->assetCollectionFirstLevelCache[$assetCollectionName];
        }

        $assetCollection = $this->assetCollectionRepository->findOneByTitle($assetCollectionName);

        if (!$assetCollection instanceof AssetCollection) {
            $assetCollection = new AssetCollection($assetCollectionName);
            $this->assetCollectionRepository->add($assetCollection);
        }

        $this->assetCollectionFirstLevelCache[$assetCollectionName] = $assetCollection;

        return $assetCollection;
    }
}
