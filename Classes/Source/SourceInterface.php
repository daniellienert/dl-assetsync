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
use Doctrine\Common\Collections\ArrayCollection;

interface SourceInterface
{

    /**
     * Template method to initialize
     * the source.
     *
     * @return void
     */
    public function initialize();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return array
     */
    public function getAssetTags();

    /**
     * @return SourceFileCollection
     */
    public function generateSourceFileCollection();

    /**
     * @return boolean
     */
    public function isRemoveAssetsNotInSource();

    /**
     * Uses information provided by the source to determine
     * if a file needs to be synched.
     *
     * @param SourceFile $sourceFile
     * @param FileState $fileState
     * @return mixed
     */
    public function isSyncNecessary(SourceFile $sourceFile, FileState $fileState);

    /**
     * @param SourceFile $sourceFile
     * @return string
     */
    public function getPathToLocalFile(SourceFile $sourceFile);

    /**
     * Template method for shutdown code.
     */
    public function shutdown();
}
