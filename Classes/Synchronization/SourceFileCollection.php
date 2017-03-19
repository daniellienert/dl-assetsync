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
     * @var array
     */
    protected $fileIdentifierHasIndex = [];

    /**
     * @param SourceFile $element
     * @return bool
     */
    public function add($element)
    {
        $this->fileIdentifierHasIndex[$element->getFileIdentifierHash()] = $element;
        return parent::add($element);
    }

    /**
     * @param $fileIdentifierHash
     * @return SourceFile
     */
    public function getSourceFileByFileIdentifierHash($fileIdentifierHash)
    {
        if (isset($this->fileIdentifierHasIndex[$fileIdentifierHash])) {
            return $this->fileIdentifierHasIndex[$fileIdentifierHash];
        }
    }
}