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
     * @var array
     */
    protected $sourceConfiguration;

    /**
     * @var array
     */
    protected $sourceOptions;

    /**
     * @var array
     */
    protected $mandatoryConfigurationOptions = [];

    /**
     * @var array
     */
    protected $assetTags = [];

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
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier()
    {
        return $this->sourceConfiguration['sourceIdentifier'];
    }

    /**
     * @return array
     */
    public function getAssetTags()
    {
        return $this->assetTags;
    }

    /**
     * @inheritdoc
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState)
    {
        return $sourceFile->getFileTime() >= $fileState->getSourceFileTime();
    }

    /**
     * @inheritdoc
     */
    public function shutdown()
    {
    }

    /**
     * @param $sourceOptions
     * @throws SourceConfigurationException
     */
    protected function validateConfigurationOptions($sourceOptions) {
        foreach ($this->mandatoryConfigurationOptions as $configurationOption) {
            if (!isset($sourceOptions[$configurationOption]) || empty($sourceOptions[$configurationOption])) {
                throw new SourceConfigurationException(sprintf('Error while validating sourceConfiguration for SynchronizationSource %s, mandatory option %s is missing.', get_class($this), $configurationOption), 1489392744);
            }
        }
    }
}