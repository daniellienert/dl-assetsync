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

use Neos\Flow\Annotations as Flow;

class SourceFactory
{
    /**
     * @var array
     * @Flow\InjectConfiguration(path="sourceConfiguration")
     */
    protected $sourceConfiguration;

    /**
     * @param array $sourceConfiguration
     */
    public function setSourceConfiguration(array $sourceConfiguration)
    {
        $this->sourceConfiguration = $sourceConfiguration;
    }

    /**
     * @param string $sourceIdentifier
     * @return SourceInterface
     * @throws SourceConfigurationException
     */
    public function createSource($sourceIdentifier)
    {
        if (!isset($this->sourceConfiguration[$sourceIdentifier])) {
            throw new SourceConfigurationException(sprintf('No source configuration for source "%s" was found.', $sourceIdentifier), 1489394283);
        }

        $this->sourceConfiguration[$sourceIdentifier]['sourceIdentifier'] = $sourceIdentifier;
        $sourceClass = $this->sourceConfiguration[$sourceIdentifier]['sourceClass'];

        if (!class_exists($sourceClass)) {
            throw new SourceConfigurationException(sprintf('No source class "%s" for source %s" was found.', $sourceClass, $sourceIdentifier), 1489394284);
        }

        $sourceObject = new $sourceClass($this->sourceConfiguration[$sourceIdentifier]);
        if (!($sourceObject instanceof SourceInterface)) {
            throw new SourceConfigurationException(sprintf('The configured class %s does not implement the interface %s', $sourceClass, SourceInterface::class));
        }

        $sourceObject->initialize();

        return $sourceObject;
    }
}
