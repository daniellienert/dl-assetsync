<?php
declare(strict_types=1);

namespace DL\AssetSync\Domain\Repository;

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
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 * @method findOneBySourceFileIdentifierHash(string $fileIdentifierHash)
 * @method findBySourceIdentifier(string $sourceIdentifier)
 */
class FileStateRepository extends Repository
{
}
