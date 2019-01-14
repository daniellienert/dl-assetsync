<?php

namespace DL\AssetSync\Source;

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
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility\Environment;

abstract class AbstractSource implements SourceInterface
{
    /**
     * @var string[]
     */
    protected $sourceConfiguration;

    /**
     * @var string[]
     */
    protected $sourceOptions;

    /**
     * @var string[]
     */
    protected $mandatoryConfigurationOptions = [];

    /**
     * @var string[]
     */
    protected $assetTags = [];

    /**
     * @var string
     */
    protected $fileIdentifierPattern = '.*';

    /**
     * @var boolean
     */
    protected $removeAssetsNotInSource = false;

    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $assetCollections = [];

    /**
     * @inheritdoc
     */
    public function __construct(array $sourceConfiguration)
    {
        $this->sourceConfiguration = $sourceConfiguration;
        $this->validateConfigurationOptions($sourceConfiguration['sourceOptions']);
        $this->sourceOptions = $sourceConfiguration['sourceOptions'];

        if (isset($sourceConfiguration['assetTags'])) {
            $this->assetTags = is_array($sourceConfiguration['assetTags']) ? $sourceConfiguration['assetTags'] : [$sourceConfiguration['assetTags']];
        }

        if (isset($sourceConfiguration['assetCollections'])) {
            $this->assetCollections = is_array($sourceConfiguration['assetCollections']) ? $sourceConfiguration['assetCollections'] : [$sourceConfiguration['assetCollections']];
        }

        if (isset($sourceConfiguration['fileIdentifierPattern'])) {
            $this->fileIdentifierPattern = $sourceConfiguration['fileIdentifierPattern'];
        }

        if (isset($sourceConfiguration['removeAssetsNotInSource'])) {
            $this->removeAssetsNotInSource = $sourceConfiguration['removeAssetsNotInSource'];
        }
    }

    /**
     * @inheritdoc
     */
    public function initialize(): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): string
    {
        return $this->sourceConfiguration['sourceIdentifier'];
    }

    /**
     * @return string[]
     */
    public function getAssetTags(): array
    {
        return $this->assetTags;
    }

    /**
     * @return bool
     */
    public function isRemoveAssetsNotInSource(): bool
    {
        return $this->removeAssetsNotInSource;
    }


    /**
     * @inheritdoc
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState): bool
    {
        return $sourceFile->getFileTime() >= $fileState->getSourceFileTime();
    }

    /**
     * @inheritdoc
     */
    public function shutdown(): void
    {
    }

    /**
     * @param string[] $sourceOptions
     * @throws SourceConfigurationException
     */
    protected function validateConfigurationOptions(array $sourceOptions): void
    {
        foreach ($this->mandatoryConfigurationOptions as $configurationOption) {
            if (!isset($sourceOptions[$configurationOption]) || empty($sourceOptions[$configurationOption])) {
                throw new SourceConfigurationException(sprintf('Error while validating sourceConfiguration for SynchronizationSource %s, mandatory option %s is missing.', get_class($this), $configurationOption), 1489392744);
            }
        }
    }

    /**
     * @return string[]
     */
    public function getAssetCollections(): array
    {
        return $this->assetCollections;
    }
}
