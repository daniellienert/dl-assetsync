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
use DL\AssetSync\Synchronization\SourceFileCollection;
use Neos\Utility\Files;

class LocalFilesystemSource extends AbstractSource
{

    /**
     * @var string[]
     */
    protected $mandatoryConfigurationOptions = ['sourcePath'];

    /**
     * @inheritdoc
     */
    public function generateSourceFileCollection(): SourceFileCollection
    {
        $sourcePath = $this->sourceOptions['sourcePath'];
        if (!is_dir($sourcePath)) {
            throw new SourceConfigurationException(sprintf('The directory "%s" defined by sourcePath was not found or not accessible.', $sourcePath), 1489827676);
        }

        $fileCollection = new SourceFileCollection();

        foreach (Files::readDirectoryRecursively($sourcePath) as $filePath) {
            $fileCollection->add($this->generateSourceFileObject(realpath($filePath)));
        }

        return $fileCollection->filterByIdentifierPattern($this->fileIdentifierPattern);
    }

    /**
     * @inheritdoc
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState): bool
    {
        return sha1_file($sourceFile->getFileIdentifier()) !== $fileState->getResource()->getSha1();
    }

    /**
     * @inheritdoc
     */
    public function getPathToLocalFile(SourceFile $sourceFile): string
    {
        return $sourceFile->getFileIdentifier();
    }

    /**
     * @param string $filePath
     * @return SourceFile
     * @throws SourceFileException
     */
    public function generateSourceFileObject(string $filePath): SourceFile
    {
        if (!is_readable($filePath)) {
            throw new SourceFileException(sprintf('The file at path "%s" was not readable.', $filePath));
        }

        $fileTime = new \DateTime();
        $fileTime->setTimestamp(filemtime($filePath));
        return new SourceFile($filePath, $fileTime, filesize($filePath));
    }
}
