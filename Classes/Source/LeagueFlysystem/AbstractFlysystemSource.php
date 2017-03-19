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
    public function getPathToLocalFile(SourceFile $sourceFile)
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
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState)
    {
        return $sourceFile->getFileTime() > $fileState->getSourceFileTime();
    }

    /**
     * @inheritdoc
     */
    public function shutdown()
    {
        Files::removeDirectoryRecursively($this->temporaryImportDirectory);
    }
}