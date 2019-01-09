<?php
namespace DL\AssetSync\Source\LeagueFlysystem;

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
use DL\AssetSync\Exception\SourceDriverNotFoundException;
use DL\AssetSync\Source\AbstractSource;
use Neos\Utility\Files;
use DL\AssetSync\Domain\Dto\SourceFile;

abstract class AbstractFlysystemSource extends AbstractSource
{

    /**
     * @var \League\Flysystem\Filesystem
     */
    protected $fileSystem;

    /**
     * @var string
     */
    protected $temporaryImportDirectory;


    /**
     * @inheritdoc
     */
    public function getPathToLocalFile(SourceFile $sourceFile): string
    {
        $temporaryTargetPathAndFilename = Files::concatenatePaths([$this->temporaryImportDirectory, basename($sourceFile->getFileIdentifier())]);

        $target = fopen($temporaryTargetPathAndFilename, 'wb');
        $sourceStream = $this->fileSystem->readStream($sourceFile->getFileIdentifier());
        stream_copy_to_stream($sourceStream, $target);
        fclose($target);

        return $temporaryTargetPathAndFilename;
    }

    /**
     * @inheritdoc
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState): bool
    {
        return $sourceFile->getFileTime() > $fileState->getSourceFileTime();
    }

    /**
     * @inheritdoc
     */
    public function shutdown(): void
    {
        Files::removeDirectoryRecursively($this->temporaryImportDirectory);
    }

    /**
     * @param $className
     * @param $providingPackageName
     * @throws SourceDriverNotFoundException
     */
    protected function checkDriverClassExists(string $className, string $providingPackageName): void
    {
        if (!class_exists($className)) {
            throw new SourceDriverNotFoundException(sprintf("The needed driver for the selected source is not available.\nInstall the package using `composer require %s`.", $providingPackageName), 1503177644);
        }
    }
}
