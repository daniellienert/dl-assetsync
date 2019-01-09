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

use DL\AssetSync\Exception\SourceDriverNotFoundException;
use Neos\Utility\Exception\FilesException;
use Neos\Utility\Files;
use DL\AssetSync\Domain\Dto\SourceFile;
use DL\AssetSync\Synchronization\SourceFileCollection;

class DropboxSource extends AbstractFlysystemSource
{

    /**
     * @var string[]
     */
    protected $mandatoryConfigurationOptions = ['sourcePath', 'accessToken', 'appSecret'];

    /**
     * @throws SourceDriverNotFoundException
     * @throws FilesException
     */
    public function initialize(): void
    {
        $this->checkDriverClassExists("\\Dropbox\\Client", "league/flysystem-dropbox");

        $client = new \Dropbox\Client(
            $this->sourceOptions['accessToken'],
            $this->sourceOptions['appSecret']
        );
        $adapter = new \League\Flysystem\Dropbox\DropboxAdapter($client, $this->sourceOptions['sourcePath']);

        $this->fileSystem = new \League\Flysystem\Filesystem($adapter);
        $this->fileSystem->addPlugin(new \League\Flysystem\Plugin\ListFiles());

        $this->temporaryImportDirectory = Files::concatenatePaths([$this->environment->getPathToTemporaryDirectory(), uniqid('DL_AssetSync_Import')]);
        Files::createDirectoryRecursively($this->temporaryImportDirectory);
    }

    /**
     * @inheritdoc
     */
    public function generateSourceFileCollection(): SourceFileCollection
    {
        $sourceFileCollection = new SourceFileCollection();

        foreach ($this->fileSystem->listContents() as $file) {
            $fileTime = new \DateTime();
            $fileTime->setTimestamp($file['timestamp']);
            $sourceFileCollection->add(new SourceFile($file['path'], $fileTime, $file['size']));
        };

        return $sourceFileCollection->filterByIdentifierPattern($this->fileIdentifierPattern);
    }
}
