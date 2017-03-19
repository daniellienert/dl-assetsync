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
use Neos\Flow\Annotations as Flow;
use Neos\Utility\Files;

class LocalFilesystemSource extends AbstractSource
{

    /**
     * @var array
     */
    protected $mandatoryConfigurationOptions = ['sourcePath'];

    /**
     * @inheritdoc
     */
    public function generateSourceFileCollection()
    {
        $sourcePath = $this->sourceOptions['sourcePath'];
        if (!is_dir($sourcePath)) {
           throw new SourceConfigurationException(sprintf('The sourcePath "%s" was not found or not accessible.', $sourcePath), 1489827676);
        }

        $fileCollection = new SourceFileCollection();

        foreach (Files::readDirectoryRecursively($sourcePath) as $filePath) {
            $fileTime = new \DateTime();
            $fileTime->setTimestamp(filemtime($filePath));
            $sourceFile = new SourceFile($filePath, $fileTime, filesize($filePath));

            $fileCollection->add($sourceFile);
        }

        return $fileCollection;
    }

    /**
     * @inheritdoc
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState)
    {
        return sha1_file($sourceFile->getFileIdentifier()) !== $fileState->getResource()->getSha1();
    }

    /**
     * @inheritdoc
     */
    public function getPathToLocalFile(SourceFile $sourceFile)
    {
        return $sourceFile->getFileIdentifier();
    }
}