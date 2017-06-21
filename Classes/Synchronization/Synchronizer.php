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

use DL\AssetSync\Domain\Model\FileState;
use DL\AssetSync\Domain\Dto\SourceFile;
use DL\AssetSync\Domain\Repository\FileStateRepository;
use DL\AssetSync\Source\SourceInterface;
use DL\AssetSync\Source\SourceFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Model\Tag;
use Neos\Media\Domain\Repository\AssetRepository;
use Neos\Media\Domain\Repository\TagRepository;
use Neos\Media\Domain\Service\AssetService;
use Neos\Media\Domain\Strategy\AssetModelMappingStrategyInterface;

class Synchronizer
{

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
     * @var array
     */
    protected $tagFirstLevelCache = [];

    /**
     * @var array
     */
    protected $syncCounter = [
        'skip' => 0,
        'new' => 0,
        'update' => 0
    ];

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $logger;

    /**
     * @param string $sourceIdentifier
     */
    public function syncAssetsBySourceIdentifier($sourceIdentifier)
    {
        $this->source = $this->sourceFactory->createSource($sourceIdentifier);

        $this->logger->log('Generating file list for source ' . $sourceIdentifier);
        $sourceFileCollection = $this->source->generateSourceFileCollection();
        $this->logger->log(sprintf('Found %s files to consider.', $sourceFileCollection->count()));

        foreach ($sourceFileCollection as $sourceFile) {
            $this->syncAsset($sourceFile);
        }

        $this->logger->log(sprintf('Synchronization of %s finished. Added %s new assets, updated %s assets, skipped %s assets.', $sourceIdentifier, $this->syncCounter['new'], $this->syncCounter['update'], $this->syncCounter['skip']));
        $this->source->shutdown();
    }

    /**
     * @param SourceFile $sourceFile
     * @return FileState
     */
    protected function syncAsset(SourceFile $sourceFile)
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
     */
    protected function syncNew(SourceFile $sourceFile)
    {
        $this->logger->log(sprintf('Syncing new file %s from source %s', $sourceFile->getFileIdentifier(), $this->source->getIdentifier()));

        $persistentResource = $this->resourceManager->importResource($this->source->getPathToLocalFile($sourceFile));

        $targetType = $this->assetModelMappingStrategy->map($persistentResource);
        $asset = new $targetType($persistentResource);
        $this->addTags($asset);
        $this->assetService->getRepository($asset)->add($asset);

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
     */
    protected function syncUpdate(SourceFile $sourceFile, FileState $fileState)
    {
        $this->logger->log(sprintf('Updating existing file %s from source %s', $sourceFile->getFileIdentifier(), $this->source->getIdentifier()));
        $resourceToBeReplaced = $fileState->getResource();

        $asset = $this->assetRepository->findOneByResourceSha1($resourceToBeReplaced->getSha1());

        $newPersistentResource = $this->resourceManager->importResource($this->source->getPathToLocalFile($sourceFile));

        $this->assetService->replaceAssetResource($asset, $newPersistentResource);

        $this->resourceManager->deleteResource($resourceToBeReplaced);
        $fileState->setResource($newPersistentResource);

        $this->fileStateRepository->update($fileState);
        return $fileState;
    }

    /**
     * @param Asset $asset
     */
    protected function addTags(Asset $asset)
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
     */
    protected function getOrCreateTag($label)
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
}
