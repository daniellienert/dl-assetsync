<?php
declare(strict_types=1);

namespace DL\AssetSync\Tests\Functional;

/*
 * This file is part of the DL.AssetSync package.
 *
 * (c) Daniel Lienert 2019
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use DL\AssetSync\Domain\Model\FileState;
use DL\AssetSync\Domain\Repository\FileStateRepository;
use DL\AssetSync\Source\SourceConfigurationException;
use DL\AssetSync\Synchronization\Synchronizer;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Media\Domain\Model\Asset;
use Neos\Media\Domain\Repository\AssetRepository;

class SyncTest extends FunctionalTestCase
{

    protected $testableSecurityEnabled = true;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var FileStateRepository
     */
    protected $fileStateRepository;

    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    public function setUp()
    {
        parent::setUp();
        $this->assetRepository = $this->objectManager->get(AssetRepository::class);
        $this->fileStateRepository = $this->objectManager->get(FileStateRepository::class);
        $this->synchronizer = $this->objectManager->get(Synchronizer::class);
    }

    /**
     * @test
     * @throws SourceConfigurationException
     * @throws IllegalObjectTypeException
     */
    public function sync()
    {
        $this->synchronizer->syncAssetsBySourceIdentifier('testLocalFileSyncSource');
        $this->persistenceManager->persistAll();
        $this->persistenceManager->clearState();

        $fileStates = $this->fileStateRepository->findAll();

        $this->assertEquals(2, $fileStates->count());

        /** @var FileState $fileState */
        foreach ($fileStates as $fileState) {
            $importedResource = $fileState->getResource();

            /** @var Asset $asset */
            $asset = $this->assetRepository->findOneByResourceSha1($importedResource->getSha1());
            $this->assertInstanceOf(Asset::class, $asset);

            $tags = $asset->getTags();
            $this->assertCount(1, $tags);
            $this->assertEquals('testTag', $tags->current()->getLabel());

            $collections = $asset->getAssetCollections();
            $this->assertCount(1, $collections, 'No collection was assigned');
            $this->assertEquals('testCollection', $collections->current()->getTitle());
        }
    }
}
