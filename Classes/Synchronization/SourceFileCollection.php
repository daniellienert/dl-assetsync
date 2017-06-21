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

use DL\AssetSync\Domain\Dto\SourceFile;
use Doctrine\Common\Collections\ArrayCollection;

class SourceFileCollection extends ArrayCollection
{
    /**
     *
     * @var array<SourceFile>
     */
    protected $fileIdentifierHashIndex = [];

    /**
     * @param SourceFile $element
     * @return bool
     */
    public function add($element)
    {
        $this->fileIdentifierHashIndex[$element->getFileIdentifierHash()] = $element;
        return parent::add($element);
    }

    /**
     * @param $fileIdentifierHash
     * @return SourceFile
     */
    public function getSourceFileByFileIdentifierHash($fileIdentifierHash)
    {
        if (isset($this->fileIdentifierHashIndex[$fileIdentifierHash])) {
            return $this->fileIdentifierHashIndex[$fileIdentifierHash];
        }
    }

    /**
     * Filters the collection by a given regex pattern
     *
     * @param string $identifierPattern
     * @return SourceFileCollection
     */
    public function filterByIdentifierPattern(string $identifierPattern) {
        $filteredCollection = new SourceFileCollection();

        foreach($this as $sourceFile) { /** @var SourceFile $sourceFile */
            if (preg_match('/' . $identifierPattern . '/', $sourceFile->getFileIdentifier()) === 1) {
                $filteredCollection->add($sourceFile);
            }
        }

        return $filteredCollection;
    }
}
